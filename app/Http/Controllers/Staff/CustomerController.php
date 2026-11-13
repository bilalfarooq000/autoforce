<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->user()->hasRole('admin') ? null : $request->user()->branch_id;

        $customers = User::role('customer')
            ->with('vehicles.invoices.payments')
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)
                ->orWhereHas('vehicles', fn ($v) => $v->where('branch_id', $branchId))))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($qq) => $qq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $customers->getCollection()->transform(function (User $customer) {
            $invoices = $customer->vehicles->flatMap->invoices;
            $customer->vehicles_count = $customer->vehicles->count();
            $customer->total_invoiced = $invoices->sum('total');
            $customer->total_outstanding = $invoices->sum(fn ($i) => $i->balanceDue());

            return $customer;
        });

        return view('staff.customers.index', compact('customers'));
    }

    public function create(Request $request)
    {
        $sidebarCustomers = $this->sidebarCustomers($request);

        return view('staff.customers.create', compact('sidebarCustomers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
        ]);

        $customer = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?: Str::slug($data['phone']).'@customer.autoforce.local',
            'branch_id' => $request->user()->hasRole('admin') ? null : $request->user()->branch_id,
            'password' => bcrypt(Str::random(16)),
        ]);

        $customer->assignRole('customer');

        \App\Services\NotificationService::notifyNewCustomerAccount($customer);

        return redirect()->route('staff.customers.show', $customer)
            ->with('status', 'Customer registered. Next, register a vehicle for them.');
    }

    public function show(Request $request, User $customer)
    {
        abort_unless($customer->hasRole('customer'), 404);

        $customer->load([
            'vehicles.branch',
            'vehicles.inspections' => fn ($q) => $q->latest(),
            'vehicles.inspections.quotation', 'vehicles.inspections.invoice',
            'vehicles.invoices.payments',
        ]);

        $customer->vehicles->each(function ($vehicle) {
            $vehicle->inspections->each(function (Inspection $inspection) {
                $inspection->next_action = $this->inspectionNextAction($inspection);
            });
        });

        $timeline = $customer->vehicles
            ->flatMap(fn ($vehicle) => $vehicle->timeline()->map(fn ($event) => $event + ['vehicle' => $vehicle->registration_number]))
            ->sortByDesc('at')
            ->values();

        $totalInvoiced = $customer->vehicles->flatMap->invoices->sum('total');
        $totalOutstanding = $customer->vehicles->flatMap->invoices->sum(fn ($i) => $i->balanceDue());

        $sidebarCustomers = $this->sidebarCustomers($request);

        return view('staff.customers.show', compact('customer', 'timeline', 'totalInvoiced', 'totalOutstanding', 'sidebarCustomers'));
    }

    /**
     * The customer profile is the hub for the Customer -> Vehicle -> Inspection ->
     * Invoice pipeline, so every job on it needs exactly one obvious next step
     * instead of staff having to work out where in the process it's stuck.
     */
    private function inspectionNextAction(Inspection $inspection): array
    {
        if ($inspection->invoice) {
            return [
                'label' => 'View Invoice',
                'url' => route('staff.invoices.show', $inspection->invoice),
                'style' => 'muted',
            ];
        }

        if ($inspection->quotation && $inspection->quotation->status === 'approved') {
            return [
                'label' => 'Create Invoice',
                'url' => route('staff.invoices.create', $inspection),
                'style' => 'primary',
            ];
        }

        if ($inspection->quotation && $inspection->quotation->status === 'pending') {
            return [
                'label' => 'Awaiting Customer Decision',
                'url' => route('staff.quotations.show', $inspection),
                'style' => 'waiting',
            ];
        }

        if (in_array($inspection->status, ['checked_in', 'inspecting'])) {
            return [
                'label' => 'Continue Inspection',
                'url' => route('staff.inspections.show', $inspection),
                'style' => 'secondary',
            ];
        }

        if ($inspection->status === 'awaiting_approval') {
            return [
                'label' => 'Build Quotation',
                'url' => route('staff.quotations.create', $inspection),
                'style' => 'secondary',
                'manager_only' => true,
            ];
        }

        return [
            'label' => 'View Job',
            'url' => route('staff.inspections.show', $inspection),
            'style' => 'muted',
        ];
    }

    /**
     * Compact, branch-scoped customer list shown alongside the Add Customer form
     * and the customer detail page, so staff never lose sight of the rest of the
     * roster while registering someone new or drilling into one profile.
     */
    private function sidebarCustomers(Request $request)
    {
        $branchId = $request->user()->hasRole('admin') ? null : $request->user()->branch_id;

        return User::role('customer')
            ->withCount('vehicles')
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)
                ->orWhereHas('vehicles', fn ($v) => $v->where('branch_id', $branchId))))
            ->when($request->get('q'), fn ($q, $s) => $q->where(fn ($qq) => $qq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%")))
            ->latest()
            ->paginate(10, ['*'], 'list_page')
            ->withQueryString();
    }
}
