<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Build Quotation — {{ $inspection->vehicle->registration_number }}</h2>
    </x-slot>

    @php
        $initialItems = $findings->isNotEmpty() ? $findings->values()->all() : [['description' => '', 'type' => 'labor', 'amount' => null]];
    @endphp

    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="text-xs text-gray-500">Vehicle</div>
                    <div class="font-semibold text-gray-900">{{ $inspection->vehicle->registration_number }} — {{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Customer</div>
                    <a href="{{ route('staff.customers.show', $inspection->vehicle->customer_id) }}" class="font-semibold text-red-600 hover:underline">{{ $inspection->vehicle->customer->name }}</a>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Job</div>
                    <a href="{{ route('staff.inspections.show', $inspection) }}" class="font-semibold text-gray-900 hover:underline">{{ $inspection->reason }}</a>
                </div>
            </div>
        </div>

        @error('items')
            <div class="rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ $message }}</div>
        @enderror

        @if ($findings->isNotEmpty())
            <div class="rounded-md bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm">
                Pre-filled from {{ $findings->count() }} priced finding(s) from the inspection — adjust or remove rows as needed.
            </div>
        @endif

        <form method="POST" action="{{ route('staff.quotations.store', $inspection) }}"
              x-data="quotationForm({{ Js::from(old('items', $initialItems)) }}, {{ Js::from($serviceTypes) }})"
              @submit="if (!items.length) $event.preventDefault()"
              class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-3">
            @csrf
            <p class="text-xs text-gray-500">Add whatever repair work is needed and its cost — this is a flexible quotation, not tied to the checklist.</p>

            <div class="space-y-2">
                <template x-for="(item, index) in items" :key="index">
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text" :name="`items[${index}][description]`" x-model="item.description"
                               class="flex-1 min-w-[160px] rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" placeholder="e.g. Front suspension bush replacement" />
                        <select :name="`items[${index}][type]`" x-model="item.type" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                            <option value="part">Part</option>
                            <option value="labor">Labor</option>
                        </select>
                        <input type="number" :name="`items[${index}][amount]`" x-model.number="item.amount" step="0.01"
                               class="w-32 rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" placeholder="Amount (PKR)" />
                        <button type="button" @click="removeRow(index)" x-show="items.length > 1"
                                class="text-gray-400 hover:text-red-600 shrink-0" title="Remove row">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>

            <div class="flex flex-wrap items-center gap-2 pt-1">
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

            <div class="flex justify-end pt-1">
                <div class="text-sm font-semibold text-gray-900" x-text="'Total: PKR ' + total.toLocaleString()"></div>
            </div>

            <div class="flex justify-between items-center pt-2">
                <a href="{{ route('staff.customers.show', $inspection->vehicle->customer_id) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <x-primary-button>Send Quotation to Customer</x-primary-button>
            </div>
        </form>
    </div>

    <script>
        function quotationForm(initialItems, serviceTypes) {
            return {
                items: initialItems,
                serviceTypes: serviceTypes,
                addRow() {
                    this.items.push({ description: '', type: 'part', amount: null });
                },
                removeRow(index) {
                    this.items.splice(index, 1);
                },
                addFromServiceType(id) {
                    const service = this.serviceTypes.find(s => String(s.id) === String(id));
                    if (!service) return;
                    this.items.push({ description: service.name, type: service.type, amount: Number(service.default_price) });
                },
                get total() {
                    return this.items.reduce((sum, item) => sum + (Number(item.amount) || 0), 0);
                },
            };
        }
    </script>
</x-app-layout>
