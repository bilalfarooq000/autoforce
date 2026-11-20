<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Purchases</h2>
            @hasanyrole('admin|manager|inventory_manager')
                <a href="{{ route('staff.purchases.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-red-700">
                    + Record Purchase
                </a>
            @endhasanyrole
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <p class="text-sm text-gray-500">Every purchase recorded here automatically receives the stock into Inventory — no separate "Receive Stock" step needed.</p>

        <form method="GET" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="text-xs text-gray-500">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Purchase #, invoice ref, supplier"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
            </div>
            @if ($suppliers->isNotEmpty())
                <div>
                    <label class="text-xs text-gray-500">Supplier</label>
                    <select name="supplier_id" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">All suppliers</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(request('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="text-xs text-gray-500">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
            </div>
            <div>
                <label class="text-xs text-gray-500">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
            </div>
            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm">Filter</button>
            @if (request()->hasAny(['search', 'supplier_id', 'from', 'to']))
                <a href="{{ route('staff.purchases.index') }}" class="text-xs text-red-600 hover:underline pb-2">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-900 text-left text-gray-300 text-xs font-semibold uppercase tracking-wider border-b-2 border-red-600">
                    <tr>
                        <th class="px-4 py-3">Purchase #</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($purchases as $purchase)
                        <tr class="hover:bg-red-50/50 transition-colors">
                            <td class="px-4 py-3 font-medium">{{ $purchase->purchase_number }}</td>
                            <td class="px-4 py-3">{{ $purchase->supplier->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $purchase->purchased_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right font-medium">PKR {{ number_format($purchase->total_amount, 2) }}</td>
                            <td class="px-4 py-3">
                                <x-action-buttons :view="route('staff.purchases.show', $purchase)" />
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No purchases recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        {{ $purchases->links() }}
    </div>
</x-app-layout>
