<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->hasRole('admin')
            ? $request->integer('branch') ?: null
            : $user->branch_id;

        $viewingBranch = $branchId ? \App\Models\Branch::find($branchId) : null;

        $inspections = Inspection::with('vehicle.customer', 'technician')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get();

        $board = [];
        foreach (Inspection::STATUSES as $status => $label) {
            $board[$status] = [
                'label' => $label,
                'items' => $inspections->where('status', $status)->sortByDesc('created_at')->values(),
            ];
        }

        $invoiceQuery = Invoice::when($branchId, fn ($q) => $q->whereHas('vehicle', fn ($v) => $v->where('branch_id', $branchId)));

        $stats = [
            'vehicles_in_workshop' => $inspections->where('status', '!=', 'completed')->count(),
            'awaiting_diagnosis' => $inspections->whereIn('status', ['checked_in', 'inspecting'])->count(),
            'awaiting_approval' => $inspections->where('status', 'awaiting_approval')->count(),
            'in_repair' => $inspections->where('status', 'in_repair')->count(),
            'ready_for_collection' => $inspections->where('status', 'ready_for_collection')->count(),
            'completed_today' => $inspections->where('status', 'completed')
                ->filter(fn ($i) => $i->collected_at?->isToday())->count(),
            'outstanding_invoices' => (clone $invoiceQuery)->whereIn('status', ['unpaid', 'partial'])->count(),
            'outstanding_amount' => (clone $invoiceQuery)->whereIn('status', ['unpaid', 'partial'])->get()
                ->sum(fn ($invoice) => $invoice->balanceDue()),
            'low_stock_items' => InventoryItem::where('is_active', true)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
                ->count(),
        ];

        $vehiclesDueService = Vehicle::with('customer')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get()
            ->filter(fn (Vehicle $v) => $v->isServiceDueSoon());

        $technicianPerformance = User::role('technician')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->get()
            ->map(function (User $tech) use ($inspections) {
                $jobs = $inspections->where('technician_id', $tech->id);
                $completed = $jobs->whereIn('status', ['ready_for_collection', 'completed']);
                $turnarounds = $completed->map(fn ($i) => $i->turnaroundHours())->filter();

                return [
                    'name' => $tech->name,
                    'active_jobs' => $jobs->whereNotIn('status', ['completed'])->count(),
                    'completed_jobs' => $completed->count(),
                    'avg_turnaround_hours' => $turnarounds->count() ? round($turnarounds->avg(), 1) : null,
                ];
            });

        return view('staff.dashboard', compact('board', 'stats', 'vehiclesDueService', 'technicianPerformance', 'viewingBranch'));
    }
}
