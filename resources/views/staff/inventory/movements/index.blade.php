<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inventory — Movements</h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <div class="flex gap-2">
            <a href="{{ route('staff.inventory.items.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-white border border-gray-200 text-gray-600 hover:bg-gray-50">Stock</a>
            <a href="{{ route('staff.inventory.movements.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request()->routeIs('staff.inventory.movements.index') ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">Movements</a>
            <a href="{{ route('staff.inventory.reports.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-white border border-gray-200 text-gray-600 hover:bg-gray-50">Reports</a>
        </div>

        <form method="GET" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-wrap gap-3 items-end">
            @if ($branches->isNotEmpty())
                <div>
                    <label class="text-xs text-gray-500">Branch</label>
                    <select name="branch_id" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">All Branches</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected(request('branch_id') == $b->id)>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="text-xs text-gray-500">Part</label>
                <select name="item_id" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">All Parts</option>
                    @foreach ($items as $it)
                        <option value="{{ $it->id }}" @selected(request('item_id') == $it->id)>{{ $it->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">Type</label>
                <select name="type" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">All Types</option>
                    @foreach (\App\Models\InventoryMovement::TYPES as $value => $label)
                        <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
            </div>
            <div>
                <label class="text-xs text-gray-500">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
            </div>
            <div class="flex-1 min-w-[160px]">
                <label class="text-xs text-gray-500">Vehicle / Customer</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Reg. number or customer name"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
            </div>
            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm">Filter</button>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Branch</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3">Vehicle</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">By</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($movements as $m)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $m->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-4 py-3"><a href="{{ route('staff.inventory.items.show', $m->item) }}" class="text-red-600 hover:underline">{{ $m->item->name ?? '—' }}</a></td>
                            <td class="px-4 py-3 text-gray-600">{{ $m->branch->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $m->typeLabel() }}</td>
                            <td class="px-4 py-3 text-right font-medium {{ $m->signedQuantity() < 0 ? 'text-red-600' : 'text-green-700' }}">
                                {{ $m->signedQuantity() > 0 ? '+' : '' }}{{ $m->signedQuantity() }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($m->inspection)
                                    <a href="{{ route('staff.inspections.show', $m->inspection) }}" class="text-red-600 hover:underline">{{ $m->inspection->vehicle->registration_number ?? '—' }}</a>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $m->inspection->vehicle->customer->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $m->reference ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $m->performedBy->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-6 text-center text-gray-500">No movements recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $movements->links() }}
    </div>
</x-app-layout>
