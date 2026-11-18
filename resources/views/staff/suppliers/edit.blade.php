<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Supplier</h2>
    </x-slot>

    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('staff.suppliers.update', $supplier) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
            @csrf @method('PUT')

            @if ($branches->isNotEmpty())
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Branch</label>
                    <select name="branch_id" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id', $supplier->branch_id) == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Supplier Name</label>
                <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Contact Person</label>
                <input type="text" name="contact_person" value="{{ old('contact_person', $supplier->contact_person) }}"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Address</label>
                <textarea name="address" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">{{ old('address', $supplier->address) }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('staff.suppliers.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <x-primary-button>Save</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
