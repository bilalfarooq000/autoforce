<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Quotation;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class PublicInspectionController extends Controller
{
    public function show(string $token)
    {
        $inspection = Inspection::where('access_token', $token)
            ->with(['vehicle.customer', 'technician', 'items.media', 'quotation.items', 'invoice', 'diagramMarks', 'comments.author'])
            ->firstOrFail();

        return view('portal.inspection', compact('inspection'));
    }

    public function decide(Request $request, string $token, Quotation $quotation)
    {
        $inspection = Inspection::where('access_token', $token)->firstOrFail();

        abort_unless($quotation->inspection_id === $inspection->id, 404);

        $data = $request->validate([
            'decision' => ['required', 'in:approved,declined'],
            'customer_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $quotation->update([
            'status' => $data['decision'],
            'customer_notes' => $data['customer_notes'] ?? null,
            'decided_at' => now(),
            'decision_channel' => 'customer_link',
        ]);

        activity()
            ->causedBy($inspection->vehicle->customer)
            ->performedOn($quotation)
            ->log('Customer '.$data['decision'].' the quotation via secure link');

        if ($data['decision'] === 'approved') {
            $inspection->update(['status' => 'in_repair']);
        } else {
            $inspection->update(['status' => 'ready_for_collection', 'ready_at' => now()]);
            NotificationService::notify($inspection->vehicle, 'vehicle_ready', $inspection);
        }

        return back()->with('status', 'Thank you. Your decision has been recorded at '.now()->format('d M Y, h:i A').'.');
    }
}
