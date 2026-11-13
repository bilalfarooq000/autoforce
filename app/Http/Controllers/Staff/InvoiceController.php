<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\User;
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
     * Entry point for the "+ Create Invoice" menu action. Billing starts from the
     * customer, not a bare job list — pick a customer, see their full record (every
     * vehicle and job, same as the Customers hub), and initiate the invoice from there.
     */
    public function selectCustomer(Request $request)
    {
        $user = $request->user();
        $branchId = $user->hasRole('admin') ? null : $user->branch_id;

        $customers = User::role('customer')
            ->withCount('vehicles')
            ->when($branchId, fn ($q) => $q->where(fn ($qq) => $qq->where('branch_id', $branchId)
                ->orWhereHas('vehicles', fn ($v) => $v->where('branch_id', $branchId))))
            ->when($request->search, fn ($q, $s) => $q->where(fn ($qq) => $qq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('staff.invoices.select', compact('customers'));
    }

    public function create(Inspection $inspection)
    {
        $inspection->load('vehicle.customer', 'quotation.items');

        return view('staff.invoices.create', compact('inspection'));
    }

    public function store(Request $request, Inspection $inspection)
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
            'vehicle_id' => $inspection->vehicle_id,
            'customer_id' => $inspection->vehicle->customer_id,
            'inspection_id' => $inspection->id,
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

        return redirect()->route('staff.invoices.show', $invoice)
            ->with('status', 'Invoice created. Customer emailed a copy.');
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
