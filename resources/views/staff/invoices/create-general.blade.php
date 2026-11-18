<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Invoice</h2>
    </x-slot>

    @php
        $initialItems = [['description' => '', 'type' => 'labor', 'quantity' => 1, 'unit_price' => 0, 'warranty_months' => null]];
        $customersPayload = $customers->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'phone' => $c->phone,
            'vehicles' => $c->vehicles->map(fn ($v) => [
                'id' => $v->id,
                'label' => $v->registration_number.' — '.$v->make.' '.$v->model,
            ]),
        ]);
    @endphp

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8"
         x-data="invoiceCreateForm({{ Js::from($customersPayload) }}, {{ Js::from($initialItems) }}, {{ Js::from($serviceTypes) }})"
         @submit="if (!items.length) $event.preventDefault()">
        <form method="POST" action="{{ route('staff.invoices.store-general') }}" class="space-y-4">
            @csrf

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h3 class="font-semibold text-gray-900">Customer</h3>
                    <div class="flex rounded-lg border border-gray-200 overflow-hidden text-xs font-medium">
                        <button type="button" @click="newCustomer = false"
                                :class="!newCustomer ? 'bg-gray-900 text-white' : 'bg-white text-gray-600'" class="px-3 py-1.5">Existing</button>
                        <button type="button" @click="newCustomer = true"
                                :class="newCustomer ? 'bg-gray-900 text-white' : 'bg-white text-gray-600'" class="px-3 py-1.5">+ New</button>
                    </div>
                </div>

                <template x-if="!newCustomer">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Select Customer</label>
                        <select name="customer_id" x-model="customerId" @change="onCustomerChange()"
                                :required="!newCustomer"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">— Select Existing Customer —</option>
                            <template x-for="c in customers" :key="c.id">
                                <option :value="c.id" x-text="c.name + ' (' + c.phone + ')'"></option>
                            </template>
                        </select>
                    </div>
                </template>

                <template x-if="newCustomer">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Name</label>
                            <input type="text" name="customer_name" x-model="newCustomerName" :required="newCustomer"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Phone</label>
                            <input type="text" name="customer_phone" placeholder="03XX-XXXXXXX" :required="newCustomer"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Email (optional)</label>
                            <input type="email" name="customer_email"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                    </div>
                </template>
                @error('customer_name') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                @error('customer_phone') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h3 class="font-semibold text-gray-900">Vehicle</h3>
                    <div class="flex rounded-lg border border-gray-200 overflow-hidden text-xs font-medium">
                        <button type="button" @click="newVehicle = false"
                                :class="!newVehicle ? 'bg-gray-900 text-white' : 'bg-white text-gray-600'" class="px-3 py-1.5">Existing</button>
                        <button type="button" @click="newVehicle = true"
                                :class="newVehicle ? 'bg-gray-900 text-white' : 'bg-white text-gray-600'" class="px-3 py-1.5">+ New</button>
                    </div>
                </div>

                <template x-if="!newVehicle">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Select Vehicle</label>
                        <select name="vehicle_id" x-model="vehicleId" :required="!newVehicle"
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">— Select Vehicle —</option>
                            <template x-for="v in currentVehicles" :key="v.id">
                                <option :value="v.id" x-text="v.label"></option>
                            </template>
                        </select>
                        <p class="text-xs text-gray-400 mt-1" x-show="!newCustomer && customerId && currentVehicles.length === 0">
                            This customer has no vehicles yet — switch to "+ New" above.
                        </p>
                    </div>
                </template>

                <template x-if="newVehicle">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Registration No.</label>
                            <input type="text" name="vehicle_registration_number" :required="newVehicle"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Make</label>
                            <input type="text" name="vehicle_make" :required="newVehicle"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-500 mb-1">Model</label>
                            <input type="text" name="vehicle_model" :required="newVehicle"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        </div>
                    </div>
                </template>
                @error('vehicle_registration_number') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
                <h3 class="font-semibold text-gray-900">Items</h3>

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
                                        <input type="text" :name="`items[${index}][description]`" x-model="item.description" placeholder="e.g. Engine oil change"
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

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="addRow()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Add Row
                    </button>

                    <template x-if="serviceTypes.length">
                        <select @change="addFromServiceType($event.target.value); $event.target.value = ''"
                                class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                            <option value="">+ Add from Service Type…</option>
                            <template x-for="s in serviceTypes" :key="s.id">
                                <option :value="s.id" x-text="s.name + ' — PKR ' + Number(s.default_price).toLocaleString()"></option>
                            </template>
                        </select>
                    </template>
                </div>

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
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('staff.invoices.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <x-primary-button>Create Invoice</x-primary-button>
            </div>
        </form>
    </div>

    <script>
        function invoiceCreateForm(customers, initialItems, serviceTypes) {
            return {
                customers: customers,
                items: initialItems,
                serviceTypes: serviceTypes,
                tax: 0,
                newCustomer: false,
                newVehicle: false,
                customerId: '',
                vehicleId: '',
                newCustomerName: '',
                currentVehicles: [],
                onCustomerChange() {
                    const customer = this.customers.find(c => String(c.id) === String(this.customerId));
                    this.currentVehicles = customer ? customer.vehicles : [];
                    this.vehicleId = '';
                    this.newVehicle = this.currentVehicles.length === 0 && this.customerId !== '';
                },
                addRow() {
                    this.items.push({ description: '', type: 'part', quantity: 1, unit_price: 0, warranty_months: null });
                },
                addFromServiceType(id) {
                    const service = this.serviceTypes.find(s => String(s.id) === String(id));
                    if (!service) return;
                    this.items.push({ description: service.name, type: service.type, quantity: 1, unit_price: Number(service.default_price), warranty_months: null });
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
