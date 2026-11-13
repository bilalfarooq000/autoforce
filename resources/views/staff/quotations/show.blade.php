<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Quotation — {{ $inspection->vehicle->registration_number }}</h2>
    </x-slot>

    @php $q = $inspection->quotation; @endphp

    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="text-xs text-gray-500">Vehicle</div>
                    <div class="font-semibold text-gray-900">{{ $inspection->vehicle->registration_number }} — {{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }}</div>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Customer</div>
                    <a href="{{ route('staff.customers.show', $inspection->vehicle->customer_id) }}" class="font-semibold text-red-600 hover:underline">{{ $inspection->vehicle->customer->name }}</a>
                </div>
                <div>
                    <div class="text-xs text-gray-500">Job</div>
                    <a href="{{ route('staff.inspections.show', $inspection) }}" class="font-semibold text-gray-900 hover:underline">{{ $inspection->reason }}</a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-800">Quotation &amp; Customer Approval</div>

            <div class="p-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs px-2 py-1 rounded-full
                        {{ $q->status === 'approved' ? 'bg-green-100 text-green-700' : ($q->status === 'declined' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">
                        {{ str($q->status)->headline() }}
                    </span>
                    <span class="font-semibold">Total: PKR {{ number_format($q->total_amount) }}</span>
                </div>
                <ul class="text-sm divide-y border rounded-md">
                    @foreach ($q->items as $qi)
                        <li class="px-3 py-2 flex justify-between"><span>{{ $qi->description }}</span><span>PKR {{ number_format($qi->amount) }}</span></li>
                    @endforeach
                </ul>

                @if ($q->decided_at)
                    <p class="text-xs text-gray-500 mt-2">
                        Decision: <strong>{{ str($q->status)->headline() }}</strong> on {{ $q->decided_at->format('d M Y, h:i A') }} —
                        {{ $q->decision_channel === 'staff_recorded' ? 'recorded by staff ('.($q->decidedByStaff->name ?? '—').')' : 'via secure customer link' }}.
                    </p>
                @elseif (auth()->user()->hasAnyRole(['admin', 'manager', 'service_advisor']))
                    <div class="mt-3 bg-amber-50 border border-amber-200 rounded-md p-3">
                        <p class="text-xs text-amber-800 mb-2">Still pending on the secure link. If the customer approved/declined in person or by phone, record it here so the job doesn't sit stuck:</p>
                        <form method="POST" action="{{ route('staff.quotations.decision', [$inspection, $q]) }}" class="flex gap-2">
                            @csrf
                            <button name="decision" value="approved" class="px-3 py-1.5 bg-green-600 text-white rounded-md text-xs">Record: Approved</button>
                            <button name="decision" value="declined" class="px-3 py-1.5 bg-red-600 text-white rounded-md text-xs">Record: Declined</button>
                        </form>
                    </div>
                @else
                    <p class="text-xs text-gray-500 mt-2">Awaiting the customer's decision on the secure link.</p>
                @endif

                @if ($q->status === 'approved' && ! $inspection->invoice)
                    <a href="{{ route('staff.invoices.create', $inspection) }}" class="inline-flex mt-3 px-4 py-2 bg-gray-900 text-white rounded-lg text-sm hover:bg-gray-800">
                        Create Invoice
                    </a>
                @elseif ($inspection->invoice)
                    <a href="{{ route('staff.invoices.show', $inspection->invoice) }}" class="inline-flex mt-3 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm hover:bg-gray-200">
                        View Invoice {{ $inspection->invoice->invoice_number }}
                    </a>
                @endif
            </div>

            @if ($q->status === 'approved')
                @php $doneCount = $q->items->where('status', 'done')->count(); @endphp
                <div class="border-t border-gray-200">
                    <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-800 flex justify-between items-center bg-gray-50">
                        <span>Repair Work Tracking</span>
                        <span class="text-xs text-gray-500">{{ $doneCount }} / {{ $q->items->count() }} done</span>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($q->items as $qi)
                            <li class="px-4 py-3 flex items-center justify-between gap-3">
                                <div>
                                    <div class="font-medium text-sm">{{ $qi->description }}</div>
                                    <div class="text-xs text-gray-500">
                                        PKR {{ number_format($qi->amount) }}
                                        @if ($qi->assignee) · {{ $qi->assignee->name }} @endif
                                        @if ($qi->completed_at) · completed {{ $qi->completed_at->diffForHumans() }} @endif
                                    </div>
                                </div>
                                <div class="flex gap-1 shrink-0">
                                    @foreach (\App\Models\QuotationItem::STATUSES as $value => $label)
                                        <form method="POST" action="{{ route('staff.quotations.items.status', [$inspection, $qi]) }}">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $value }}">
                                            <button class="text-xs px-2 py-1 rounded-md border {{ $qi->status === $value ? 'bg-gray-800 text-white border-gray-800' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}">{{ $label }}</button>
                                        </form>
                                    @endforeach
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
