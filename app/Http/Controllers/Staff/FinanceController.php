<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->hasRole('admin') ? $request->integer('branch') ?: null : $user->branch_id;

        $filters = function ($q) use ($request, $branchId) {
            $q->when($branchId, fn ($q) => $q->whereHas('vehicle', fn ($v) => $v->where('branch_id', $branchId)))
                ->when($request->status, fn ($q, $s) => $q->where('status', $s))
                ->when($request->search, fn ($q, $s) => $q->where(fn ($qq) => $qq->where('invoice_number', 'like', "%{$s}%")
                    ->orWhereHas('vehicle', fn ($v) => $v->where('registration_number', 'like', "%{$s}%"))
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%"))))
                ->when($request->from, fn ($q, $d) => $q->whereDate('issued_at', '>=', $d))
                ->when($request->to, fn ($q, $d) => $q->whereDate('issued_at', '<=', $d));
        };

        $invoices = Invoice::with('vehicle.branch', 'customer', 'payments')
            ->tap($filters)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $allForTotals = Invoice::with('payments')
            ->tap($filters)
            ->get();

        $stats = [
            'total_invoiced' => $allForTotals->sum('total'),
            'total_collected' => $allForTotals->sum(fn ($i) => $i->amountPaid()),
            'total_outstanding' => $allForTotals->sum(fn ($i) => $i->balanceDue()),
            'unpaid_count' => $allForTotals->where('status', '!=', 'paid')->count(),
        ];

        $branches = $user->hasRole('admin') ? \App\Models\Branch::all() : collect();

        return view('staff.finance.index', compact('invoices', 'stats', 'branches', 'branchId'));
    }
}
