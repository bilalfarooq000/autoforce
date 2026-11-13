<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Adjust Stock — {{ $item->name }}</h2>
    </x-slot>

    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-4 text-sm">
            <div class="text-xs text-gray-500">Current stock</div>
            <div class="text-2xl font-bold text-gray-900">{{ $item->quantity_on_hand }} {{ $item->unit }}</div>
        </div>

        <form method="POST" action="{{ route('staff.inventory.items.adjust.store', $item) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
            @csrf
            <div>
                <x-input-label value="Direction" />
                <div class="flex gap-4 mt-1">
                    <label class="flex items-center gap-1.5 text-sm"><input type="radio" name="direction" value="in" checked> Increase (found extra stock)</label>
                    <label class="flex items-center gap-1.5 text-sm"><input type="radio" name="direction" value="out"> Decrease (damaged / lost)</label>
                </div>
                <x-input-error :messages="$errors->get('direction')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="quantity" value="Quantity" />
                <x-text-input id="quantity" name="quantity" type="number" min="1" class="block mt-1 w-full" required autofocus />
                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="reference" value="Reason" />
                <x-text-input id="reference" name="reference" class="block mt-1 w-full" required placeholder="e.g. Stock-take correction, damaged in transit" />
                <x-input-error :messages="$errors->get('reference')" class="mt-2" />
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('staff.inventory.items.show', $item) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <x-primary-button>Save Adjustment</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
