<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function create(Inspection $inspection)
    {
        $inspection->load('vehicle.customer');

        if ($inspection->quotation()->exists()) {
            return redirect()->route('staff.quotations.show', $inspection);
        }

        return view('staff.quotations.create', compact('inspection'));
    }

    public function show(Inspection $inspection)
    {
        $inspection->load('vehicle.customer', 'quotation.items.assignee', 'quotation.decidedByStaff', 'invoice');

        abort_unless($inspection->quotation, 404, 'No quotation has been built for this job yet.');

        return view('staff.quotations.show', compact('inspection'));
    }

    public function store(Request $request, Inspection $inspection)
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $rows = collect($data['items'])
            ->filter(fn ($row) => filled($row['description'] ?? null) && is_numeric($row['amount'] ?? null));

        abort_if($rows->isEmpty(), 422, 'Add at least one item with a description and amount.');

        $quotation = Quotation::updateOrCreate(
            ['inspection_id' => $inspection->id],
            ['status' => 'pending', 'decided_at' => null, 'decision_channel' => null, 'decided_by_staff_id' => null]
        );

        $quotation->items()->delete();

        foreach ($rows as $row) {
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'description' => $row['description'],
                'amount' => $row['amount'],
            ]);
        }

        $quotation->update(['total_amount' => $rows->sum('amount')]);

        if ($inspection->status !== 'awaiting_approval') {
            $inspection->update(['status' => 'awaiting_approval']);
        }

        NotificationService::notify($inspection->vehicle, 'approval_required', $inspection);

        return redirect()->route('staff.customers.show', $inspection->vehicle->customer_id)
            ->with('status', 'Quotation sent for customer approval.');
    }

    /**
     * Staff can record the customer's decision themselves (e.g. approved verbally
     * in person or over the phone) instead of waiting on the secure link —
     * keeps quotations from sitting "pending" indefinitely.
     */
    public function recordDecision(Request $request, Inspection $inspection, Quotation $quotation)
    {
        abort_unless($quotation->inspection_id === $inspection->id, 404);

        $data = $request->validate([
            'decision' => ['required', 'in:approved,declined'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $quotation->update([
            'status' => $data['decision'],
            'customer_notes' => $data['customer_notes'] ?? null,
            'decided_at' => now(),
            'decision_channel' => 'staff_recorded',
            'decided_by_staff_id' => $request->user()->id,
        ]);

        if ($data['decision'] === 'approved') {
            $inspection->update(['status' => 'in_repair']);
        } else {
            $inspection->update(['status' => 'ready_for_collection', 'ready_at' => now()]);
            NotificationService::notify($inspection->vehicle, 'vehicle_ready', $inspection);
        }

        return redirect()->route('staff.quotations.show', $inspection)
            ->with('status', 'Decision recorded on behalf of the customer.');
    }

    /**
     * Track the actual repair work against each quotation line item.
     */
    public function updateItemStatus(Request $request, Inspection $inspection, QuotationItem $item)
    {
        abort_unless($item->quotation->inspection_id === $inspection->id, 404);

        $data = $request->validate([
            'status' => ['required', 'in:pending,in_progress,done'],
        ]);

        $item->update([
            'status' => $data['status'],
            'assigned_to' => $data['status'] === 'in_progress' ? ($item->assigned_to ?? $request->user()->id) : $item->assigned_to,
            'started_at' => $data['status'] === 'in_progress' && ! $item->started_at ? now() : $item->started_at,
            'completed_at' => $data['status'] === 'done' ? now() : null,
        ]);

        return back()->with('status', "Task \"{$item->description}\" marked ".str($data['status'])->headline().'.');
    }
}
