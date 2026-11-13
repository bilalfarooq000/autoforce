<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
    h1 { font-size: 20px; margin: 0; color: #111827; }
    .muted { color: #6b7280; }
    .red { color: #dc2626; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 6px 4px; text-align: left; border-bottom: 1px solid #e5e7eb; }
    th { color: #6b7280; font-size: 10px; text-transform: uppercase; }
    .text-right { text-align: right; }
    .totals { width: 240px; float: right; margin-top: 10px; }
    .totals div { display: flex; justify-content: space-between; padding: 3px 0; }
    .totals .grand { font-weight: bold; border-top: 1px solid #1f2937; padding-top: 6px; font-size: 13px; }
    .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 3px solid #dc2626; padding-bottom: 14px; margin-bottom: 16px; }
    .logo { height: 36px; }
    .panel { display: flex; justify-content: space-between; gap: 20px; margin-bottom: 16px; }
    .box { width: 32%; background: #f9fafb; border-radius: 6px; padding: 10px 12px; }
    .box-title { font-size: 10px; text-transform: uppercase; color: #9ca3af; font-weight: bold; margin-bottom: 4px; }
    .box strong { color: #111827; }
    .status-badge { display: inline-block; margin-top: 4px; padding: 2px 10px; border-radius: 10px; font-size: 10px; font-weight: bold; }
    .status-paid { background: #d1fae5; color: #047857; }
    .status-partial { background: #fef3c7; color: #b45309; }
    .status-unpaid { background: #fee2e2; color: #b91c1c; }
    .footer { margin-top: 40px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #9ca3af; text-align: center; }
</style>
</head>
<body>
    <div class="header">
        <div>
            <img class="logo" src="{{ public_path('images/autoforce-logo-dark.png') }}">
            <div class="muted" style="margin-top:4px;">Vehicle Service Invoice</div>
        </div>
        <div style="text-align:right">
            <h1>{{ $invoice->invoice_number }}</h1>
            <div class="muted">Invoice ID: #{{ str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</div>
            <div class="muted">Issued: {{ $invoice->issued_at->format('d M Y') }}</div>
            <span class="status-badge status-{{ $invoice->status }}">{{ str($invoice->status)->headline() }}</span>
        </div>
    </div>

    <div class="panel">
        <div class="box">
            <div class="box-title">Billed To</div>
            <strong>{{ $invoice->customer->name }}</strong><br>
            <span class="muted">{{ $invoice->customer->phone ?? '—' }}</span>
            @unless (str_ends_with($invoice->customer->email, '@customer.autoforce.local'))
                <br><span class="muted">{{ $invoice->customer->email }}</span>
            @endunless
        </div>
        <div class="box">
            <div class="box-title">Vehicle Details</div>
            <strong>{{ $invoice->vehicle->registration_number }}</strong><br>
            <span class="muted">{{ $invoice->vehicle->make }} {{ $invoice->vehicle->model }} ({{ $invoice->vehicle->year ?? '—' }})</span><br>
            <span class="muted">Color: {{ $invoice->vehicle->color ?? '—' }} &nbsp;|&nbsp; Mileage: {{ number_format($invoice->vehicle->mileage) }} km</span><br>
            <span class="muted">Chassis: {{ $invoice->vehicle->chassis_number ?? '—' }}</span>
        </div>
        <div class="box">
            <div class="box-title">Serviced At</div>
            <strong>{{ $invoice->vehicle->branch->name ?? 'AutoForce UK' }}</strong><br>
            <span class="muted">{{ $invoice->vehicle->branch->city ?? '' }}</span><br>
            <span class="muted">{{ $invoice->vehicle->branch->address ?? '' }}</span><br>
            <span class="muted">{{ $invoice->vehicle->branch->phone ?? '' }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th>Type</th>
                <th>Qty</th>
                <th class="text-right">Unit Price</th>
                <th>Warranty</th>
                <th class="text-right">Amount (PKR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ str($item->type)->headline() }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                    <td>{{ $item->warranty_months ? $item->warranty_months.' mo' : '—' }}</td>
                    <td class="text-right">{{ number_format($item->amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div><span>Subtotal</span><span>PKR {{ number_format($invoice->subtotal) }}</span></div>
        <div><span>Tax</span><span>PKR {{ number_format($invoice->tax) }}</span></div>
        <div class="grand"><span>Total</span><span>PKR {{ number_format($invoice->total) }}</span></div>
        <div><span>Paid</span><span class="red" style="color:#047857;">PKR {{ number_format($invoice->amountPaid()) }}</span></div>
        <div><strong>Balance Due</strong><strong class="red">PKR {{ number_format($invoice->balanceDue()) }}</strong></div>
    </div>

    <div style="clear:both;"></div>

    <div class="footer">
        AutoForce UK &middot; This is a system-generated invoice for the vehicle and job referenced above.<br>
        Thank you for choosing AutoForce.
    </div>
</body>
</html>
