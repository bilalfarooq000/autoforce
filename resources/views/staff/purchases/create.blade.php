<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Record Purchase</h2>
    </x-slot>

    @php
        $itemsPayload = $items->map(fn ($i) => ['id' => $i->id, 'name' => $i->name, 'unit' => $i->unit, 'cost_price' => $i->cost_price]);
        $initialRows = [['item_id' => '', 'quantity' => 1, 'unit_cost' => 0]];
    @endphp

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('staff.purchases.store') }}"
              x-data="purchaseForm({{ Js::from($itemsPayload) }}, {{ Js::from($initialRows) }})"
              @submit="if (!rows.length) $event.preventDefault()"
              class="space-y-4">
            @csrf

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <label class="block text-xs font-medium text-gray-500">Supplier</label>
                    <a href="{{ route('staff.suppliers.create') }}" class="text-xs text-red-600 hover:underline">+ New Supplier</a>
                </div>
                <select name="supplier_id" required class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">— Select Supplier —</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                    @endforeach
                </select>
                @error('supplier_id') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Supplier Invoice Reference (optional)</label>
                    <input type="text" name="supplier_invoice_reference" value="{{ old('supplier_invoice_reference') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                <h3 class="font-semibold text-gray-900">Items Received</h3>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                            <tr>
                                <th class="py-2 pr-2">Item</th>
                                <th class="py-2 px-2 w-24">Qty</th>
                                <th class="py-2 px-2 w-32">Unit Cost</th>
                                <th class="py-2 px-2 w-32 text-right">Amount</th>
                                <th class="py-2 pl-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="index">
                                <tr class="border-t border-gray-100">
                                    <td class="py-2 pr-2">
                                        <select :name="`items[${index}][item_id]`" x-model="row.item_id" @change="onItemChange(row)" required
                                                class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                                            <option value="">— Select Item —</option>
                                            <template x-for="i in inventoryItems" :key="i.id">
                                                <option :value="i.id" x-text="i.name"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-2 px-2">
                                        <input type="number" min="1" :name="`items[${index}][quantity]`" x-model.number="row.quantity"
                                               class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" required>
                                    </td>
                                    <td class="py-2 px-2">
                                        <input type="number" min="0" step="0.01" :name="`items[${index}][unit_cost]`" x-model.number="row.unit_cost"
                                               class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" required>
                                    </td>
                                    <td class="py-2 px-2 text-right font-medium text-gray-900" x-text="formatMoney((row.quantity || 0) * (row.unit_cost || 0))"></td>
                                    <td class="py-2 pl-2 text-center">
                                        <button type="button" @click="removeRow(index)" x-show="rows.length > 1" class="text-gray-400 hover:text-red-600">
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

                <div class="flex justify-end text-base font-bold text-gray-900 pt-2 border-t border-gray-200">
                    <span>Total:&nbsp;</span><span x-text="formatMoney(total)"></span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Notes (optional)</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('staff.purchases.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <x-primary-button>Record Purchase &amp; Receive Stock</x-primary-button>
            </div>
        </form>
    </div>

    <script>
        function purchaseForm(inventoryItems, initialRows) {
            return {
                inventoryItems: inventoryItems,
                rows: initialRows,
                addRow() {
                    this.rows.push({ item_id: '', quantity: 1, unit_cost: 0 });
                },
                removeRow(index) {
                    this.rows.splice(index, 1);
                },
                onItemChange(row) {
                    const item = this.inventoryItems.find(i => String(i.id) === String(row.item_id));
                    if (item) row.unit_cost = Number(item.cost_price);
                },
                get total() {
                    return this.rows.reduce((sum, row) => sum + ((row.quantity || 0) * (row.unit_cost || 0)), 0);
                },
                formatMoney(value) {
                    return 'PKR ' + Number(value || 0).toLocaleString('en-PK', { maximumFractionDigits: 2 });
                },
            };
        }
    </script>
</x-app-layout>
