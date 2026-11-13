<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit — {{ $item->name }}</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('staff.inventory.items.update', $item) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" value="Item Name" />
                    <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $item->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="category_id" value="Category" />
                    <select id="category_id" name="category_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id', $item->category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-input-label for="sku" value="SKU" />
                    <x-text-input id="sku" name="sku" class="block mt-1 w-full" :value="old('sku', $item->sku)" />
                </div>
                <div>
                    <x-input-label for="brand" value="Brand" />
                    <x-text-input id="brand" name="brand" class="block mt-1 w-full" :value="old('brand', $item->brand)" />
                </div>
                <div>
                    <x-input-label for="part_number" value="Part Number" />
                    <x-text-input id="part_number" name="part_number" class="block mt-1 w-full" :value="old('part_number', $item->part_number)" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <x-input-label for="unit" value="Unit" />
                    <x-text-input id="unit" name="unit" class="block mt-1 w-full" :value="old('unit', $item->unit)" required />
                </div>
                <div>
                    <x-input-label for="cost_price" value="Cost Price (PKR)" />
                    <x-text-input id="cost_price" name="cost_price" type="number" step="0.01" class="block mt-1 w-full" :value="old('cost_price', $item->cost_price)" required />
                </div>
                <div>
                    <x-input-label for="selling_price" value="Selling Price (PKR)" />
                    <x-text-input id="selling_price" name="selling_price" type="number" step="0.01" class="block mt-1 w-full" :value="old('selling_price', $item->selling_price)" required />
                </div>
                <div>
                    <x-input-label for="reorder_level" value="Reorder Level" />
                    <x-text-input id="reorder_level" name="reorder_level" type="number" class="block mt-1 w-full" :value="old('reorder_level', $item->reorder_level)" required />
                </div>
            </div>

            <p class="text-xs text-gray-400">Current stock on hand: <strong>{{ $item->quantity_on_hand }} {{ $item->unit }}</strong> — change it via Receive/Issue/Adjust, not here, so the movement ledger stays accurate.</p>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('staff.inventory.items.show', $item) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <x-primary-button>Save Changes</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
