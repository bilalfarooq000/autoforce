<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->hasRole('admin') ? null : $user->branch_id;

        $purchases = Purchase::with('supplier', 'purchasedBy')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->supplier_id, fn ($q, $id) => $q->where('supplier_id', $id))
            ->latest('purchased_at')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->orderBy('name')
            ->get();

        return view('staff.purchases.index', compact('purchases', 'suppliers'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $branchId = $user->hasRole('admin') ? null : $user->branch_id;

        $suppliers = Supplier::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->orderBy('name')
            ->get();

        $items = InventoryItem::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name', 'unit', 'cost_price']);

        return view('staff.purchases.create', compact('suppliers', 'items'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'supplier_invoice_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_id' => ['required', 'exists:inventory_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        $branchId = $user->branch_id;

        $purchase = DB::transaction(function () use ($data, $user, $branchId) {
            $total = collect($data['items'])->sum(fn ($row) => $row['quantity'] * $row['unit_cost']);

            $purchase = Purchase::create([
                'branch_id' => $branchId,
                'supplier_id' => $data['supplier_id'],
                'purchase_number' => 'PO-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
                'supplier_invoice_reference' => $data['supplier_invoice_reference'] ?? null,
                'total_amount' => $total,
                'notes' => $data['notes'] ?? null,
                'purchased_by' => $user->id,
                'purchased_at' => now(),
            ]);

            foreach ($data['items'] as $row) {
                $amount = $row['quantity'] * $row['unit_cost'];

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $row['item_id'],
                    'quantity' => $row['quantity'],
                    'unit_cost' => $row['unit_cost'],
                    'amount' => $amount,
                ]);

                $item = InventoryItem::where('id', $row['item_id'])->lockForUpdate()->first();

                InventoryMovement::create([
                    'branch_id' => $item->branch_id,
                    'item_id' => $item->id,
                    'type' => 'stock_in',
                    'quantity' => $row['quantity'],
                    'unit_cost' => $row['unit_cost'],
                    'reference' => "Purchase {$purchase->purchase_number}",
                    'performed_by' => $user->id,
                ]);

                $item->increment('quantity_on_hand', $row['quantity']);
            }

            return $purchase;
        });

        return redirect()->route('staff.purchases.show', $purchase)->with('status', "Purchase {$purchase->purchase_number} recorded and stock received.");
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'purchasedBy', 'items.item', 'branch');

        return view('staff.purchases.show', compact('purchase'));
    }
}
