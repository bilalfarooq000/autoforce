<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inventory — Reports</h2>
    </x-slot>

    @php
        $qs = fn (array $extra = []) => array_filter(array_merge(request()->query(), $extra), fn ($v) => $v !== null && $v !== '');
    @endphp

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <div class="flex gap-2">
            <a href="{{ route('staff.inventory.items.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-white border border-gray-200 text-gray-600 hover:bg-gray-50">Stock</a>
            <a href="{{ route('staff.inventory.movements.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-white border border-gray-200 text-gray-600 hover:bg-gray-50">Movements</a>
            <a href="{{ route('staff.inventory.reports.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-gray-900 text-white">Reports</a>
        </div>

        @if ($branches->isNotEmpty())
            <form method="GET" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-wrap gap-3 items-end">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div>
                    <label class="text-xs text-gray-500">Branch</label>
                    <select name="branch_id" onchange="this.form.submit()" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">All Branches (comparison)</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected((int) $branchId === $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
                @if ($branchId)
                    <a href="{{ route('staff.inventory.reports.index', $qs(['branch_id' => null])) }}" class="text-xs text-red-600 hover:underline pb-2">Clear branch filter</a>
                @endif
            </form>
        @endif

        <div class="flex gap-2 border-b border-gray-200">
            @foreach (['overview' => 'Overview', 'low_stock' => 'Low Stock', 'valuation' => 'Valuation', 'usage' => 'Usage'] as $key => $label)
                <a href="{{ route('staff.inventory.reports.index', $qs(['tab' => $key, 'item_id' => null])) }}"
                   class="px-3 py-2 text-sm font-medium border-b-2 {{ $tab === $key ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-gray-800' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if ($tab === 'overview')
            @if ($branchStats->count() > 1)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3">Branch</th>
                                <th class="px-4 py-3 text-right">Active Items</th>
                                <th class="px-4 py-3 text-right">Stock Value</th>
                                <th class="px-4 py-3 text-right">Low Stock</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach ($branchStats as $b)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium">{{ $b->name }}</td>
                                    <td class="px-4 py-3 text-right">{{ $b->items_count }}</td>
                                    <td class="px-4 py-3 text-right">PKR {{ number_format($b->stock_value, 2) }}</td>
                                    <td class="px-4 py-3 text-right {{ $b->low_stock_count ? 'text-red-600 font-medium' : '' }}">{{ $b->low_stock_count }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('staff.inventory.reports.index', ['tab' => 'overview', 'branch_id' => $b->id]) }}" class="text-xs text-red-600 hover:underline font-medium">View reports →</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @php $b = $branchStats->first(); @endphp
                    <x-stat-card label="Active Items" :value="$b->items_count ?? 0" icon="clipboard" accent="slate" />
                    <x-stat-card label="Stock Value (PKR)" :value="number_format($b->stock_value ?? 0, 0)" icon="cash" accent="green" />
                    <x-stat-card label="Low Stock Items" :value="$b->low_stock_count ?? 0" icon="wrench" accent="red" />
                </div>
            @endif
        @endif

        @if ($tab === 'low_stock')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3">Item</th>
                            <th class="px-4 py-3">Branch</th>
                            <th class="px-4 py-3 text-right">On Hand</th>
                            <th class="px-4 py-3 text-right">Reorder At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($lowStockItems as $item)
                            <tr>
                                <td class="px-4 py-3"><a href="{{ route('staff.inventory.items.show', $item) }}" class="text-red-600 hover:underline font-medium">{{ $item->name }}</a></td>
                                <td class="px-4 py-3 text-gray-600">{{ $item->branch->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-red-600">{{ $item->quantity_on_hand }} {{ $item->unit }}</td>
                                <td class="px-4 py-3 text-right text-gray-500">{{ $item->reorder_level }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Nothing is low on stock.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        @if ($tab === 'valuation')
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                @forelse ($valuationByCategory as $category => $data)
                    <div class="border-b border-gray-100 last:border-b-0">
                        <div class="px-4 py-2 bg-gray-50 flex justify-between text-xs font-semibold text-gray-600 uppercase">
                            <span>{{ $category }}</span>
                            <span>{{ $data['total_qty'] }} units · PKR {{ number_format($data['total_value'], 2) }}</span>
                        </div>
                        <table class="w-full text-sm">
                            <tbody class="divide-y">
                                @foreach ($data['items'] as $item)
                                    <tr>
                                        <td class="px-4 py-2"><a href="{{ route('staff.inventory.items.show', $item) }}" class="text-red-600 hover:underline">{{ $item->name }}</a></td>
                                        <td class="px-4 py-2 text-right text-gray-500">{{ $item->quantity_on_hand }} {{ $item->unit }} × PKR {{ number_format($item->cost_price, 2) }}</td>
                                        <td class="px-4 py-2 text-right font-medium w-32">PKR {{ number_format($item->stockValue(), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @empty
                    <p class="px-4 py-6 text-center text-gray-500">No active inventory items yet.</p>
                @endforelse
                <div class="px-4 py-3 bg-gray-900 text-white flex justify-between font-semibold">
                    <span>Total Inventory Value</span>
                    <span>PKR {{ number_format($grandTotalValue, 2) }}</span>
                </div>
            </div>
        @endif

        @if ($tab === 'usage')
            <form method="GET" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-wrap gap-3 items-end">
                <input type="hidden" name="tab" value="usage">
                @if ($branchId)<input type="hidden" name="branch_id" value="{{ $branchId }}">@endif
                <div>
                    <label class="text-xs text-gray-500">From</label>
                    <input type="date" name="from" value="{{ $usageFrom }}" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
                </div>
                <div>
                    <label class="text-xs text-gray-500">To</label>
                    <input type="date" name="to" value="{{ $usageTo }}" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
                </div>
                <div>
                    <label class="text-xs text-gray-500">Part</label>
                    <select name="item_id" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">All parts (summary)</option>
                        @foreach ($allItems as $ai)
                            <option value="{{ $ai->id }}" @selected((string) $usageItemId === (string) $ai->id)>{{ $ai->name }} @if($branches->isNotEmpty()) ({{ $ai->branch->name ?? '' }}) @endif</option>
                        @endforeach
                    </select>
                </div>
                <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm">Apply</button>
            </form>

            @if ($usageItemId && $usageDetail !== null)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-800 flex justify-between items-center">
                        <span>Issue History — {{ $usageDetail->first()->item->name ?? '' }}</span>
                        <a href="{{ route('staff.inventory.reports.index', $qs(['item_id' => null])) }}" class="text-xs text-red-600 hover:underline font-medium">← Back to all parts</a>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Branch</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3">Vehicle</th>
                                <th class="px-4 py-3">Customer</th>
                                <th class="px-4 py-3">Issued By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($usageDetail as $m)
                                <tr>
                                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $m->created_at->format('d M Y, h:i A') }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $m->branch->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">{{ $m->quantity }}</td>
                                    <td class="px-4 py-3">
                                        @if ($m->inspection)
                                            <a href="{{ route('staff.inspections.show', $m->inspection) }}" class="text-red-600 hover:underline">{{ $m->inspection->vehicle->registration_number ?? '—' }}</a>
                                        @else
                                            <span class="text-gray-400">Not tied to a job</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">{{ $m->inspection->vehicle->customer->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $m->performedBy->name ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No issues for this part in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                            <tr>
                                <th class="px-4 py-3">Item</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3 text-right">Used ({{ \Illuminate\Support\Carbon::parse($usageFrom)->format('d M') }} – {{ \Illuminate\Support\Carbon::parse($usageTo)->format('d M') }})</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            @forelse ($usage as $row)
                                <tr>
                                    <td class="px-4 py-3"><a href="{{ route('staff.inventory.items.show', $row->item) }}" class="text-red-600 hover:underline">{{ $row->item->name ?? '—' }}</a></td>
                                    <td class="px-4 py-3 text-gray-600">{{ $row->item->category->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">{{ $row->total_used }} {{ $row->item->unit ?? '' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ route('staff.inventory.reports.index', $qs(['item_id' => $row->item_id])) }}" class="text-xs text-red-600 hover:underline font-medium">View detail →</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No stock was issued to jobs in this period.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
