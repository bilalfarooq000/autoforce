<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $vehicle->registration_number }}</h2>
                <p class="text-sm text-gray-500">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }}) · {{ $vehicle->customer->name }} · {{ $vehicle->customer->phone }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('staff.vehicles.edit', $vehicle) }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md text-sm hover:bg-gray-50">Edit</a>
                <a href="{{ route('staff.inspections.create', $vehicle) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">
                    + New Inspection
                </a>
            </div>
        </div>
    </x-slot>

    <div x-data="{ tab: 'overview' }" class="max-w-5xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Vehicle profile card — always visible -->
        <div class="bg-white rounded-lg shadow p-4 flex gap-4">
            @if ($vehicle->photo_path)
                <img src="{{ asset('storage/'.$vehicle->photo_path) }}" class="w-28 h-28 object-cover rounded-md border" />
            @endif
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm flex-1">
                <div><div class="text-gray-500">Type / Fuel</div><div class="font-medium">{{ ucfirst($vehicle->vehicle_type) }} · {{ ucfirst($vehicle->fuel_type) }}</div></div>
                <div><div class="text-gray-500">Transmission</div><div class="font-medium">{{ ucfirst($vehicle->transmission) }}</div></div>
                <div><div class="text-gray-500">Chassis No.</div><div class="font-medium">{{ $vehicle->chassis_number ?: '—' }}</div></div>
                <div><div class="text-gray-500">Engine No.</div><div class="font-medium">{{ $vehicle->engine_number ?: '—' }}</div></div>
                <div><div class="text-gray-500">Color</div><div class="font-medium">{{ $vehicle->color ?: '—' }}</div></div>
                <div><div class="text-gray-500">Mileage</div><div class="font-medium">{{ number_format($vehicle->mileage) }} km</div></div>
                <div><div class="text-gray-500">Branch</div><div class="font-medium">{{ $vehicle->branch->name ?? '—' }}</div></div>
                <div><div class="text-gray-500">Registered Since</div><div class="font-medium">{{ $vehicle->created_at->format('d M Y') }}</div></div>
                <div>
                    <div class="text-gray-500">Next Service Due</div>
                    <div class="font-medium {{ $vehicle->isServiceDueSoon() ? 'text-amber-600' : '' }}">
                        {{ $vehicle->next_service_due_date?->format('d M Y') ?? '—' }}
                        @if ($vehicle->next_service_due_mileage) / {{ number_format($vehicle->next_service_due_mileage) }} km @endif
                    </div>
                </div>
                <div>
                    <div class="text-gray-500">Insurance Expiry</div>
                    <div class="font-medium {{ $vehicle->insuranceExpiringSoon() ? 'text-red-600' : '' }}">{{ $vehicle->insurance_expiry?->format('d M Y') ?? '—' }}</div>
                </div>
            </div>
            <div class="shrink-0 text-center">
                <img src="{{ route('staff.vehicles.qr', $vehicle) }}" class="w-20 h-20" />
                <div class="text-xs text-gray-400 mt-1">Scan for record</div>
            </div>
        </div>

        @if ($vehicle->notes)
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800">{{ $vehicle->notes }}</div>
        @endif

        <!-- Tabs -->
        <div class="border-b border-gray-200">
            <nav class="flex gap-6 -mb-px">
                @foreach ([
                    'overview' => 'Overview / Timeline',
                    'inspections' => 'Inspection Sheets ('.$vehicle->inspections->count().')',
                    'invoices' => 'Invoices ('.$vehicle->invoices->count().')',
                    'activity' => 'Communication & Audit',
                ] as $key => $label)
                    <button @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="whitespace-nowrap py-3 border-b-2 text-sm font-medium">
                        {{ $label }}
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Overview / Timeline -->
        <div x-show="tab === 'overview'" class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-semibold text-gray-700">Full History — Single-Click Timeline</div>
            <ul class="divide-y max-h-[32rem] overflow-y-auto">
                @php
                    $timelineColor = [
                        'vehicle' => 'bg-gray-400', 'checkin' => 'bg-blue-400', 'progress' => 'bg-red-400',
                        'quotation' => 'bg-amber-400', 'approved' => 'bg-green-500', 'declined' => 'bg-red-400',
                        'ready' => 'bg-teal-500', 'collected' => 'bg-gray-700', 'invoice' => 'bg-purple-400', 'payment' => 'bg-green-400',
                    ];
                @endphp
                @foreach ($vehicle->timeline() as $event)
                    <li class="px-4 py-2 flex items-start gap-3 text-sm">
                        <span class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $timelineColor[$event['type']] ?? 'bg-gray-400' }}"></span>
                        <div class="flex-1">
                            @if ($event['url'])
                                <a href="{{ $event['url'] }}" class="hover:underline">{{ $event['label'] }}</a>
                            @else
                                {{ $event['label'] }}
                            @endif
                        </div>
                        <span class="text-xs text-gray-400 shrink-0">{{ $event['at']->format('d M Y, h:i A') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Inspection Sheets -->
        <div x-show="tab === 'inspections'" x-cloak class="space-y-3">
            @forelse ($vehicle->inspections as $inspection)
                <div x-data="{ open: false }" class="bg-white rounded-lg shadow">
                    <button @click="open = ! open" class="w-full px-4 py-3 flex justify-between items-center text-left">
                        <div>
                            <div class="font-medium text-gray-800">{{ $inspection->reason }}</div>
                            <div class="text-xs text-gray-500">{{ $inspection->created_at->format('d M Y') }} · {{ $inspection->technician->name ?? 'Unassigned' }} · {{ $inspection->items->where('is_checked', true)->count() }}/{{ $inspection->items->count() }} checklist items checked</div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">{{ $inspection->statusLabel() }}</span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>
                    <div x-show="open" x-cloak class="border-t px-4 py-4 space-y-4">
                        @php
                            $uColor = ['critical' => 'text-red-600', 'advisory' => 'text-amber-600', 'satisfactory' => 'text-green-600'];
                            $uBadge = ['critical' => 'bg-red-100 text-red-700', 'advisory' => 'bg-amber-100 text-amber-700', 'satisfactory' => 'bg-green-100 text-green-700'];
                            $uLabel = ['critical' => 'Faulty', 'advisory' => 'Replaceable', 'satisfactory' => 'OK'];
                            $checked = $inspection->items->where('is_checked', true)->whereNotNull('category')->groupBy('category');
                            $adHoc = $inspection->items->whereNull('category');
                        @endphp

                        <!-- Job meta -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs bg-gray-50 rounded-md p-3">
                            <div><div class="text-gray-500">Technician</div><div class="font-medium text-gray-800">{{ $inspection->technician->name ?? 'Unassigned' }}</div></div>
                            <div><div class="text-gray-500">Checked in by</div><div class="font-medium text-gray-800">{{ $inspection->creator->name ?? '—' }}</div></div>
                            <div><div class="text-gray-500">Mileage at visit</div><div class="font-medium text-gray-800">{{ number_format($inspection->mileage_at_visit) }} km</div></div>
                            <div><div class="text-gray-500">Checked in</div><div class="font-medium text-gray-800">{{ $inspection->created_at->format('d M Y, h:i A') }}</div></div>
                        </div>

                        @if ($inspection->summary)
                            <div class="text-sm">
                                <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Inspection Summary</div>
                                <p class="text-gray-700">{{ $inspection->summary }}</p>
                            </div>
                        @endif

                        <!-- Vehicle diagram (read-only) -->
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Vehicle Diagram — Damage / Finding Locations</div>
                            <x-vehicle-diagram :marks="$inspection->diagramMarks" :editable="false" />
                        </div>

                        <!-- Checklist with technician remarks + media -->
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Checklist Findings</div>
                            @forelse ($checked as $category => $catItems)
                                <div class="mb-3">
                                    <div class="text-xs font-semibold text-gray-400 mb-1">{{ $category }}</div>
                                    <ul class="text-sm divide-y border rounded-md">
                                        @foreach ($catItems as $item)
                                            <li class="px-3 py-2">
                                                <div class="flex justify-between items-start gap-3">
                                                    <span class="font-medium">{{ $item->title }}</span>
                                                    <span class="text-xs px-2 py-0.5 rounded-full shrink-0 {{ $uBadge[$item->urgency] ?? '' }}">{{ $uLabel[$item->urgency] ?? str($item->urgency)->headline() }}</span>
                                                </div>
                                                @if ($item->technician_comment)
                                                    <p class="text-xs text-gray-600 mt-1">"{{ $item->technician_comment }}"</p>
                                                @endif
                                                @if ($item->media->count())
                                                    <div class="flex gap-2 mt-2 flex-wrap">
                                                        @foreach ($item->media as $m)
                                                            @if ($m->type === 'photo')
                                                                <img src="{{ $m->url() }}" class="w-16 h-16 object-cover rounded-md border" />
                                                            @else
                                                                <video src="{{ $m->url() }}" class="w-16 h-16 object-cover rounded-md border" muted></video>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No checklist items checked yet.</p>
                            @endforelse

                            @if ($adHoc->count())
                                <div class="mt-3">
                                    <div class="text-xs font-semibold text-gray-400 mb-1">Additional Findings</div>
                                    <ul class="text-sm divide-y border rounded-md">
                                        @foreach ($adHoc as $item)
                                            <li class="px-3 py-2">
                                                <div class="flex justify-between items-start gap-3">
                                                    <span class="font-medium">{{ $item->title }}</span>
                                                    <span class="text-xs px-2 py-0.5 rounded-full shrink-0 {{ $uBadge[$item->urgency] ?? '' }}">{{ $uLabel[$item->urgency] ?? str($item->urgency)->headline() }}</span>
                                                </div>
                                                @if ($item->technician_comment)
                                                    <p class="text-xs text-gray-600 mt-1">"{{ $item->technician_comment }}"</p>
                                                @endif
                                                @if ($item->media->count())
                                                    <div class="flex gap-2 mt-2 flex-wrap">
                                                        @foreach ($item->media as $m)
                                                            @if ($m->type === 'photo')
                                                                <img src="{{ $m->url() }}" class="w-16 h-16 object-cover rounded-md border" />
                                                            @else
                                                                <video src="{{ $m->url() }}" class="w-16 h-16 object-cover rounded-md border" muted></video>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <!-- Quotation -->
                        @if ($inspection->quotation)
                            @php $q = $inspection->quotation; @endphp
                            <div>
                                <div class="text-xs font-semibold text-gray-500 uppercase mb-1 flex justify-between">
                                    <span>Quotation</span>
                                    <span class="px-2 py-0.5 rounded-full {{ $q->status === 'approved' ? 'bg-green-100 text-green-700' : ($q->status === 'declined' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ str($q->status)->headline() }}</span>
                                </div>
                                <ul class="text-sm divide-y border rounded-md">
                                    @foreach ($q->items as $qi)
                                        <li class="px-3 py-1.5 flex justify-between"><span>{{ $qi->description }}</span><span>PKR {{ number_format($qi->amount) }}</span></li>
                                    @endforeach
                                </ul>
                                <div class="text-right text-sm font-semibold mt-1">Total: PKR {{ number_format($q->total_amount) }}</div>
                            </div>
                        @endif

                        <!-- Invoice -->
                        @if ($inspection->invoice)
                            <div class="flex justify-between items-center bg-gray-50 rounded-md p-3 text-sm">
                                <div>
                                    <div class="font-medium">Invoice {{ $inspection->invoice->invoice_number }}</div>
                                    <div class="text-xs text-gray-500">PKR {{ number_format($inspection->invoice->total) }} · {{ str($inspection->invoice->status)->headline() }}</div>
                                </div>
                                <a href="{{ route('staff.invoices.show', $inspection->invoice) }}" class="text-red-600 hover:underline text-xs">View Invoice</a>
                            </div>
                        @endif

                        <a href="{{ route('staff.inspections.show', $inspection) }}" class="inline-block text-sm text-red-600 hover:underline">Open full inspection sheet (edit / diagram) →</a>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-6 text-sm text-gray-500">No inspections yet.</div>
            @endforelse
        </div>

        <!-- Invoices -->
        <div x-show="tab === 'invoices'" x-cloak class="bg-white rounded-lg shadow">
            <ul class="divide-y">
                @forelse ($vehicle->invoices as $invoice)
                    <li class="px-4 py-3 flex justify-between items-center">
                        <div>
                            <a href="{{ route('staff.invoices.show', $invoice) }}" class="font-medium text-red-600 hover:underline">
                                {{ $invoice->invoice_number }}
                            </a>
                            <div class="text-xs text-gray-500">{{ $invoice->issued_at->format('d M Y') }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-medium">PKR {{ number_format($invoice->total, 0) }}</div>
                            <span class="text-xs px-2 py-1 rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                {{ str($invoice->status)->headline() }}
                            </span>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-gray-500">No invoices yet.</li>
                @endforelse
            </ul>
        </div>

        <!-- Communication & Audit -->
        <div x-show="tab === 'activity'" x-cloak class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b font-semibold text-gray-700">Communication Log</div>
                <ul class="divide-y text-sm">
                    @forelse ($vehicle->notifications as $n)
                        <li class="px-4 py-2">
                            <div class="flex justify-between">
                                <span class="font-medium">{{ str($n->type)->headline() }}</span>
                                <span class="text-xs text-gray-400">{{ $n->channel }} · {{ $n->status }}</span>
                            </div>
                            <div class="text-xs text-gray-500">{{ $n->created_at->diffForHumans() }}</div>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-sm text-gray-500">No notifications sent yet.</li>
                    @endforelse
                </ul>
            </div>

            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b font-semibold text-gray-700">Audit Trail</div>
                <ul class="divide-y text-sm">
                    @forelse ($activities as $activity)
                        <li class="px-4 py-2">
                            <div>{{ $activity->description }}</div>
                            <div class="text-xs text-gray-500">{{ $activity->causer?->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}</div>
                        </li>
                    @empty
                        <li class="px-4 py-6 text-sm text-gray-500">No activity recorded yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
