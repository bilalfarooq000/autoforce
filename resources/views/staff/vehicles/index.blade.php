<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Vehicles</h2>
            <a href="{{ route('staff.vehicles.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-red-700">
                + Register Vehicle
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search registration no., make, model or customer"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm" />
            <button class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm">Search</button>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-900 text-left text-gray-300 text-xs font-semibold uppercase tracking-wider border-b-2 border-red-600">
                    <tr class="hover:bg-red-50/50 transition-colors">
                        <th class="px-4 py-3">Registration</th>
                        <th class="px-4 py-3">Vehicle</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Branch</th>
                        <th class="px-4 py-3">Mileage</th>
                        <th class="px-4 py-3">Service</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($vehicles as $vehicle)
                        <tr class="hover:bg-red-50/50 transition-colors">
                            <td class="px-4 py-3 font-medium">{{ $vehicle->registration_number }}</td>
                            <td class="px-4 py-3">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})</td>
                            <td class="px-4 py-3">{{ $vehicle->customer->name }}<br><span class="text-xs text-gray-500">{{ $vehicle->customer->phone }}</span></td>
                            <td class="px-4 py-3">{{ $vehicle->branch->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ number_format($vehicle->mileage) }} km</td>
                            <td class="px-4 py-3">
                                @if ($vehicle->isServiceDueSoon())
                                    <span class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-700">Due Soon</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-action-buttons
                                    :view="route('staff.vehicles.show', $vehicle)"
                                    :edit="route('staff.vehicles.edit', $vehicle)"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No vehicles registered yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        {{ $vehicles->links() }}
    </div>
</x-app-layout>
