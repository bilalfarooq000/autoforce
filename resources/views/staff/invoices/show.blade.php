<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Invoice {{ $invoice->invoice_number }}</h2>
            <a href="{{ route('staff.invoices.pdf', $invoice) }}" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm">Download PDF</a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex justify-between mb-6">
                <div>
                    <div class="font-semibold">{{ $invoice->customer->name }}</div>
                    <div class="text-sm text-gray-500">{{ $invoice->customer->phone }}</div>
                    <div class="text-sm text-gray-500">{{ $invoice->vehicle->registration_number }} — {{ $invoice->vehicle->make }} {{ $invoice->vehicle->model }}</div>
                </div>
                <div class="text-right text-sm">
                    <div>Issued: {{ $invoice->issued_at->format('d M Y') }}</div>
                    <span class="inline-block mt-1 text-xs px-2 py-1 rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                        {{ str($invoice->status)->headline() }}
                    </span>
                </div>
            </div>

            <table class="w-full text-sm">
                <thead class="text-left text-gray-500 border-b"><tr><th class="py-2">Description</th><th>Type</th><th>Qty</th><th>Warranty</th><th class="text-right">Amount</th></tr></thead>
                <tbody class="divide-y">
                    @foreach ($invoice->items as $item)
                        <tr>
                            <td class="py-2">{{ $item->description }}</td>
                            <td>{{ str($item->type)->headline() }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->warranty_months ? $item->warranty_months.' mo' : '—' }}</td>
                            <td class="text-right">PKR {{ number_format($item->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="flex justify-end mt-4">
                <div class="w-56 text-sm space-y-1">
                    <div class="flex justify-between"><span>Subtotal</span><span>PKR {{ number_format($invoice->subtotal) }}</span></div>
                    <div class="flex justify-between"><span>Tax</span><span>PKR {{ number_format($invoice->tax) }}</span></div>
                    <div class="flex justify-between font-semibold text-base border-t pt-1"><span>Total</span><span>PKR {{ number_format($invoice->total) }}</span></div>
                    <div class="flex justify-between text-green-700"><span>Paid</span><span>PKR {{ number_format($invoice->amountPaid()) }}</span></div>
                    <div class="flex justify-between font-semibold {{ $invoice->balanceDue() > 0 ? 'text-red-600' : 'text-green-700' }}"><span>Balance Due</span><span>PKR {{ number_format($invoice->balanceDue()) }}</span></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-semibold text-gray-700">Payments</div>
            <ul class="divide-y text-sm">
                @forelse ($invoice->payments as $payment)
                    <li class="px-4 py-2 flex justify-between">
                        <span>PKR {{ number_format($payment->amount) }} — {{ str($payment->method)->headline() }} {{ $payment->reference ? "(#{$payment->reference})" : '' }}</span>
                        <span class="text-xs text-gray-400">{{ $payment->paid_at->format('d M Y, h:i A') }} · {{ $payment->receiver->name ?? '—' }}</span>
                    </li>
                @empty
                    <li class="px-4 py-6 text-gray-500">No payments recorded yet.</li>
                @endforelse
            </ul>

            @if ($invoice->balanceDue() > 0)
                <form method="POST" action="{{ route('staff.invoices.payments.store', $invoice) }}" class="p-4 border-t flex flex-wrap gap-3 items-end">
                    @csrf
                    <div>
                        <x-input-label value="Amount (PKR)" />
                        <x-text-input name="amount" type="number" step="0.01" max="{{ $invoice->balanceDue() }}" value="{{ $invoice->balanceDue() }}" class="w-40" required />
                    </div>
                    <div>
                        <x-input-label value="Method" />
                        <select name="method" class="rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <x-input-label value="Reference (optional)" />
                        <x-text-input name="reference" class="w-40" />
                    </div>
                    <x-primary-button>Record Payment</x-primary-button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>
