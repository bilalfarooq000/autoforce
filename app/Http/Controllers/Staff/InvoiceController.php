<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\ServiceType;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branchId = $user->hasRole('admin') ? null : $user->branch_id;

        $invoices = Invoice::with('vehicle', 'customer', 'payments')
            ->when($branchId, fn ($q) => $q->whereHas('vehicle', fn ($v) => $v->where('branch_id', $branchId)))
            ->when($request->search, fn ($q, $s) => $q->where('invoice_number', 'like', "%{$s}%")
                ->orWhereHas('vehicle', fn ($v) => $v->where('registration_number', 'like', "%{$s}%"))
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$s}%")))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('staff.invoices.index', compact('invoices'));
    }

    /**
     * Entry point for the "+ Create Invoice" menu action: one page covering
     * customer, vehicle, and line items together, instead of making billing a
     * multi-step hunt through the Customers hub first. Either side (customer,
     * vehicle) can be an existing record or a brand-new one, same inline-create
     * pattern already used on vehicle registration.
     */
    public function createGeneral(Request $request)
    {
        $user = $request->user();
        $branchId = $user->hasRole('admin') ? null : $user->branch_id;

        $customers = User::role('customer')
            ->with(['vehicles' => fn ($q) => $q->orderBy('registration_number')])
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)
                ->orWhereHas('vehicles', fn ($v) => $v->where('branch_id', $branchId))))
            ->orderBy('name')
            ->get();

        $serviceTypes = ServiceType::where('is_active', true)
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)->orWhereNull('branch_id')))
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'default_price']);

        return view('staff.invoices.create-general', compact('customers', 'serviceTypes'));
    }

    public function storeGeneral(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:users,id'],
            'customer_name' => ['required_without:customer_id', 'nullable', 'string', 'max:255'],
            'customer_phone' => ['required_without:customer_id', 'nullable', 'string', 'max:30', 'regex:/^(\+?92|0)3\d{2}-?\d{7}$/'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'vehicle_registration_number' => ['required_without:vehicle_id', 'nullable', 'string', 'max:50'],
            'vehicle_make' => ['required_without:vehicle_id', 'nullable', 'string', 'max:100'],
            'vehicle_model' => ['required_without:vehicle_id', 'nullable', 'string', 'max:100'],
        ]);

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

        if (! empty($data['vehicle_id'])) {
            $vehicle = Vehicle::where('customer_id', $customer->id)->findOrFail($data['vehicle_id']);
        } else {
            $existing = Vehicle::where('registration_number', $data['vehicle_registration_number'])->first();

            if ($existing && $existing->customer_id !== $customer->id) {
                return back()->withInput()->withErrors([
                    'vehicle_registration_number' => "{$data['vehicle_registration_number']} is already registered to a different customer ({$existing->customer->name}).",
                ]);
            }

            $vehicle = Vehicle::firstOrCreate(
                ['registration_number' => $data['vehicle_registration_number']],
                [
                    'customer_id' => $customer->id,
                    'branch_id' => $request->user()->branch_id,
                    'make' => $data['vehicle_make'],
                    'model' => $data['vehicle_model'],
                ]
            );
        }

        $invoice = $this->createInvoiceFromRequest($request, $vehicle->id, $customer->id, null);

        return redirect()->route('staff.invoices.show', $invoice)
            ->with('status', 'Invoice created. Customer emailed a copy.');
    }

    public function create(Inspection $inspection)
    {
        if ($inspection->invoice) {
            return redirect()->route('staff.invoices.show', $inspection->invoice);
        }

        $inspection->load('vehicle.customer', 'quotation.items');

        return view('staff.invoices.create', compact('inspection'));
    }

    public function store(Request $request, Inspection $inspection)
    {
        if ($inspection->invoice) {
            return redirect()->route('staff.invoices.show', $inspection->invoice)
                ->with('status', 'This job already has an invoice.');
        }

        $invoice = $this->createInvoiceFromRequest($request, $inspection->vehicle_id, $inspection->vehicle->customer_id, $inspection->id);

        return redirect()->route('staff.invoices.show', $invoice)
            ->with('status', 'Invoice created. Customer emailed a copy.');
    }

    /**
     * Walk-in / counter-sale billing: a customer needs something invoiced (a part,
     * a quick job) without going through the full inspection -> quotation ->
     * approval pipeline. Still tied to one of their vehicles, since every invoice
     * belongs to a vehicle, but with no inspection behind it.
     */
    public function createQuick(Vehicle $vehicle)
    {
        $vehicle->load('customer');

        $serviceTypes = ServiceType::where('is_active', true)
            ->when($vehicle->branch_id, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $vehicle->branch_id)->orWhereNull('branch_id')))
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'default_price']);

        return view('staff.invoices.create-quick', compact('vehicle', 'serviceTypes'));
    }

    public function storeQuick(Request $request, Vehicle $vehicle)
    {
        $invoice = $this->createInvoiceFromRequest($request, $vehicle->id, $vehicle->customer_id, null);

        return redirect()->route('staff.invoices.show', $invoice)
            ->with('status', 'Invoice created. Customer emailed a copy.');
    }

    private function createInvoiceFromRequest(Request $request, int $vehicleId, int $customerId, ?int $inspectionId): Invoice
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.type' => ['required', 'in:part,labor'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.warranty_months' => ['nullable', 'integer', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
        ]);

        $subtotal = 0;
        foreach ($data['items'] as $row) {
            $subtotal += $row['quantity'] * $row['unit_price'];
        }
        $tax = $data['tax'] ?? 0;

        $invoice = Invoice::create([
            'invoice_number' => 'AF-'.now()->format('Ymd').'-'.Str::upper(Str::random(4)),
            'vehicle_id' => $vehicleId,
            'customer_id' => $customerId,
            'inspection_id' => $inspectionId,
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $subtotal + $tax,
            'status' => 'unpaid',
            'issued_at' => now(),
        ]);

        foreach ($data['items'] as $row) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $row['description'],
                'type' => $row['type'],
                'quantity' => $row['quantity'],
                'unit_price' => $row['unit_price'],
                'amount' => $row['quantity'] * $row['unit_price'],
                'warranty_months' => $row['warranty_months'] ?? null,
            ]);
        }

        NotificationService::notifyInvoiceIssued($invoice);

        return $invoice;
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('items', 'vehicle.branch', 'customer', 'payments.receiver');

        return view('staff.invoices.show', compact('invoice'));
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$invoice->balanceDue()],
            'method' => ['required', 'in:cash,card,bank_transfer,other'],
            'reference' => ['nullable', 'string', 'max:100'],
        ]);

        Payment::create([
            'invoice_id' => $invoice->id,
            'received_by' => $request->user()->id,
            'amount' => $data['amount'],
            'method' => $data['method'],
            'reference' => $data['reference'] ?? null,
            'paid_at' => now(),
        ]);

        $invoice->update([
            'status' => $invoice->balanceDue() <= 0 ? 'paid' : 'partial',
        ]);

        return back()->with('status', 'Payment recorded.');
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load('items', 'vehicle.branch', 'customer', 'payments');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('staff.invoices.pdf', compact('invoice'));

        return $pdf->download($invoice->invoice_number.'.pdf');
    }
}
