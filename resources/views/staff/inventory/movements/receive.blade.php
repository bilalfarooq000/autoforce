<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Receive Stock — {{ $item->name }}</h2>
    </x-slot>

    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-4 text-sm">
            <div class="text-xs text-gray-500">Current stock</div>
            <div class="text-2xl font-bold text-gray-900">{{ $item->quantity_on_hand }} {{ $item->unit }}</div>
        </div>

        <form method="POST" action="{{ route('staff.inventory.items.receive.store', $item) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
            @csrf
            <div>
                <x-input-label for="quantity" value="Quantity Received" />
                <x-text-input id="quantity" name="quantity" type="number" min="1" class="block mt-1 w-full" required autofocus />
                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="unit_cost" value="Unit Cost (PKR, optional)" />
                <x-text-input id="unit_cost" name="unit_cost" type="number" step="0.01" class="block mt-1 w-full" :value="$item->cost_price" />
            </div>
            <div>
                <x-input-label for="reference" value="Supplier / Reference (optional)" />
                <x-text-input id="reference" name="reference" class="block mt-1 w-full" placeholder="e.g. ABC Auto Parts, PO#1234" />
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('staff.inventory.items.show', $item) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <x-primary-button>Receive Stock</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
