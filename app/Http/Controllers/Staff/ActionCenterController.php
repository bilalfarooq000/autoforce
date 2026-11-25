<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\Quotation;
use Illuminate\Http\Request;

class ActionCenterController extends Controller
{
    /**
     * One screen for "things that need attention today" — pulled from data that
     * already exists elsewhere (invoices, quotations, stock) rather than a new
     * tracking concept of its own, so there's nothing extra for staff to maintain.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->hasRole('admin') ? null : $user->branch_id;

        $overdueInvoices = Invoice::with('vehicle', 'customer', 'payments')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('issued_at', '<=', now()->subDays(7))
            ->when($branchId, fn ($q) => $q->whereHas('vehicle', fn ($v) => $v->where('branch_id', $branchId)))
            ->get()
            ->sortByDesc(fn ($invoice) => (int) $invoice->issued_at->diffInDays(now()));

        $pendingApprovals = Quotation::with('inspection.vehicle.customer')
            ->where('status', 'pending')
            ->when($branchId, fn ($q) => $q->whereHas('inspection', fn ($i) => $i->where('branch_id', $branchId)))
            ->oldest()
            ->get();

        $lowStockItems = InventoryItem::with('branch')
            ->where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByRaw('CAST(quantity_on_hand AS SIGNED) - CAST(reorder_level AS SIGNED) ASC')
            ->get();

        $newBookingRequests = Booking::with('branch', 'serviceType')
            ->where('status', 'pending')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->oldest('requested_at')
            ->get();

        return view('staff.action-center.index', compact('overdueInvoices', 'pendingApprovals', 'lowStockItems', 'newBookingRequests'));
    }
}
