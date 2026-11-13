<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Workshop Dashboard @if ($viewingBranch) — {{ $viewingBranch->name }} @endif
                </h2>
                @hasrole('admin')
                    @if ($viewingBranch)
                        <a href="{{ route('admin.dashboard') }}" class="text-xs text-red-600 hover:underline">← Back to company overview</a>
                    @else
                        <span class="text-xs text-gray-500">All branches (head office view)</span>
                    @endif
                @endhasrole
            </div>
            <a href="{{ route('staff.vehicles.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-gray-800">
                + Register Vehicle
            </a>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
            <x-stat-card label="In Workshop" :value="$stats['vehicles_in_workshop']" icon="car" accent="slate" />
            <x-stat-card label="Awaiting Diagnosis" :value="$stats['awaiting_diagnosis']" icon="clock" accent="amber" />
            <x-stat-card label="Awaiting Approval" :value="$stats['awaiting_approval']" icon="clipboard" accent="orange" />
            <x-stat-card label="In Repair" :value="$stats['in_repair']" icon="wrench" accent="blue" />
            <x-stat-card label="Ready for Collection" :value="$stats['ready_for_collection']" icon="check-circle" accent="green" />
            <x-stat-card label="Outstanding Invoices" :value="$stats['outstanding_invoices']" icon="receipt" accent="red" />
            <x-stat-card label="Outstanding (PKR)" :value="number_format($stats['outstanding_amount'], 0)" icon="cash" accent="red" />
            <x-stat-card label="Low Stock Items" :value="$stats['low_stock_items']" icon="wrench" accent="red" />
        </div>

        <div>
            <h3 class="font-semibold text-gray-800 mb-3">Workshop Job Board</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-3">
                @foreach ($board as $status => $column)
                    @if ($status !== 'completed')
                        <div class="bg-gray-50/60 rounded-xl border border-gray-200">
                            <div class="px-3 py-2.5 border-b border-gray-200 flex justify-between items-center">
                                <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">{{ $column['label'] }}</span>
                                <span class="text-xs bg-gray-200 text-gray-700 rounded-full px-2 py-0.5 font-semibold">{{ $column['items']->count() }}</span>
                            </div>
                            <div class="p-2 space-y-2 max-h-96 overflow-y-auto">
                                @forelse ($column['items'] as $inspection)
                                    <a href="{{ route('staff.inspections.show', $inspection) }}" class="block bg-white rounded-lg border border-gray-100 shadow-sm p-2.5 hover:shadow-md hover:border-gray-200 transition">
                                        <div class="text-sm font-medium text-gray-900">{{ $inspection->vehicle->registration_number }}</div>
                                        <div class="text-xs text-gray-500">{{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }}</div>
                                        <div class="text-xs text-gray-400">{{ $inspection->vehicle->customer->name }}</div>
                                        @if ($inspection->technician)
                                            <div class="text-xs text-red-600 font-medium mt-1">{{ $inspection->technician->name }}</div>
                                        @endif
                                    </a>
                                @empty
                                    <p class="text-xs text-gray-400 px-1 py-2">Empty</p>
                                @endforelse
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-800">Technician Performance</div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                        <tr><th class="px-4 py-3">Technician</th><th class="px-4 py-3">Active</th><th class="px-4 py-3">Completed</th><th class="px-4 py-3">Avg Turnaround</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($technicianPerformance as $tech)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $tech['name'] }}</td>
                                <td class="px-4 py-3">{{ $tech['active_jobs'] }}</td>
                                <td class="px-4 py-3">{{ $tech['completed_jobs'] }}</td>
                                <td class="px-4 py-3">{{ $tech['avg_turnaround_hours'] ? $tech['avg_turnaround_hours'].' hrs' : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No technicians yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-800 flex justify-between items-center">
                    <span>Vehicles Due for Service</span>
                    @hasrole('admin')
                        <form method="POST" action="{{ route('admin.service-reminders.run') }}">
                            @csrf
                            <button class="text-xs px-3 py-1.5 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700">Send Reminders Now</button>
                        </form>
                    @endhasrole
                </div>
                <ul class="divide-y">
                    @forelse ($vehiclesDueService as $vehicle)
                        <li class="px-4 py-3 flex justify-between items-center">
                            <div>
                                <a href="{{ route('staff.vehicles.show', $vehicle) }}" class="font-medium text-red-600 hover:underline">{{ $vehicle->registration_number }}</a>
                                <div class="text-xs text-gray-500">{{ $vehicle->customer->name }} · {{ $vehicle->customer->phone }}</div>
                            </div>
                            <div class="text-xs text-right text-gray-500">
                                @if ($vehicle->next_service_due_date)
                                    <div>Due: {{ $vehicle->next_service_due_date->format('d M Y') }}</div>
                                @endif
                                @if ($vehicle->next_service_due_mileage)
                                    <div>At: {{ number_format($vehicle->next_service_due_mileage) }} km</div>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-sm text-gray-500">No vehicles due for service soon.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
