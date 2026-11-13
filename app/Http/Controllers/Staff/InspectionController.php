<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\InspectionDiagramMark;
use App\Models\InspectionItem;
use App\Models\InspectionMedia;
use App\Models\JobComment;
use App\Models\Vehicle;
use App\Services\NotificationService;
use App\Support\InspectionChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    public function create(Vehicle $vehicle)
    {
        return view('staff.inspections.create', compact('vehicle'));
    }

    public function store(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
            'mileage_at_visit' => ['required', 'integer', 'min:0'],
        ]);

        $inspection = Inspection::create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $vehicle->branch_id,
            'technician_id' => $request->user()->hasRole('technician') ? $request->user()->id : null,
            'created_by' => $request->user()->id,
            'reason' => $data['reason'],
            'mileage_at_visit' => $data['mileage_at_visit'],
            'status' => 'checked_in',
        ]);

        $vehicle->update(['mileage' => max($vehicle->mileage, $data['mileage_at_visit'])]);

        $order = 0;
        foreach (InspectionChecklist::template() as $category => $items) {
            foreach ($items as $title) {
                InspectionItem::create([
                    'inspection_id' => $inspection->id,
                    'category' => $category,
                    'title' => $title,
                    'is_checked' => false,
                    'sort_order' => $order++,
                ]);
            }
        }

        NotificationService::notify($vehicle, 'checked_in', $inspection);

        return redirect()->route('staff.inspections.show', $inspection)
            ->with('status', 'Vehicle checked in. Customer notified.');
    }

    public function show(Inspection $inspection)
    {
        $inspection->load([
            'vehicle.customer', 'technician', 'creator', 'items.media',
            'quotation.items', 'invoice', 'notifications' => fn ($q) => $q->latest(),
            'diagramMarks', 'comments.author',
        ]);

        $technicians = \App\Models\User::role('technician')->get();

        return view('staff.inspections.show', compact('inspection', 'technicians'));
    }

    public function storeDiagramMark(Request $request, Inspection $inspection)
    {
        $data = $request->validate([
            'view' => ['required', 'in:front,rear,left,right,top'],
            'x' => ['required', 'numeric', 'min:0', 'max:100'],
            'y' => ['required', 'numeric', 'min:0', 'max:100'],
            'severity' => ['required', 'in:critical,advisory,satisfactory'],
            'note' => ['required', 'string', 'max:255'],
        ]);

        InspectionDiagramMark::create([
            'inspection_id' => $inspection->id,
            ...$data,
        ]);

        return back()->with('status', 'Marked on vehicle diagram.');
    }

    public function destroyDiagramMark(Inspection $inspection, InspectionDiagramMark $mark)
    {
        abort_unless($mark->inspection_id === $inspection->id, 404);
        $mark->delete();

        return back()->with('status', 'Mark removed.');
    }

    /**
     * A technician/manager posts a progress update while the job is moving through
     * its repair/maintenance stages. The customer is emailed immediately so they
     * can follow along, not just at checked-in/ready milestones.
     */
    public function storeComment(Request $request, Inspection $inspection)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $comment = JobComment::create([
            'inspection_id' => $inspection->id,
            'author_id' => $request->user()->id,
            'stage' => $inspection->status,
            'body' => $data['body'],
        ]);

        NotificationService::notifyJobComment($comment->load('author', 'inspection.vehicle.customer'));

        return back()->with('status', 'Comment added. Customer notified by email.');
    }

    public function startInspection(Request $request, Inspection $inspection)
    {
        $data = $request->validate(['technician_id' => ['nullable', 'exists:users,id']]);

        $inspection->update([
            'status' => 'inspecting',
            'technician_id' => $data['technician_id'] ?? $inspection->technician_id ?? $request->user()->id,
            'inspection_started_at' => now(),
        ]);

        return back()->with('status', 'Inspection started.');
    }

    public function storeItem(Request $request, Inspection $inspection)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'technician_comment' => ['nullable', 'string'],
            'urgency' => ['required', 'in:critical,advisory,satisfactory'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'before_media.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi', 'max:51200'],
            'after_media.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi', 'max:51200'],
        ]);

        $item = InspectionItem::create([
            'inspection_id' => $inspection->id,
            'category' => null, // ad-hoc finding, not part of the standard checklist
            'title' => $data['title'],
            'technician_comment' => $data['technician_comment'] ?? null,
            'urgency' => $data['urgency'],
            'is_checked' => true,
            'estimated_cost' => $data['estimated_cost'] ?? null,
        ]);

        $this->attachMedia($request, $item, $inspection->id);

        return redirect()->route('staff.inspections.show', $inspection)
            ->with('status', 'Finding added.');
    }

    public function updateItem(Request $request, Inspection $inspection, InspectionItem $item)
    {
        abort_unless($item->inspection_id === $inspection->id, 404);

        $data = $request->validate([
            'technician_comment' => ['nullable', 'string'],
            'urgency' => ['required', 'in:critical,advisory,satisfactory'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'before_media.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi', 'max:51200'],
            'after_media.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,mp4,mov,avi', 'max:51200'],
        ]);

        $item->update([
            'technician_comment' => $data['technician_comment'] ?? null,
            'urgency' => $data['urgency'],
            'estimated_cost' => $data['estimated_cost'] ?? null,
            'is_checked' => true,
        ]);

        $this->attachMedia($request, $item, $inspection->id);

        // Standard checklist items have a fixed, known location on the vehicle —
        // marking its status automatically places/updates the diagram pin for it.
        if ($item->category) {
            $position = InspectionChecklist::positionFor($item->title);

            InspectionDiagramMark::updateOrCreate(
                ['inspection_item_id' => $item->id],
                [
                    'inspection_id' => $inspection->id,
                    'view' => $position['view'],
                    'x' => $position['x'],
                    'y' => $position['y'],
                    'severity' => $data['urgency'],
                    'note' => $item->title,
                ]
            );
        }

        return redirect()->route('staff.inspections.show', $inspection)
            ->with('status', "Checklist item \"{$item->title}\" updated.");
    }

    /**
     * The standard checklist's Good/Moderate/Poor buttons used to submit (and
     * reload the page) on every single click — painfully slow for an 18-item
     * checklist. The UI now holds every click locally and posts them all here
     * in one go when the technician hits "Save Checklist".
     */
    public function batchUpdateItems(Request $request, Inspection $inspection)
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.urgency' => ['nullable', 'in:critical,advisory,satisfactory'],
        ]);

        $updated = 0;

        DB::transaction(function () use ($data, $inspection, &$updated) {
            foreach ($data['items'] as $row) {
                if (empty($row['urgency'])) {
                    continue;
                }

                $item = InspectionItem::find($row['id']);

                if (! $item || $item->inspection_id !== $inspection->id) {
                    continue;
                }

                $item->update(['urgency' => $row['urgency'], 'is_checked' => true]);
                $updated++;

                if ($item->category) {
                    $position = InspectionChecklist::positionFor($item->title);

                    InspectionDiagramMark::updateOrCreate(
                        ['inspection_item_id' => $item->id],
                        [
                            'inspection_id' => $inspection->id,
                            'view' => $position['view'],
                            'x' => $position['x'],
                            'y' => $position['y'],
                            'severity' => $row['urgency'],
                            'note' => $item->title,
                        ]
                    );
                }
            }
        });

        return redirect()->route('staff.inspections.show', $inspection)
            ->with('status', "Checklist updated — {$updated} item(s) saved.");
    }

    private function attachMedia(Request $request, InspectionItem $item, int $inspectionId): void
    {
        foreach (['before', 'after'] as $stage) {
            foreach ($request->file("{$stage}_media", []) as $file) {
                $path = $file->store('inspections/'.$inspectionId, 'public');

                InspectionMedia::create([
                    'inspection_item_id' => $item->id,
                    'path' => $path,
                    'type' => str_starts_with($file->getMimeType(), 'video') ? 'video' : 'photo',
                    'stage' => $stage,
                ]);
            }
        }
    }

    public function completeDiagnosis(Request $request, Inspection $inspection)
    {
        $data = $request->validate(['summary' => ['nullable', 'string']]);

        $uncheckedCount = $inspection->items()->where('is_checked', false)->count();
        if ($uncheckedCount > 0) {
            return back()->withErrors([
                'checklist' => "{$uncheckedCount} checklist item(s) are still unchecked. Please go through every item before completing diagnosis.",
            ]);
        }

        $inspection->update([
            'status' => 'awaiting_approval',
            'summary' => $data['summary'] ?? $inspection->summary,
            'diagnosis_completed_at' => now(),
        ]);

        NotificationService::notify($inspection->vehicle, 'inspection_completed', $inspection);

        return redirect()->route('staff.inspections.show', $inspection)
            ->with('status', 'Diagnosis completed. Customer notified via secure link.');
    }

    public function markReadyNoRepair(Inspection $inspection)
    {
        $uncheckedCount = $inspection->items()->where('is_checked', false)->count();
        if ($uncheckedCount > 0) {
            return back()->withErrors([
                'checklist' => "{$uncheckedCount} checklist item(s) are still unchecked. Please go through every item first.",
            ]);
        }

        $inspection->update(['status' => 'ready_for_collection', 'ready_at' => now(), 'diagnosis_completed_at' => $inspection->diagnosis_completed_at ?? now()]);

        NotificationService::notify($inspection->vehicle, 'vehicle_ready', $inspection);

        return back()->with('status', 'No repair needed. Vehicle marked ready for collection.');
    }

    public function markReady(Inspection $inspection)
    {
        $pendingTasks = $inspection->quotation?->items()->where('status', '!=', 'done')->count() ?? 0;
        if ($pendingTasks > 0) {
            return back()->withErrors(['tasks' => "{$pendingTasks} repair task(s) are still not marked Done. Finish tracking the work before marking the vehicle ready."]);
        }

        $inspection->update(['status' => 'ready_for_collection', 'ready_at' => now()]);

        NotificationService::notify($inspection->vehicle, 'vehicle_ready', $inspection);

        return back()->with('status', 'Vehicle marked ready for collection. Customer notified.');
    }

    public function markCollected(Inspection $inspection)
    {
        $inspection->update(['status' => 'completed', 'collected_at' => now()]);

        return back()->with('status', 'Vehicle marked as collected. Job closed.');
    }
}
