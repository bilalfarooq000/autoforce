<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $item->name }}</h2>
                <p class="text-sm text-gray-500">{{ $item->category->name ?? '—' }} · {{ $item->branch->name ?? '—' }} @if(! $item->is_active) · <span class="text-red-600 font-medium">Inactive</span> @endif</p>
            </div>
            @hasanyrole('admin|manager|inventory_manager')
                <div class="flex gap-2">
                    <a href="{{ route('staff.inventory.items.receive', $item) }}" class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">+ Receive Stock</a>
                    <a href="{{ route('staff.inventory.items.issue', $item) }}" class="px-3 py-2 bg-orange-600 text-white rounded-lg text-sm font-medium hover:bg-orange-700">Issue Stock</a>
                    <a href="{{ route('staff.inventory.items.adjust', $item) }}" class="px-3 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Adjust</a>
                    <a href="{{ route('staff.inventory.items.edit', $item) }}" class="px-3 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50">Edit</a>
                </div>
            @endhasanyrole
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="text-xs text-gray-500">On Hand</div>
                <div class="text-2xl font-bold {{ $item->isLowStock() ? 'text-red-600' : 'text-gray-900' }}">{{ $item->quantity_on_hand }} {{ $item->unit }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="text-xs text-gray-500">Reorder Level</div>
                <div class="text-2xl font-bold text-gray-900">{{ $item->reorder_level }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="text-xs text-gray-500">Cost / Selling</div>
                <div class="text-lg font-bold text-gray-900">PKR {{ number_format($item->cost_price, 2) }} / {{ number_format($item->selling_price, 2) }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="text-xs text-gray-500">Stock Value</div>
                <div class="text-2xl font-bold text-gray-900">PKR {{ number_format($item->stockValue(), 2) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <div><div class="text-xs text-gray-500">SKU</div><div class="font-medium">{{ $item->sku ?? '—' }}</div></div>
            <div><div class="text-xs text-gray-500">Brand</div><div class="font-medium">{{ $item->brand ?? '—' }}</div></div>
            <div><div class="text-xs text-gray-500">Part Number</div><div class="font-medium">{{ $item->part_number ?? '—' }}</div></div>
        </div>

        @hasanyrole('admin|manager|inventory_manager')
            <form method="POST" action="{{ route('staff.inventory.items.toggle', $item) }}">
                @csrf @method('PATCH')
                <button class="text-xs text-gray-500 hover:underline">{{ $item->is_active ? 'Deactivate this item' : 'Reactivate this item' }}</button>
            </form>
        @endhasanyrole

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-800">Movement History</div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3">Reference / Job</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">By</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($item->movements as $m)
                        <tr>
                            <td class="px-4 py-3 text-gray-500">{{ $m->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-4 py-3">{{ $m->typeLabel() }}</td>
                            <td class="px-4 py-3 text-right font-medium {{ $m->signedQuantity() < 0 ? 'text-red-600' : 'text-green-700' }}">
                                {{ $m->signedQuantity() > 0 ? '+' : '' }}{{ $m->signedQuantity() }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $m->reference ?? '—' }}
                                @if ($m->inspection)
                                    · <a href="{{ route('staff.inspections.show', $m->inspection) }}" class="text-red-600 hover:underline">{{ $m->inspection->vehicle->registration_number ?? 'Job #'.$m->inspection_id }}</a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $m->inspection->vehicle->customer->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $m->performedBy->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No movements recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
