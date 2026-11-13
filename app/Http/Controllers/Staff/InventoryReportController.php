<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');
        $tab = $request->get('tab', 'overview');

        // Admin can pick any branch (or none, for the company-wide comparison);
        // everyone else is always pinned to their own — every tab below respects this.
        $branchId = $isAdmin ? $request->integer('branch_id') ?: null : $user->branch_id;
        $branches = $isAdmin ? Branch::orderBy('name')->get() : collect();

        $branchStats = Branch::query()
            ->when($branchId, fn ($q) => $q->where('id', $branchId))
            ->withCount(['inventoryItems as items_count' => fn ($q) => $q->where('is_active', true)])
            ->get()
            ->map(function (Branch $branch) {
                $items = $branch->inventoryItems()->where('is_active', true)->get();

                $branch->stock_value = $items->sum(fn ($i) => $i->quantity_on_hand * $i->cost_price);
                $branch->low_stock_count = $items->filter(fn ($i) => $i->isLowStock())->count();

                return $branch;
            });

        $lowStockItems = InventoryItem::with('category', 'branch')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true)
            ->whereColumn('quantity_on_hand', '<=', 'reorder_level')
            ->orderByRaw('quantity_on_hand - reorder_level ASC')
            ->get();

        $valuationByCategory = InventoryItem::with('category')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('is_active', true)
            ->get()
            ->groupBy(fn ($i) => $i->category->name ?? 'Uncategorized')
            ->map(fn ($items) => [
                'items' => $items,
                'total_qty' => $items->sum('quantity_on_hand'),
                'total_value' => $items->sum(fn ($i) => $i->quantity_on_hand * $i->cost_price),
            ]);

        $grandTotalValue = $valuationByCategory->sum('total_value');

        $usageFrom = $request->get('from', now()->subDays(30)->format('Y-m-d'));
        $usageTo = $request->get('to', now()->format('Y-m-d'));
        $usageItemId = $request->get('item_id');

        $usage = InventoryMovement::query()
            ->select('item_id', DB::raw('SUM(quantity) as total_used'))
            ->where('type', 'issued_to_job')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', '>=', $usageFrom)
            ->whereDate('created_at', '<=', $usageTo)
            ->groupBy('item_id')
            ->with('item.category')
            ->orderByDesc('total_used')
            ->get();

        // Drill down: every single "issued to job" movement for one part, in the same
        // window — who issued it, when, and which vehicle/customer it went on.
        $usageDetail = null;
        if ($usageItemId) {
            $usageDetail = InventoryMovement::with('item', 'branch', 'performedBy', 'inspection.vehicle.customer')
                ->where('type', 'issued_to_job')
                ->where('item_id', $usageItemId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->whereDate('created_at', '>=', $usageFrom)
                ->whereDate('created_at', '<=', $usageTo)
                ->latest()
                ->get();
        }

        $allItems = InventoryItem::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

        return view('staff.inventory.reports.index', compact(
            'tab', 'branchStats', 'lowStockItems', 'valuationByCategory', 'grandTotalValue',
            'usage', 'usageFrom', 'usageTo', 'usageItemId', 'usageDetail',
            'branches', 'branchId', 'allItems'
        ));
    }
}
