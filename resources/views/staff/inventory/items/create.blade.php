<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Inventory Item</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('staff.inventory.items.store') }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
            @csrf

            @if ($branches->isNotEmpty())
                <div>
                    <x-input-label for="branch_id" value="Branch" />
                    <select id="branch_id" name="branch_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('branch_id')" class="mt-2" />
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" value="Item Name" />
                    <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name')" required autofocus placeholder="e.g. Brake Pad Set (Front)" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="category_id" value="Category" />
                    <select id="category_id" name="category_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                        <option value="">Select category...</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <x-input-label for="sku" value="SKU (optional)" />
                    <x-text-input id="sku" name="sku" class="block mt-1 w-full" :value="old('sku')" />
                </div>
                <div>
                    <x-input-label for="brand" value="Brand (optional)" />
                    <x-text-input id="brand" name="brand" class="block mt-1 w-full" :value="old('brand')" />
                </div>
                <div>
                    <x-input-label for="part_number" value="Part Number (optional)" />
                    <x-text-input id="part_number" name="part_number" class="block mt-1 w-full" :value="old('part_number')" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <x-input-label for="unit" value="Unit" />
                    <x-text-input id="unit" name="unit" class="block mt-1 w-full" :value="old('unit', 'pcs')" required placeholder="pcs, liter, set..." />
                    <x-input-error :messages="$errors->get('unit')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="cost_price" value="Cost Price (PKR)" />
                    <x-text-input id="cost_price" name="cost_price" type="number" step="0.01" class="block mt-1 w-full" :value="old('cost_price', 0)" required />
                    <x-input-error :messages="$errors->get('cost_price')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="selling_price" value="Selling Price (PKR)" />
                    <x-text-input id="selling_price" name="selling_price" type="number" step="0.01" class="block mt-1 w-full" :value="old('selling_price', 0)" required />
                    <x-input-error :messages="$errors->get('selling_price')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="reorder_level" value="Reorder Level" />
                    <x-text-input id="reorder_level" name="reorder_level" type="number" class="block mt-1 w-full" :value="old('reorder_level', 0)" required />
                    <x-input-error :messages="$errors->get('reorder_level')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="opening_quantity" value="Opening Quantity (optional)" />
                <x-text-input id="opening_quantity" name="opening_quantity" type="number" min="0" class="block mt-1 w-64" :value="old('opening_quantity', 0)" />
                <p class="text-xs text-gray-400 mt-1">Recorded as an initial "Stock In" movement so it shows in the ledger.</p>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('staff.inventory.items.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <x-primary-button>Add Item</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
