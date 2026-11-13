<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function show(Request $request, Vehicle $vehicle)
    {
        abort_unless($vehicle->customer_id === $request->user()->id, 403);

        $vehicle->load([
            'inspections' => fn ($q) => $q->latest(),
            'inspections.items.media', 'inspections.technician', 'inspections.diagramMarks',
            'inspections.quotation.items', 'inspections.invoice', 'inspections.comments.author',
            'invoices' => fn ($q) => $q->latest(),
            'invoices.items', 'invoices.payments',
        ]);

        return view('customer.vehicles.show', compact('vehicle'));
    }
}
