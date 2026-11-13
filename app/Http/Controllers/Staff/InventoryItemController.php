<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->hasRole('admin') ? null : $user->branch_id;

        $items = InventoryItem::with('category', 'branch')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->branch_id && $user->hasRole('admin'), fn ($q) => $q->where('branch_id', $request->branch_id))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->boolean('low_stock'), fn ($q) => $q->whereColumn('quantity_on_hand', '<=', 'reorder_level'))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($qq) => $qq->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")
                ->orWhere('part_number', 'like', "%{$s}%")))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = InventoryCategory::orderBy('name')->get();
        $branches = $user->hasRole('admin') ? Branch::orderBy('name')->get() : collect();

        return view('staff.inventory.items.index', compact('items', 'categories', 'branches'));
    }

    public function create(Request $request)
    {
        $categories = InventoryCategory::orderBy('name')->get();
        $branches = $request->user()->hasRole('admin') ? Branch::orderBy('name')->get() : collect();

        return view('staff.inventory.items.create', compact('categories', 'branches'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'branch_id' => [$user->hasRole('admin') ? 'required' : 'nullable', 'exists:branches,id'],
            'category_id' => ['required', 'exists:inventory_categories,id'],
            'sku' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:100'],
            'part_number' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:20'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
            'opening_quantity' => ['nullable', 'integer', 'min:0'],
        ]);

        $branchId = $user->hasRole('admin') ? $data['branch_id'] : $user->branch_id;

        $item = DB::transaction(function () use ($data, $branchId, $user) {
            $item = InventoryItem::create([
                'branch_id' => $branchId,
                'category_id' => $data['category_id'],
                'sku' => $data['sku'] ?? null,
                'name' => $data['name'],
                'brand' => $data['brand'] ?? null,
                'part_number' => $data['part_number'] ?? null,
                'unit' => $data['unit'],
                'cost_price' => $data['cost_price'],
                'selling_price' => $data['selling_price'],
                'reorder_level' => $data['reorder_level'],
                'quantity_on_hand' => 0,
            ]);

            $opening = (int) ($data['opening_quantity'] ?? 0);

            if ($opening > 0) {
                InventoryMovement::create([
                    'branch_id' => $branchId,
                    'item_id' => $item->id,
                    'type' => 'stock_in',
                    'quantity' => $opening,
                    'unit_cost' => $data['cost_price'],
                    'reference' => 'Opening stock',
                    'performed_by' => $user->id,
                ]);

                $item->increment('quantity_on_hand', $opening);
            }

            return $item;
        });

        return redirect()->route('staff.inventory.items.show', $item)
            ->with('status', 'Item added to inventory.');
    }

    public function show(InventoryItem $stockItem)
    {
        $stockItem->load('category', 'branch', 'movements.performedBy', 'movements.inspection.vehicle.customer');

        return view('staff.inventory.items.show', ['item' => $stockItem]);
    }

    public function edit(InventoryItem $stockItem)
    {
        $categories = InventoryCategory::orderBy('name')->get();

        return view('staff.inventory.items.edit', ['item' => $stockItem, 'categories' => $categories]);
    }

    public function update(Request $request, InventoryItem $stockItem)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:inventory_categories,id'],
            'sku' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:100'],
            'part_number' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:20'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'reorder_level' => ['required', 'integer', 'min:0'],
        ]);

        $stockItem->update($data);

        return redirect()->route('staff.inventory.items.show', $stockItem)->with('status', 'Item updated.');
    }

    public function toggle(InventoryItem $stockItem)
    {
        $stockItem->update(['is_active' => ! $stockItem->is_active]);

        return back()->with('status', $stockItem->is_active ? 'Item reactivated.' : 'Item deactivated.');
    }
}
