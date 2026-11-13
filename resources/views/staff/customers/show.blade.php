<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $customer->name }}
                    @unless ($customer->is_active)
                        <span class="ml-2 text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 align-middle">Inactive</span>
                    @endunless
                </h2>
                <p class="text-sm text-gray-500">{{ $customer->phone }} · {{ $customer->email }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('staff.customers.edit', $customer) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">
                    Edit
                </a>
                <a href="{{ route('staff.vehicles.create', ['customer' => $customer->id]) }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-gray-800">
                    + Register Vehicle
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-[280px_1fr] gap-6 items-start">
            <div class="h-[40rem]">
                @include('staff.customers._sidebar', ['activeId' => $customer->id])
            </div>

            <div class="space-y-6">
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                        <div class="text-xs text-gray-500">Vehicles</div>
                        <div class="text-2xl font-bold">{{ $customer->vehicles->count() }}</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                        <div class="text-xs text-gray-500">Total Invoiced</div>
                        <div class="text-2xl font-bold">PKR {{ number_format($totalInvoiced) }}</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                        <div class="text-xs text-gray-500">Outstanding</div>
                        <div class="text-2xl font-bold {{ $totalOutstanding > 0 ? 'text-red-600' : '' }}">PKR {{ number_format($totalOutstanding) }}</div>
                    </div>
                </div>

                @php
                    $statusBadge = ['checked_in' => 'bg-gray-100 text-gray-700', 'inspecting' => 'bg-blue-100 text-blue-700', 'awaiting_approval' => 'bg-amber-100 text-amber-700', 'in_repair' => 'bg-orange-100 text-orange-700', 'ready_for_collection' => 'bg-green-100 text-green-700', 'completed' => 'bg-gray-100 text-gray-500'];
                    $actionBtnClass = [
                        'primary' => 'px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-semibold hover:bg-red-700',
                        'secondary' => 'px-3 py-1.5 border border-gray-300 text-gray-700 rounded-lg text-xs font-medium hover:bg-gray-50',
                        'waiting' => 'px-3 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-xs font-medium',
                        'muted' => 'px-3 py-1.5 text-xs text-gray-500 hover:text-gray-800 hover:underline',
                    ];
                @endphp

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-800 flex items-center justify-between">
                        <span>Vehicles &amp; Jobs</span>
                        <a href="{{ route('staff.vehicles.create', ['customer' => $customer->id]) }}" class="text-xs text-red-600 hover:underline font-medium">+ Register Vehicle</a>
                    </div>

                    @forelse ($customer->vehicles as $vehicle)
                        <div class="border-t border-gray-100 first:border-t-0">
                            <div class="px-4 py-3 bg-gray-50/60 flex items-center justify-between">
                                <div>
                                    <a href="{{ route('staff.vehicles.show', $vehicle) }}" class="font-medium text-red-600 hover:underline">{{ $vehicle->registration_number }}</a>
                                    <span class="text-xs text-gray-500">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }}) · {{ $vehicle->branch->name ?? '—' }}</span>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    @if (auth()->user()->hasAnyRole(['admin', 'manager', 'service_advisor']))
                                        <a href="{{ route('staff.invoices.quick-create', $vehicle) }}" class="text-xs text-gray-600 hover:underline font-medium">+ Quick Invoice</a>
                                    @endif
                                    <a href="{{ route('staff.inspections.create', $vehicle) }}" class="text-xs text-gray-600 hover:underline font-medium">+ New Inspection</a>
                                </div>
                            </div>

                            <ul class="divide-y divide-gray-50">
                                @forelse ($vehicle->inspections as $inspection)
                                    <li class="px-4 py-3 flex items-center justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="text-sm font-medium text-gray-900 truncate">{{ $inspection->reason }}</div>
                                            <div class="text-xs text-gray-500 flex items-center gap-2 mt-0.5">
                                                <span class="px-1.5 py-0.5 rounded-full {{ $statusBadge[$inspection->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $inspection->statusLabel() }}</span>
                                                <span>{{ $inspection->created_at->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                        @if (! empty($inspection->next_action['manager_only']) && ! auth()->user()->hasAnyRole(['admin', 'manager', 'service_advisor']))
                                            <span class="shrink-0 px-3 py-1.5 text-xs text-gray-400">Awaiting manager</span>
                                        @else
                                            <a href="{{ $inspection->next_action['url'] }}" class="shrink-0 {{ $actionBtnClass[$inspection->next_action['style']] }}">
                                                {{ $inspection->next_action['label'] }}
                                            </a>
                                        @endif
                                    </li>
                                @empty
                                    <li class="px-4 py-3 flex items-center justify-between gap-3">
                                        <span class="text-sm text-gray-500">No inspection started yet.</span>
                                        <a href="{{ route('staff.inspections.create', $vehicle) }}" class="shrink-0 px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-medium hover:bg-gray-800">Start Inspection</a>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    @empty
                        <p class="px-4 py-6 text-sm text-gray-500">No vehicles registered yet — register one above to start their first inspection.</p>
                    @endforelse
                </div>

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-800">Complete History (All Vehicles)</div>
                    <ul class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                        @forelse ($timeline as $event)
                            <li class="px-4 py-2 flex items-start gap-3 text-sm">
                                <span class="text-xs font-semibold text-gray-400 w-20 shrink-0">{{ $event['vehicle'] }}</span>
                                <div class="flex-1">
                                    @if ($event['url'])
                                        <a href="{{ $event['url'] }}" class="hover:underline">{{ $event['label'] }}</a>
                                    @else
                                        {{ $event['label'] }}
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400 shrink-0">{{ $event['at']->format('d M Y, h:i A') }}</span>
                            </li>
                        @empty
                            <li class="px-4 py-6 text-sm text-gray-500">No history yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
