<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Register Customer</h2>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-6 items-start">
            <div class="h-[32rem]">
                @include('staff.customers._sidebar', ['activeId' => null])
            </div>

            <form method="POST" action="{{ route('staff.customers.store') }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
                @csrf

                <p class="text-sm text-gray-500">Register the customer first. Once saved, you'll register a vehicle for them, then build their inspection.</p>

                <div>
                    <x-input-label for="name" value="Full Name" />
                    <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name')" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="phone" value="Phone (WhatsApp/SMS)" />
                        <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone')" required placeholder="03XX-XXXXXXX" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Email (optional)" />
                        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email')" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        <p class="text-xs text-gray-400 mt-1">If provided, they'll be emailed a link to set up their customer portal login.</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <a href="{{ route('staff.customers.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                    <x-primary-button>Register Customer</x-primary-button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
