<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Booking</h2>
    </x-slot>

    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <form method="POST" action="{{ route('staff.bookings.store') }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4">
            @csrf

            @if ($branches->isNotEmpty())
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Branch</label>
                    <select name="branch_id" required class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Customer Name</label>
                <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Phone</label>
                    <input type="text" name="customer_phone" value="{{ old('customer_phone') }}" placeholder="03XX-XXXXXXX" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Email (optional)</label>
                    <input type="email" name="customer_email" value="{{ old('customer_email') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Vehicle Reg</label>
                    <input type="text" name="vehicle_registration_number" value="{{ old('vehicle_registration_number') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Make</label>
                    <input type="text" name="vehicle_make" value="{{ old('vehicle_make') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Model</label>
                    <input type="text" name="vehicle_model" value="{{ old('vehicle_model') }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
            </div>

            @if ($serviceTypes->isNotEmpty())
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Service</label>
                    <select name="service_type_id" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">—</option>
                        @foreach ($serviceTypes as $serviceType)
                            <option value="{{ $serviceType->id }}">{{ $serviceType->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Date & Time</label>
                <input type="datetime-local" name="requested_at" value="{{ old('requested_at') }}" required
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Notes (optional)</label>
                <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">{{ old('notes') }}</textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('staff.bookings.index') }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <x-primary-button>Schedule Booking</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
