<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Add Staff Account</h2>
    </x-slot>

    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.staff.store') }}" class="bg-white rounded-lg shadow p-6 space-y-4">
            @csrf
            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name')" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email')" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" value="Phone" />
                <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone')" />
            </div>
            <div>
                <x-input-label for="role" value="Role" />
                <select id="role" name="role" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                    <option value="manager">Manager</option>
                    <option value="service_advisor">Service Advisor</option>
                    <option value="technician">Technician</option>
                    <option value="inventory_manager">Inventory Manager</option>
                    <option value="admin">Admin</option>
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="branch_id" value="Branch" />
                <select id="branch_id" name="branch_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="password" value="Temporary Password" />
                <x-text-input id="password" name="password" type="text" class="block mt-1 w-full" required />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.staff.index') }}" class="px-4 py-2 text-sm text-gray-600">Cancel</a>
                <x-primary-button>Create Account</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
