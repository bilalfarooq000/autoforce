<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $vehicle->registration_number }}</h2>
            <p class="text-sm text-gray-500">{{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }}) · {{ number_format($vehicle->mileage) }} km</p>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white rounded-lg shadow p-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div><div class="text-gray-500">Vehicle Type</div><div class="font-medium">{{ ucfirst($vehicle->vehicle_type) }} · {{ ucfirst($vehicle->fuel_type) }}</div></div>
            <div><div class="text-gray-500">Chassis No.</div><div class="font-medium">{{ $vehicle->chassis_number ?: '—' }}</div></div>
            <div>
                <div class="text-gray-500">Next Service Due</div>
                <div class="font-medium {{ $vehicle->isServiceDueSoon() ? 'text-amber-600' : '' }}">
                    {{ $vehicle->next_service_due_date?->format('d M Y') ?? '—' }}
                </div>
            </div>
            <div>
                <div class="text-gray-500">Insurance Expiry</div>
                <div class="font-medium {{ $vehicle->insuranceExpiringSoon() ? 'text-red-600' : '' }}">{{ $vehicle->insurance_expiry?->format('d M Y') ?? '—' }}</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-semibold text-gray-700">Complete Vehicle History</div>
            <ul class="divide-y max-h-80 overflow-y-auto">
                @foreach ($vehicle->timeline(forCustomer: true) as $event)
                    <li class="px-4 py-2 flex items-start gap-3 text-sm">
                        <span class="w-2 h-2 rounded-full mt-1.5 shrink-0 bg-red-400"></span>
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

        <div class="space-y-3">
            <div class="font-semibold text-gray-700 px-1">Service & Inspection History</div>
            @php
                $uColor = ['critical' => 'text-red-600', 'advisory' => 'text-amber-600', 'satisfactory' => 'text-green-600'];
                $uBadge = ['critical' => 'bg-red-100 text-red-700', 'advisory' => 'bg-amber-100 text-amber-700', 'satisfactory' => 'bg-green-100 text-green-700'];
                $uLabel = ['critical' => 'Faulty', 'advisory' => 'Replaceable', 'satisfactory' => 'OK'];
            @endphp
            @forelse ($vehicle->inspections as $inspection)
                <div x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }" class="bg-white rounded-lg shadow">
                    <button @click="open = ! open" class="w-full px-4 py-4 flex justify-between items-center text-left">
                        <div>
                            <div class="font-medium">{{ $inspection->reason }}</div>
                            <div class="text-xs text-gray-500">{{ $inspection->created_at->format('d M Y') }} · {{ number_format($inspection->mileage_at_visit) }} km · {{ $inspection->technician->name ?? 'Technician unassigned' }}</div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs px-2 py-1 rounded-full {{ in_array($inspection->status, ['ready_for_collection', 'completed']) ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $inspection->statusLabel() }}
                            </span>
                            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    <div x-show="open" x-cloak class="border-t px-4 py-4 space-y-4">
                        <!-- Job meta -->
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-xs bg-gray-50 rounded-md p-3">
                            <div><div class="text-gray-500">Technician</div><div class="font-medium text-gray-800">{{ $inspection->technician->name ?? 'Unassigned' }}</div></div>
                            <div><div class="text-gray-500">Mileage at visit</div><div class="font-medium text-gray-800">{{ number_format($inspection->mileage_at_visit) }} km</div></div>
                            <div><div class="text-gray-500">Checked in</div><div class="font-medium text-gray-800">{{ $inspection->created_at->format('d M Y, h:i A') }}</div></div>
                        </div>

                        @if ($inspection->summary)
                            <div>
                                <div class="text-xs font-semibold text-gray-500 uppercase mb-1">Inspection Summary</div>
                                <p class="text-sm text-gray-700">{{ $inspection->summary }}</p>
                            </div>
                        @endif

                        @if ($inspection->diagramMarks->count())
                            <div>
                                <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Vehicle Diagram</div>
                                <x-vehicle-diagram :marks="$inspection->diagramMarks" :editable="false" />
                            </div>
                        @endif

                        <!-- Checklist grouped by category -->
                        @php
                            $checked = $inspection->items->where('is_checked', true)->whereNotNull('category')->groupBy('category');
                            $adHoc = $inspection->items->whereNull('category')->where('is_checked', true);
                        @endphp
                        <div>
                            <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Inspection Checklist</div>
                            @forelse ($checked as $category => $catItems)
                                <div class="mb-3">
                                    <div class="text-xs font-semibold text-gray-400 mb-1">{{ $category }}</div>
                                    <div class="space-y-2">
                                        @foreach ($catItems as $item)
                                            <div class="border rounded-md p-2">
                                                <div class="flex justify-between items-start gap-3">
                                                    <span class="font-medium text-sm">{{ $item->title }}</span>
                                                    <span class="text-xs px-2 py-0.5 rounded-full shrink-0 {{ $uBadge[$item->urgency] ?? '' }}">{{ $uLabel[$item->urgency] ?? str($item->urgency)->headline() }}</span>
                                                </div>
                                                @if ($item->technician_comment)
                                                    <p class="text-xs text-gray-600 mt-1">"{{ $item->technician_comment }}"</p>
                                                @endif
                                                @foreach (['before' => 'Before', 'after' => 'After'] as $stage => $stageLabel)
                                                    @php $media = $item->media->where('stage', $stage); @endphp
                                                    @if ($media->count())
                                                        <div class="mt-2">
                                                            <div class="text-xs text-gray-400 uppercase mb-1">{{ $stageLabel }}</div>
                                                            <div class="flex gap-2 flex-wrap">
                                                                @foreach ($media as $m)
                                                                    @if ($m->type === 'photo')
                                                                        <a href="{{ $m->url() }}" target="_blank"><img src="{{ $m->url() }}" class="w-16 h-16 object-cover rounded-md border" /></a>
                                                                    @else
                                                                        <video src="{{ $m->url() }}" class="w-16 h-16 object-cover rounded-md border" muted controls></video>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <p class="text-sm text-gray-500">No checklist items checked yet — diagnosis in progress.</p>
                            @endforelse

                            @if ($adHoc->count())
                                <div class="mt-3">
                                    <div class="text-xs font-semibold text-gray-400 mb-1">Additional Findings</div>
                                    <div class="space-y-2">
                                        @foreach ($adHoc as $item)
                                            <div class="border rounded-md p-2">
                                                <div class="flex justify-between items-start gap-3">
                                                    <span class="font-medium text-sm">{{ $item->title }}</span>
                                                    <span class="text-xs px-2 py-0.5 rounded-full shrink-0 {{ $uBadge[$item->urgency] ?? '' }}">{{ $uLabel[$item->urgency] ?? str($item->urgency)->headline() }}</span>
                                                </div>
                                                @if ($item->technician_comment)
                                                    <p class="text-xs text-gray-600 mt-1">"{{ $item->technician_comment }}"</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($inspection->quotation)
                            @php $q = $inspection->quotation; @endphp
                            <div class="text-sm border rounded-md p-3">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-semibold">Quotation</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full {{ $q->status === 'approved' ? 'bg-green-100 text-green-700' : ($q->status === 'declined' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ str($q->status)->headline() }}</span>
                                </div>
                                @foreach ($q->items as $qi)
                                    <div class="flex justify-between text-xs py-0.5"><span>{{ $qi->description }}</span><span>PKR {{ number_format($qi->amount) }}</span></div>
                                @endforeach
                                <div class="flex justify-between font-semibold text-sm mt-1 border-t pt-1"><span>Total</span><span>PKR {{ number_format($q->total_amount) }}</span></div>
                            </div>
                        @endif

                        @if ($inspection->comments->count())
                            <div>
                                <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Progress Updates</div>
                                <ul class="space-y-2">
                                    @foreach ($inspection->comments as $comment)
                                        <li class="border rounded-md p-2 text-sm">
                                            <p class="text-gray-800">{{ $comment->body }}</p>
                                            <div class="text-xs text-gray-400 mt-1">{{ $comment->author->name }} · {{ $comment->created_at->format('d M Y, h:i A') }}</div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if ($inspection->invoice)
                            <div class="flex justify-between items-center bg-gray-50 rounded-md p-3 text-sm">
                                <div>
                                    <div class="font-medium">Invoice {{ $inspection->invoice->invoice_number }}</div>
                                    <div class="text-xs text-gray-500">PKR {{ number_format($inspection->invoice->total) }} · {{ str($inspection->invoice->status)->headline() }}</div>
                                </div>
                                <a href="{{ route('customer.invoices.pdf', $inspection->invoice) }}" class="text-red-600 hover:underline text-xs">Download PDF</a>
                            </div>
                        @endif

                        <a href="{{ route('portal.inspection', $inspection->access_token) }}" class="inline-block text-xs text-red-600 hover:underline">Open original secure report link →</a>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-6 text-sm text-gray-500">No inspections recorded yet.</div>
            @endforelse
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-semibold text-gray-700">Invoices, Parts Replaced & Payment Receipts</div>
            <div class="divide-y">
                @forelse ($vehicle->invoices as $invoice)
                    <div class="px-4 py-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-medium">{{ $invoice->invoice_number }}</div>
                                <div class="text-xs text-gray-500">Issued {{ $invoice->issued_at->format('d M Y') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">PKR {{ number_format($invoice->total) }}</div>
                                @if ($invoice->balanceDue() > 0)
                                    <div class="text-xs text-red-600">Balance due: PKR {{ number_format($invoice->balanceDue()) }}</div>
                                @endif
                                <span class="text-xs px-2 py-1 rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                    {{ str($invoice->status)->headline() }}
                                </span>
                            </div>
                        </div>

                        <!-- Parts replaced / work undertaken -->
                        <table class="w-full text-xs mt-3 border rounded-md">
                            <thead class="bg-gray-50 text-gray-500">
                                <tr><th class="px-2 py-1 text-left">Description</th><th class="px-2 py-1 text-left">Type</th><th class="px-2 py-1 text-right">Amount</th><th class="px-2 py-1 text-right">Warranty</th></tr>
                            </thead>
                            <tbody class="divide-y">
                                @foreach ($invoice->items as $item)
                                    @php
                                        $warrantyActive = $item->warranty_months && now()->lessThan($invoice->issued_at->copy()->addMonths($item->warranty_months));
                                    @endphp
                                    <tr>
                                        <td class="px-2 py-1">{{ $item->description }}</td>
                                        <td class="px-2 py-1">{{ str($item->type)->headline() }}</td>
                                        <td class="px-2 py-1 text-right">PKR {{ number_format($item->amount) }}</td>
                                        <td class="px-2 py-1 text-right">
                                            @if ($item->warranty_months)
                                                <span class="{{ $warrantyActive ? 'text-green-600' : 'text-gray-400' }}">
                                                    {{ $item->warranty_months }} mo {{ $warrantyActive ? '(active)' : '(expired)' }}
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Payment receipts -->
                        @if ($invoice->payments->count())
                            <div class="mt-2 text-xs text-gray-600">
                                <div class="font-semibold text-gray-500 uppercase mb-1">Payment Receipts</div>
                                @foreach ($invoice->payments as $payment)
                                    <div class="flex justify-between py-0.5">
                                        <span>{{ $payment->paid_at->format('d M Y, h:i A') }} · {{ str($payment->method)->headline() }} {{ $payment->reference ? "(#{$payment->reference})" : '' }}</span>
                                        <span class="font-medium">PKR {{ number_format($payment->amount) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <a href="{{ route('customer.invoices.pdf', $invoice) }}" class="inline-block mt-2 text-xs text-red-600 hover:underline">Download Invoice PDF →</a>
                    </div>
                @empty
                    <div class="px-4 py-6 text-sm text-gray-500">No invoices yet.</div>
                @endforelse
            </div>
        </div>

        @php
            $repairPhotos = $vehicle->inspections->flatMap->items->flatMap->media->where('stage', 'after');
        @endphp
        @if ($repairPhotos->count())
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b font-semibold text-gray-700">Photos & Videos of Completed Repairs</div>
                <div class="p-4 flex flex-wrap gap-3">
                    @foreach ($repairPhotos as $media)
                        @if ($media->type === 'photo')
                            <a href="{{ $media->url() }}" target="_blank"><img src="{{ $media->url() }}" class="w-24 h-24 object-cover rounded-md border" /></a>
                        @else
                            <video src="{{ $media->url() }}" controls class="w-24 h-24 object-cover rounded-md border"></video>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
