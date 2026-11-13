<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Invoice — {{ $inspection->vehicle->registration_number }}</h2>
    </x-slot>

    @php
        $quotationItems = $inspection->quotation->items ?? collect();
        $initialItems = $quotationItems->isEmpty()
            ? [['description' => '', 'type' => 'labor', 'quantity' => 1, 'unit_price' => 0, 'warranty_months' => null]]
            : $quotationItems->map(fn ($qi) => [
                'description' => $qi->description,
                'type' => 'labor',
                'quantity' => 1,
                'unit_price' => (float) $qi->amount,
                'warranty_months' => null,
            ])->values()->all();
    @endphp

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="text-xs text-gray-500">Vehicle</div>
                    <div class="font-semibold text-gray-900">{{ $inspection->vehicle->registration_number }} — {{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Customer</div>
                    <div class="font-semibold text-gray-900">{{ $inspection->vehicle->customer->name }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Job Checked In</div>
                    <div class="font-semibold text-gray-900">{{ $inspection->created_at->format('d M Y') }}</div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('staff.invoices.store', $inspection) }}"
              x-data="invoiceForm({{ Js::from($initialItems) }})" @submit="if (!items.length) $event.preventDefault()"
              class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
            @csrf

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                        <tr>
                            <th class="py-2 pr-2">Description</th>
                            <th class="py-2 px-2 w-28">Type</th>
                            <th class="py-2 px-2 w-20">Qty</th>
                            <th class="py-2 px-2 w-28">Unit Price</th>
                            <th class="py-2 px-2 w-28">Warranty (months)</th>
                            <th class="py-2 px-2 w-28 text-right">Amount</th>
                            <th class="py-2 pl-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(item, index) in items" :key="index">
                            <tr class="border-t border-gray-100">
                                <td class="py-2 pr-2">
                                    <input type="text" :name="`items[${index}][description]`" x-model="item.description" placeholder="e.g. Brake pad replacement"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" required>
                                </td>
                                <td class="py-2 px-2">
                                    <select :name="`items[${index}][type]`" x-model="item.type" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                                        <option value="part">Part</option>
                                        <option value="labor">Labor</option>
                                    </select>
                                </td>
                                <td class="py-2 px-2">
                                    <input type="number" min="1" :name="`items[${index}][quantity]`" x-model.number="item.quantity"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" required>
                                </td>
                                <td class="py-2 px-2">
                                    <input type="number" min="0" step="0.01" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" required>
                                </td>
                                <td class="py-2 px-2">
                                    <input type="number" min="0" :name="`items[${index}][warranty_months]`" x-model.number="item.warranty_months" placeholder="optional"
                                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                                </td>
                                <td class="py-2 px-2 text-right font-medium text-gray-900" x-text="formatMoney((item.quantity || 0) * (item.unit_price || 0))"></td>
                                <td class="py-2 pl-2 text-center">
                                    <button type="button" @click="removeRow(index)" x-show="items.length > 1"
                                            class="text-gray-400 hover:text-red-600" title="Remove row">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <button type="button" @click="addRow()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Row
            </button>

            <div class="flex justify-end">
                <div class="w-full sm:w-72 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span x-text="formatMoney(subtotal)"></span>
                    </div>
                    <div class="flex justify-between items-center text-gray-600">
                        <label for="tax">Tax (PKR)</label>
                        <input id="tax" name="tax" type="number" min="0" step="0.01" x-model.number="tax" class="w-28 rounded-lg border-gray-300 text-sm text-right focus:border-red-500 focus:ring-red-500">
                    </div>
                    <div class="flex justify-between text-base font-bold text-gray-900 pt-2 border-t border-gray-200">
                        <span>Total</span>
                        <span x-text="formatMoney(total)"></span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('staff.inspections.show', $inspection) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <x-primary-button>Create Invoice</x-primary-button>
            </div>
        </form>
    </div>

    <script>
        function invoiceForm(initialItems) {
            return {
                items: initialItems,
                tax: 0,
                addRow() {
                    this.items.push({ description: '', type: 'part', quantity: 1, unit_price: 0, warranty_months: null });
                },
                removeRow(index) {
                    this.items.splice(index, 1);
                },
                get subtotal() {
                    return this.items.reduce((sum, item) => sum + ((item.quantity || 0) * (item.unit_price || 0)), 0);
                },
                get total() {
                    return this.subtotal + (this.tax || 0);
                },
                formatMoney(value) {
                    return 'PKR ' + Number(value || 0).toLocaleString('en-PK', { maximumFractionDigits: 2 });
                },
            };
        }
    </script>
</x-app-layout>
