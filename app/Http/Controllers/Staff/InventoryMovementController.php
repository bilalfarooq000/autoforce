<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Inspection;
use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryMovementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');
        $branchId = $isAdmin ? $request->integer('branch_id') ?: null : $user->branch_id;
        $branches = $isAdmin ? Branch::orderBy('name')->get() : collect();

        $items = InventoryItem::when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get();

        $movements = InventoryMovement::with('item', 'branch', 'performedBy', 'inspection.vehicle.customer')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->type, fn ($q, $t) => $q->where('type', $t))
            ->when($request->item_id, fn ($q, $id) => $q->where('item_id', $id))
            ->when($request->from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($request->to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->when($request->search, fn ($q, $s) => $q->whereHas('inspection', fn ($i) => $i->whereHas('vehicle', fn ($v) => $v->where('registration_number', 'like', "%{$s}%")
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%")))))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('staff.inventory.movements.index', compact('movements', 'branches', 'items'));
    }

    public function receive(InventoryItem $stockItem)
    {
        return view('staff.inventory.movements.receive', ['item' => $stockItem]);
    }

    public function storeReceive(Request $request, InventoryItem $stockItem)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $this->applyMovement($request, $stockItem, 'stock_in', $data['quantity'], [
            'unit_cost' => $data['unit_cost'] ?? $stockItem->cost_price,
            'reference' => $data['reference'] ?? null,
        ]);

        return redirect()->route('staff.inventory.items.show', $stockItem)->with('status', 'Stock received.');
    }

    public function issue(Request $request, InventoryItem $stockItem)
    {
        $openJobs = Inspection::where('branch_id', $stockItem->branch_id)
            ->whereNotIn('status', ['completed'])
            ->with('vehicle')
            ->latest()
            ->get();

        return view('staff.inventory.movements.issue', ['item' => $stockItem, 'openJobs' => $openJobs]);
    }

    public function storeIssue(Request $request, InventoryItem $stockItem)
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:'.$stockItem->quantity_on_hand],
            'inspection_id' => ['nullable', 'exists:inspections,id'],
            'reference' => ['nullable', 'string', 'max:255'],
        ]);

        $this->applyMovement($request, $stockItem, 'issued_to_job', $data['quantity'], [
            'inspection_id' => $data['inspection_id'] ?? null,
            'reference' => $data['reference'] ?? null,
        ]);

        return redirect()->route('staff.inventory.items.show', $stockItem)->with('status', 'Stock issued.');
    }

    public function adjust(InventoryItem $stockItem)
    {
        return view('staff.inventory.movements.adjust', ['item' => $stockItem]);
    }

    public function storeAdjust(Request $request, InventoryItem $stockItem)
    {
        $data = $request->validate([
            'direction' => ['required', 'in:in,out'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reference' => ['required', 'string', 'max:255'],
        ]);

        if ($data['direction'] === 'out' && $data['quantity'] > $stockItem->quantity_on_hand) {
            return back()->withErrors(['quantity' => 'Cannot remove more than the current stock on hand ('.$stockItem->quantity_on_hand.').'])->withInput();
        }

        $this->applyMovement($request, $stockItem, $data['direction'] === 'in' ? 'adjustment_in' : 'adjustment_out', $data['quantity'], [
            'reference' => $data['reference'],
        ]);

        return redirect()->route('staff.inventory.items.show', $stockItem)->with('status', 'Stock adjusted.');
    }

    private function applyMovement(Request $request, InventoryItem $stockItem, string $type, int $quantity, array $extra = []): InventoryMovement
    {
        return DB::transaction(function () use ($request, $stockItem, $type, $quantity, $extra) {
            $item = InventoryItem::where('id', $stockItem->id)->lockForUpdate()->first();

            $newQuantity = in_array($type, InventoryMovement::DECREASING)
                ? $item->quantity_on_hand - $quantity
                : $item->quantity_on_hand + $quantity;

            abort_if($newQuantity < 0, 422, 'This would take stock below zero.');

            $movement = InventoryMovement::create(array_merge([
                'branch_id' => $item->branch_id,
                'item_id' => $item->id,
                'type' => $type,
                'quantity' => $quantity,
                'performed_by' => $request->user()->id,
            ], $extra));

            $item->update(['quantity_on_hand' => $newQuantity]);

            return $movement;
        });
    }
}
