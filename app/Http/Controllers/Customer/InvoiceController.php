<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function pdf(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->customer_id === $request->user()->id, 403);

        $invoice->load('items', 'vehicle.branch', 'customer', 'payments');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('staff.invoices.pdf', compact('invoice'));

        return $pdf->download($invoice->invoice_number.'.pdf');
    }
}
