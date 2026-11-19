<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Purchase {{ $purchase->purchase_number }}</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="text-xs text-gray-500">Supplier</div>
                    <div class="font-semibold text-gray-900">{{ $purchase->supplier->name }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Date</div>
                    <div class="font-semibold text-gray-900">{{ $purchase->purchased_at->format('d M Y') }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Recorded By</div>
                    <div class="font-semibold text-gray-900">{{ $purchase->purchasedBy->name }}</div>
                </div>
            </div>
            @if ($purchase->supplier_invoice_reference)
                <div class="mt-3 text-sm"><span class="text-gray-500">Supplier Invoice Ref:</span> {{ $purchase->supplier_invoice_reference }}</div>
            @endif
            @if ($purchase->notes)
                <div class="mt-3 text-sm"><span class="text-gray-500">Notes:</span> {{ $purchase->notes }}</div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Unit Cost</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($purchase->items as $item)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $item->item->name }}</td>
                            <td class="px-4 py-3 text-right">{{ $item->quantity }} {{ $item->item->unit }}</td>
                            <td class="px-4 py-3 text-right">PKR {{ number_format($item->unit_cost, 2) }}</td>
                            <td class="px-4 py-3 text-right font-medium">PKR {{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-gray-200">
                        <td colspan="3" class="px-4 py-3 text-right font-bold">Total</td>
                        <td class="px-4 py-3 text-right font-bold">PKR {{ number_format($purchase->total_amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
            </div>
        </div>
    </div>
</x-app-layout>
