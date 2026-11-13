<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Register Branch</h2>
    </x-slot>

    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('admin.branches.store') }}" class="bg-white rounded-lg shadow p-6 space-y-4">
            @csrf
            <div>
                <x-input-label for="name" value="Branch Name" />
                <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name')" required placeholder="AutoForce Islamabad" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="city" value="City" />
                <x-text-input id="city" name="city" class="block mt-1 w-full" :value="old('city')" required />
                <x-input-error :messages="$errors->get('city')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="address" value="Address" />
                <x-text-input id="address" name="address" class="block mt-1 w-full" :value="old('address')" />
            </div>
            <div>
                <x-input-label for="phone" value="Phone" />
                <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone')" />
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.branches.index') }}" class="px-4 py-2 text-sm text-gray-600">Cancel</a>
                <x-primary-button>Register Branch</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
