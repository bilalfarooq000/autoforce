<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Staff — {{ $staff->name }}</h2>
    </x-slot>

    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.staff.update', $staff) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <x-input-label for="name" value="Name" />
                <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $staff->name)" required />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email', $staff->email)" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="phone" value="Phone" />
                <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $staff->phone)" />
            </div>
            <div>
                <x-input-label for="role" value="Role" />
                <select id="role" name="role" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm" required>
                    @php $currentRole = $staff->roles->first()->name ?? 'technician'; @endphp
                    @foreach (['manager' => 'Manager', 'service_advisor' => 'Service Advisor', 'technician' => 'Technician', 'inventory_manager' => 'Inventory Manager', 'admin' => 'Admin'] as $value => $label)
                        <option value="{{ $value }}" @selected($currentRole === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="branch_id" value="Branch" />
                <select id="branch_id" name="branch_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">— No branch —</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected($staff->branch_id === $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_active" name="is_active" value="1" @checked($staff->is_active) class="rounded border-gray-300">
                <x-input-label for="is_active" value="Active" />
            </div>
            <div x-data="{ show: false }">
                <x-input-label for="password" value="Reset Password (optional)" />
                <div class="relative mt-1">
                    <input id="password" name="password" :type="show ? 'text' : 'password'" class="block w-full rounded-md border-gray-300 shadow-sm pr-16" placeholder="Leave blank to keep current password">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 px-3 text-xs text-gray-500 hover:text-gray-800" x-text="show ? 'Hide' : 'Show'"></button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.staff.index') }}" class="px-4 py-2 text-sm text-gray-600">Cancel</a>
                <x-primary-button>Save Changes</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
