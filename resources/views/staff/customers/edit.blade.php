<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Customer — {{ $customer->name }}</h2>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-6 items-start">
            <div class="h-[32rem]">
                @include('staff.customers._sidebar', ['activeId' => $customer->id])
            </div>

            <form method="POST" action="{{ route('staff.customers.update', $customer) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
                @csrf
                @method('PATCH')

                <div>
                    <x-input-label for="name" value="Full Name" />
                    <x-text-input id="name" name="name" class="block mt-1 w-full" :value="old('name', $customer->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="phone" value="Phone (WhatsApp/SMS)" />
                        <x-text-input id="phone" name="phone" class="block mt-1 w-full" :value="old('phone', $customer->phone)" required placeholder="03XX-XXXXXXX" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Email (optional)" />
                        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full"
                                       :value="old('email', str_ends_with($customer->email, '@customer.autoforce.local') ? '' : $customer->email)" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        <p class="text-xs text-gray-400 mt-1">Adding an email here for the first time sends them the portal login link.</p>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-2">
                    <div>
                        @if ($customer->is_active)
                            <button type="submit" form="toggle-customer" class="text-sm text-amber-600 hover:underline">Deactivate Customer</button>
                        @else
                            <button type="submit" form="toggle-customer" class="text-sm text-green-600 hover:underline">Reactivate Customer</button>
                        @endif
                    </div>
                    <div class="flex gap-3">
                        <a href="{{ route('staff.customers.show', $customer) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                        <x-primary-button>Save Changes</x-primary-button>
                    </div>
                </div>
            </form>

            <form id="toggle-customer" method="POST" action="{{ route('staff.customers.toggle', $customer) }}"
                  onsubmit="return confirm('{{ $customer->is_active ? 'Deactivate this customer? They will be hidden from the active roster but their history is kept.' : 'Reactivate this customer?' }}')">
                @csrf
                @method('PATCH')
            </form>
        </div>
    </div>
</x-app-layout>
