<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Inspection;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\User;
use App\Models\Vehicle;

class DashboardController extends Controller
{
    public function index()
    {
        $branches = Branch::withCount(['vehicles'])->get()->map(function (Branch $branch) {
            $inspections = Inspection::where('branch_id', $branch->id)->get();
            $invoices = Invoice::whereHas('vehicle', fn ($v) => $v->where('branch_id', $branch->id))->get();

            $branch->staff_count = User::where('branch_id', $branch->id)->role(['manager', 'service_advisor', 'technician'])->count();
            $branch->active_jobs = $inspections->where('status', '!=', 'completed')->count();
            $branch->awaiting_approval = $inspections->where('status', 'awaiting_approval')->count();
            $branch->outstanding_amount = $invoices->whereIn('status', ['unpaid', 'partial'])->sum(fn ($i) => $i->balanceDue());
            $branch->revenue_collected = $invoices->sum(fn ($i) => $i->amountPaid());

            return $branch;
        });

        $activeItems = InventoryItem::where('is_active', true)->get();

        $companyStats = [
            'total_branches' => $branches->count(),
            'total_vehicles' => Vehicle::count(),
            'total_staff' => User::role(['manager', 'service_advisor', 'technician'])->count(),
            'jobs_in_progress' => Inspection::where('status', '!=', 'completed')->count(),
            'total_outstanding' => $branches->sum('outstanding_amount'),
            'total_revenue' => $branches->sum('revenue_collected'),
            'total_customers' => User::role('customer')->count(),
            'inventory_value' => $activeItems->sum(fn ($i) => $i->quantity_on_hand * $i->cost_price),
            'low_stock_items' => $activeItems->filter(fn ($i) => $i->quantity_on_hand <= $i->reorder_level)->count(),
        ];

        return view('admin.dashboard', compact('branches', 'companyStats'));
    }
}
