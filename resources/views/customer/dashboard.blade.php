<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Vehicles</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($vehicles as $vehicle)
                <a href="{{ route('customer.vehicles.show', $vehicle) }}" class="bg-white rounded-lg shadow p-5 hover:shadow-md transition">
                    <div class="font-semibold text-lg">{{ $vehicle->registration_number }}</div>
                    <div class="text-sm text-gray-500">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})</div>
                    <div class="text-xs text-gray-500 mt-2">{{ $vehicle->inspections_count }} inspection(s) · {{ $vehicle->invoices_count }} invoice(s)</div>
                </a>
            @empty
                <p class="text-sm text-gray-500">No vehicles linked to your account yet. Visit an AutoForce workshop to register your vehicle.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
