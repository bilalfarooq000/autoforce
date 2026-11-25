<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\ServiceType;
use Illuminate\Http\Request;

class PublicBookingController extends Controller
{
    /**
     * No login needed — a prospective or existing customer books a slot from
     * a link the garage shares (website, WhatsApp, etc). Staff confirm or
     * convert it to a real job once the vehicle actually arrives.
     */
    public function show(Branch $branch)
    {
        if (! $branch->is_active) {
            return view('portal.booking.unavailable', compact('branch'));
        }

        $serviceTypes = ServiceType::where('is_active', true)
            ->where(fn ($q) => $q->where('branch_id', $branch->id)->orWhereNull('branch_id'))
            ->orderBy('name')
            ->get();

        return view('portal.booking.show', compact('branch', 'serviceTypes'));
    }

    public function store(Request $request, Branch $branch)
    {
        abort_unless($branch->is_active, 404);

        $data = $request->validate([
            'service_type_id' => ['nullable', 'exists:service_types,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30', 'regex:/^(\+?92|0)3\d{2}-?\d{7}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'vehicle_registration_number' => ['nullable', 'string', 'max:50'],
            'vehicle_make' => ['nullable', 'string', 'max:100'],
            'vehicle_model' => ['nullable', 'string', 'max:100'],
            'requested_at' => ['required', 'date', 'after:now'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'customer_name.required' => 'Please tell us your name.',
            'customer_phone.required' => 'Please enter your phone number.',
            'customer_phone.regex' => 'Enter a valid Pakistani mobile number, e.g. 0300-1234567.',
            'customer_email.email' => 'That doesn\'t look like a valid email address.',
            'requested_at.required' => 'Please choose a date and time for your booking.',
            'requested_at.after' => 'Please choose a time that\'s still in the future.',
        ]);

        Booking::create(array_merge($data, [
            'branch_id' => $branch->id,
            'status' => 'pending',
        ]));

        return redirect()->route('portal.book.show', $branch)
            ->with('status', "Thanks! We've received your booking request — the garage will confirm it shortly.");
    }
}
