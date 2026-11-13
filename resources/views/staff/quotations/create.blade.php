<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Build Quotation — {{ $inspection->vehicle->registration_number }}</h2>
    </x-slot>

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

        <form method="POST" action="{{ route('staff.quotations.store', $inspection) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-3">
            @csrf
            <p class="text-xs text-gray-500">Add whatever repair work is needed and its cost — this is a flexible quotation, not tied to the checklist. Leave rows blank if not needed.</p>
            @for ($i = 0; $i < 6; $i++)
                <div class="flex items-center gap-3">
                    <input type="text" name="items[{{ $i }}][description]" class="flex-1 rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" placeholder="e.g. Front suspension bush replacement" />
                    <input type="number" name="items[{{ $i }}][amount]" step="0.01" class="w-32 rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" placeholder="Amount (PKR)" />
                </div>
            @endfor
            <div class="flex justify-between items-center pt-2">
                <a href="{{ route('staff.customers.show', $inspection->vehicle->customer_id) }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <x-primary-button>Send Quotation to Customer</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
