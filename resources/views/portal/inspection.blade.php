<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AutoForce — Inspection Report</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="max-w-xl mx-auto px-4 py-6 space-y-6">
        <div class="text-center">
            <h1 class="text-xl font-bold text-gray-800">AutoForce Pakistan</h1>
            <p class="text-sm text-gray-500">Digital Inspection Report</p>
        </div>

        @if (session('status'))
            <div class="rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-4">
            <div class="font-semibold text-lg">{{ $inspection->vehicle->registration_number }}</div>
            <div class="text-sm text-gray-500">{{ $inspection->vehicle->make }} {{ $inspection->vehicle->model }} ({{ $inspection->vehicle->year }})</div>
            <div class="text-sm text-gray-500 mt-1">Reason: {{ $inspection->reason }}</div>
            <div class="text-sm text-gray-500">Mileage: {{ number_format($inspection->mileage_at_visit) }} km</div>
            <div class="text-sm text-gray-500">Technician: {{ $inspection->technician->name ?? '—' }}</div>
            <span class="inline-block mt-2 text-xs px-2 py-1 rounded-full {{ in_array($inspection->status, ['ready_for_collection', 'completed']) ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                {{ $inspection->statusLabel() }}
            </span>
            @if ($inspection->summary)
                <p class="text-sm text-gray-700 mt-3 border-t pt-3">{{ $inspection->summary }}</p>
            @endif
        </div>

        @if ($inspection->diagramMarks->count())
            <div class="bg-white rounded-lg shadow p-4">
                <div class="font-semibold text-gray-700 mb-3">Vehicle Diagram</div>
                <x-vehicle-diagram :marks="$inspection->diagramMarks" :editable="false" />
            </div>
        @endif

        <div class="space-y-3">
            <h2 class="font-semibold text-gray-700 px-1">Findings</h2>
            @forelse ($inspection->items->where('is_checked', true) as $item)
                @php
                    $urgencyColor = ['critical' => 'border-red-300 bg-red-50', 'advisory' => 'border-amber-300 bg-amber-50', 'satisfactory' => 'border-green-300 bg-green-50'];
                    $uLabel = ['critical' => 'Faulty', 'advisory' => 'Replaceable', 'satisfactory' => 'OK'];
                @endphp
                <div class="bg-white rounded-lg shadow p-4 border-l-4 {{ $urgencyColor[$item->urgency] }}">
                    <div class="flex justify-between items-start">
                        <div class="font-medium">{{ $item->title }}</div>
                        <span class="text-xs uppercase font-semibold text-gray-500">{{ $uLabel[$item->urgency] ?? $item->urgency }}</span>
                    </div>
                    @if ($item->technician_comment)
                        <p class="text-sm text-gray-600 mt-1">{{ $item->technician_comment }}</p>
                    @endif
                    @foreach (['before' => 'Before', 'after' => 'After'] as $stage => $stageLabel)
                        @php $media = $item->media->where('stage', $stage); @endphp
                        @if ($media->count())
                            <div class="mt-3">
                                <div class="text-xs text-gray-400 uppercase mb-1">{{ $stageLabel }}</div>
                                <div class="flex gap-2 flex-wrap">
                                    @foreach ($media as $m)
                                        @if ($m->type === 'photo')
                                            <a href="{{ $m->url() }}" target="_blank"><img src="{{ $m->url() }}" class="w-20 h-20 object-cover rounded-md border" /></a>
                                        @else
                                            <video src="{{ $m->url() }}" controls class="w-20 h-20 object-cover rounded-md border"></video>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                    @if ($item->estimated_cost)
                        <div class="text-sm font-medium mt-2">Estimated cost: PKR {{ number_format($item->estimated_cost) }}</div>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500 px-1">No findings recorded yet. Check back shortly.</p>
            @endforelse
        </div>

        @if ($inspection->quotation)
            @php $q = $inspection->quotation; @endphp
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="font-semibold text-gray-700 mb-2">Repair Quotation</h2>
                <ul class="text-sm divide-y border rounded-md mb-3">
                    @foreach ($q->items as $qi)
                        <li class="px-3 py-2 flex justify-between"><span>{{ $qi->description }}</span><span>PKR {{ number_format($qi->amount) }}</span></li>
                    @endforeach
                </ul>
                <div class="flex justify-between font-semibold mb-3">
                    <span>Total</span><span>PKR {{ number_format($q->total_amount) }}</span>
                </div>

                @if ($q->status === 'pending')
                    <form method="POST" action="{{ route('portal.quotation.decide', [$inspection->access_token, $q->id]) }}" class="space-y-2">
                        @csrf
                        <textarea name="customer_notes" rows="2" class="w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="Notes (optional)"></textarea>
                        <div class="flex gap-2">
                            <button name="decision" value="approved" class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md text-sm">Approve Repair</button>
                            <button name="decision" value="declined" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-md text-sm">Decline</button>
                        </div>
                    </form>
                @else
                    <div class="text-sm px-3 py-2 rounded-md {{ $q->status === 'approved' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                        You {{ $q->status }} this quotation on {{ optional($q->decided_at)->format('d M Y, h:i A') }}.
                    </div>
                @endif
            </div>
        @endif

        @if ($inspection->comments->count())
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="font-semibold text-gray-700 mb-2">Progress Updates</h2>
                <ul class="space-y-2">
                    @foreach ($inspection->comments as $comment)
                        <li class="border rounded-md p-2 text-sm">
                            <p class="text-gray-800">{{ $comment->body }}</p>
                            <div class="text-xs text-gray-400 mt-1">{{ $comment->created_at->format('d M Y, h:i A') }}</div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($inspection->invoice)
            <div class="bg-white rounded-lg shadow p-4 flex justify-between items-center">
                <div>
                    <div class="font-medium">Invoice {{ $inspection->invoice->invoice_number }}</div>
                    <div class="text-sm text-gray-500">PKR {{ number_format($inspection->invoice->total) }}</div>
                </div>
                <span class="text-xs px-2 py-1 rounded-full {{ $inspection->invoice->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                    {{ str($inspection->invoice->status)->headline() }}
                </span>
            </div>
        @endif

        <p class="text-center text-xs text-gray-400 pt-4">This is a secure, unique link for your vehicle. Please do not share it.</p>
    </div>
</body>
</html>
