<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Check In — {{ $booking->customer_name }}</h2>
    </x-slot>

    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <p class="text-sm text-gray-500 mb-4">Confirm the vehicle details to open a job — this creates (or reuses) the customer and vehicle record, then checks the vehicle in exactly like a counter walk-in.</p>

        <form method="POST" action="{{ route('staff.bookings.convert.store', $booking) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Vehicle Reg</label>
                    <input type="text" name="vehicle_registration_number" value="{{ old('vehicle_registration_number', $booking->vehicle_registration_number) }}" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Make</label>
                    <input type="text" name="vehicle_make" value="{{ old('vehicle_make', $booking->vehicle_make) }}" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Model</label>
                    <input type="text" name="vehicle_model" value="{{ old('vehicle_model', $booking->vehicle_model) }}" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Current Mileage (km)</label>
                <input type="number" name="mileage_at_visit" value="{{ old('mileage_at_visit', 0) }}" min="0" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                @error('mileage_at_visit') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Reason for Visit</label>
                <input type="text" name="reason" value="{{ old('reason', $booking->serviceType->name ?? $booking->notes) }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('staff.bookings.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <x-primary-button>Check In &amp; Start Job</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
