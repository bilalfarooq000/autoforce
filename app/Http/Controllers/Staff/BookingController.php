<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\NotificationService;
use App\Support\InspectionChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->hasRole('admin') ? null : $user->branch_id;

        $bookings = Booking::with('branch', 'serviceType', 'inspection')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when(! $request->status, fn ($q) => $q->whereIn('status', ['pending', 'confirmed']))
            ->orderBy('requested_at')
            ->paginate(20)
            ->withQueryString();

        $branches = $user->hasRole('admin') ? Branch::orderBy('name')->get() : collect([$user->branch])->filter();

        return view('staff.bookings.index', compact('bookings', 'branches'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $branches = $user->hasRole('admin') ? Branch::orderBy('name')->get() : collect();

        $branchId = $user->hasRole('admin') ? null : $user->branch_id;
        $serviceTypes = ServiceType::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->orderBy('name')
            ->get();

        return view('staff.bookings.create', compact('branches', 'serviceTypes'));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'branch_id' => [$user->hasRole('admin') ? 'required' : 'nullable', 'exists:branches,id'],
            'service_type_id' => ['nullable', 'exists:service_types,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30', 'regex:/^(\+?92|0)3\d{2}-?\d{7}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'vehicle_registration_number' => ['nullable', 'string', 'max:50'],
            'vehicle_make' => ['nullable', 'string', 'max:100'],
            'vehicle_model' => ['nullable', 'string', 'max:100'],
            'requested_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        Booking::create(array_merge($data, [
            'branch_id' => $user->hasRole('admin') ? $data['branch_id'] : $user->branch_id,
            'status' => 'confirmed',
            'created_by' => $user->id,
        ]));

        return redirect()->route('staff.bookings.index')->with('status', 'Booking scheduled.');
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        $data = $request->validate(['status' => ['required', 'in:confirmed,cancelled,no_show']]);

        $booking->update(['status' => $data['status']]);

        return back()->with('status', 'Booking marked '.$booking->statusLabel().'.');
    }

    /**
     * Small confirmation step before check-in: the booking may be missing
     * vehicle details (a public booker isn't required to give them), so staff
     * fill in whatever's missing right as the customer arrives.
     */
    public function showConvert(Booking $booking)
    {
        return view('staff.bookings.convert', compact('booking'));
    }

    /**
     * The booking becomes the job: resolve (or create) the customer and vehicle
     * the same way walk-in registration does, then open a real Inspection from
     * it — same checklist, same notification, same everything a counter-started
     * job gets.
     */
    public function convertToJob(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'vehicle_registration_number' => ['required', 'string', 'max:50'],
            'vehicle_make' => ['required', 'string', 'max:100'],
            'vehicle_model' => ['required', 'string', 'max:100'],
            'mileage_at_visit' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:255'],
        ]);

        $customer = User::firstOrCreate(
            ['phone' => $booking->customer_phone],
            [
                'name' => $booking->customer_name,
                'email' => $booking->customer_email ?: Str::slug($booking->customer_phone).'@customer.autoforce.local',
                'branch_id' => $booking->branch_id,
                'password' => bcrypt(Str::random(16)),
            ]
        );

        if (! $customer->hasRole('customer')) {
            $customer->assignRole('customer');
        }

        if ($customer->wasRecentlyCreated) {
            NotificationService::notifyNewCustomerAccount($customer);
        }

        $vehicle = Vehicle::firstOrCreate(
            ['registration_number' => $data['vehicle_registration_number']],
            [
                'customer_id' => $customer->id,
                'branch_id' => $booking->branch_id,
                'make' => $data['vehicle_make'],
                'model' => $data['vehicle_model'],
                'mileage' => $data['mileage_at_visit'],
            ]
        );

        $vehicle->update(['mileage' => max($vehicle->mileage, $data['mileage_at_visit'])]);

        $inspection = Inspection::create([
            'vehicle_id' => $vehicle->id,
            'branch_id' => $booking->branch_id,
            'technician_id' => $request->user()->hasRole('technician') ? $request->user()->id : null,
            'created_by' => $request->user()->id,
            'reason' => $data['reason'],
            'mileage_at_visit' => $data['mileage_at_visit'],
            'status' => 'checked_in',
        ]);

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

        $booking->update(['status' => 'completed', 'inspection_id' => $inspection->id]);

        NotificationService::notify($vehicle, 'checked_in', $inspection);

        return redirect()->route('staff.inspections.show', $inspection)
            ->with('status', 'Booking converted — vehicle checked in.');
    }
}
