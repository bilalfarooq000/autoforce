<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\ServiceType;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function create(Inspection $inspection)
    {
        $inspection->load('vehicle.customer', 'items');

        if ($inspection->quotation()->exists()) {
            return redirect()->route('staff.quotations.show', $inspection);
        }

        // Findings the technician already priced (anything with an estimated
        // cost) become the starting rows, so that price isn't typed twice.
        $findings = $inspection->items
            ->filter(fn ($item) => $item->estimated_cost && $item->estimated_cost > 0)
            ->map(fn ($item) => [
                'description' => $item->title,
                'type' => 'labor',
                'amount' => (float) $item->estimated_cost,
            ])
            ->values();

        $branchId = request()->user()->hasRole('admin') ? null : request()->user()->branch_id;
        $serviceTypes = ServiceType::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'default_price']);

        return view('staff.quotations.create', compact('inspection', 'findings', 'serviceTypes'));
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
            'items.*.type' => ['nullable', 'in:part,labor'],
            'items.*.amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        // A row with only one of description/amount filled in is a mistake, not
        // an intentionally-blank row — silently dropping it (and its price)
        // would understate the quotation with no warning to the customer or staff.
        $incompleteRow = collect($data['items'])->first(function ($row) {
            $hasDescription = filled($row['description'] ?? null);
            $hasAmount = is_numeric($row['amount'] ?? null);

            return $hasDescription xor $hasAmount;
        });

        if ($incompleteRow) {
            $label = filled($incompleteRow['description'] ?? null) ? "\"{$incompleteRow['description']}\"" : 'One of the rows';

            return back()->withInput()->withErrors(['items' => "{$label} is missing a description or an amount — fill in both, or clear the row completely."]);
        }

        $rows = collect($data['items'])
            ->filter(fn ($row) => filled($row['description'] ?? null) && is_numeric($row['amount'] ?? null));

        if ($rows->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'Add at least one item with a description and an amount.']);
        }

        $quotation = Quotation::updateOrCreate(
            ['inspection_id' => $inspection->id],
            ['status' => 'pending', 'decided_at' => null, 'decision_channel' => null, 'decided_by_staff_id' => null]
        );

        $quotation->items()->delete();

        foreach ($rows as $row) {
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'description' => $row['description'],
                'type' => $row['type'] ?? 'labor',
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
