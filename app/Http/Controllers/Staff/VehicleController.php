<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreVehicleRequest;
use App\Http\Requests\Staff\UpdateVehicleRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $vehicles = Vehicle::with('customer', 'branch')
            ->when(! $request->user()->hasRole('admin'), fn ($q) => $q->where('branch_id', $request->user()->branch_id))
            ->when($request->search, function ($query, $search) {
                $query->where('registration_number', 'like', "%{$search}%")
                    ->orWhere('make', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('staff.vehicles.index', compact('vehicles'));
    }

    public function create(Request $request)
    {
        $customer = null;

        if ($request->filled('customer')) {
            $customer = User::role('customer')->with('vehicles')->findOrFail($request->integer('customer'));

            if (! $request->user()->hasRole('admin')) {
                $branchId = $request->user()->branch_id;
                $belongsToBranch = $customer->branch_id === $branchId || $customer->vehicles->contains('branch_id', $branchId);

                abort_unless($belongsToBranch, 403, 'This customer has no record at your branch.');
            }
        }

        return view('staff.vehicles.create', compact('customer'));
    }

    public function store(StoreVehicleRequest $request)
    {
        $data = $request->validated();

        if (! empty($data['customer_id'])) {
            $customer = User::role('customer')->findOrFail($data['customer_id']);
        } else {
            $customer = User::firstOrCreate(
                ['phone' => $data['customer_phone']],
                [
                    'name' => $data['customer_name'],
                    'email' => $data['customer_email'] ?: Str::slug($data['customer_phone']).'@customer.autoforce.local',
                    'branch_id' => $request->user()->hasRole('admin') ? null : $request->user()->branch_id,
                    'password' => bcrypt(Str::random(16)),
                ]
            );

            if (! $customer->hasRole('customer')) {
                $customer->assignRole('customer');
            }

            if ($customer->wasRecentlyCreated) {
                NotificationService::notifyNewCustomerAccount($customer);
            }
        }

        $photoPath = $request->file('photo')?->store('vehicles', 'public');

        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'branch_id' => $request->user()->branch_id,
            'registration_number' => $data['registration_number'],
            'make' => $data['make'],
            'model' => $data['model'],
            'year' => $data['year'] ?? null,
            'chassis_number' => $data['chassis_number'] ?? null,
            'engine_number' => $data['engine_number'] ?? null,
            'mileage' => $data['mileage'],
            'color' => $data['color'] ?? null,
            'vehicle_type' => $data['vehicle_type'],
            'fuel_type' => $data['fuel_type'],
            'transmission' => $data['transmission'],
            'purchase_date' => $data['purchase_date'] ?? null,
            'insurance_expiry' => $data['insurance_expiry'] ?? null,
            'next_service_due_date' => $data['next_service_due_date'] ?? null,
            'next_service_due_mileage' => $data['next_service_due_mileage'] ?? null,
            'notes' => $data['notes'] ?? null,
            'photo_path' => $photoPath,
        ]);

        return redirect()->route('staff.vehicles.show', $vehicle)
            ->with('status', 'Vehicle registered successfully.');
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load([
            'customer', 'branch',
            'inspections' => fn ($q) => $q->latest(),
            'inspections.technician', 'inspections.creator',
            'inspections.items.media', 'inspections.quotation.items', 'inspections.invoice', 'inspections.diagramMarks',
            'invoices.payments', 'notifications' => fn ($q) => $q->latest()->limit(10),
        ]);

        $activities = \Spatie\Activitylog\Models\Activity::forSubject($vehicle)->latest()->limit(10)->get();

        return view('staff.vehicles.show', compact('vehicle', 'activities'));
    }

    public function edit(Vehicle $vehicle)
    {
        return view('staff.vehicles.edit', compact('vehicle'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        $data = $request->safe()->except(['photo']);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('vehicles', 'public');
        }

        $vehicle->update($data);

        return redirect()->route('staff.vehicles.show', $vehicle)->with('status', 'Vehicle details updated.');
    }

    public function qr(Vehicle $vehicle)
    {
        $url = route('staff.vehicles.show', $vehicle);

        return response(QrCode::format('svg')->size(300)->generate($url))
            ->header('Content-Type', 'image/svg+xml');
    }
}
