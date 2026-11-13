<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Inspection — {{ $vehicle->registration_number }}</h2>
    </x-slot>

    <div class="max-w-2xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('staff.inspections.store', $vehicle) }}" class="bg-white rounded-lg shadow p-6 space-y-4">
            @csrf

            <div>
                <x-input-label for="reason" value="Reason for Visit" />
                <x-text-input id="reason" name="reason" class="block mt-1 w-full" :value="old('reason')" required placeholder="e.g. Suspension noise complaint" />
                <x-input-error :messages="$errors->get('reason')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="mileage_at_visit" value="Mileage at Visit (km)" />
                <x-text-input id="mileage_at_visit" name="mileage_at_visit" type="number" class="block mt-1 w-full" :value="old('mileage_at_visit', $vehicle->mileage)" required />
                <x-input-error :messages="$errors->get('mileage_at_visit')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('staff.vehicles.show', $vehicle) }}" class="px-4 py-2 text-sm text-gray-600">Cancel</a>
                <x-primary-button>Start Inspection</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
