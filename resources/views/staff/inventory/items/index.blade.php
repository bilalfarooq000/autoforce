<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inventory — Stock</h2>
            @hasanyrole('admin|manager|inventory_manager')
                <a href="{{ route('staff.inventory.items.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-red-700">
                    + Add Item
                </a>
            @endhasanyrole
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <div class="flex gap-2">
            <a href="{{ route('staff.inventory.items.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request()->routeIs('staff.inventory.items.index') ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">Stock</a>
            <a href="{{ route('staff.inventory.movements.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-white border border-gray-200 text-gray-600 hover:bg-gray-50">Movements</a>
            <a href="{{ route('staff.inventory.reports.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium bg-white border border-gray-200 text-gray-600 hover:bg-gray-50">Reports</a>
        </div>

        <form method="GET" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="text-xs text-gray-500">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, SKU, part number..."
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
            </div>
            <div>
                <label class="text-xs text-gray-500">Category</label>
                <select name="category_id" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">All Categories</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            @if ($branches->isNotEmpty())
                <div>
                    <label class="text-xs text-gray-500">Branch</label>
                    <select name="branch_id" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">All Branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <label class="flex items-center gap-1.5 text-sm text-gray-600 pb-2">
                <input type="checkbox" name="low_stock" value="1" @checked(request()->boolean('low_stock')) class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                Low stock only
            </label>
            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm">Filter</button>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-900 text-left text-gray-300 text-xs font-semibold uppercase tracking-wider border-b-2 border-red-600">
                    <tr>
                        <th class="px-4 py-3">Item</th>
                        <th class="px-4 py-3">Category</th>
                        @if ($branches->isNotEmpty())<th class="px-4 py-3">Branch</th>@endif
                        <th class="px-4 py-3 text-right">On Hand</th>
                        <th class="px-4 py-3 text-right">Reorder At</th>
                        <th class="px-4 py-3 text-right">Cost</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($items as $item)
                        <tr class="hover:bg-red-50/50 transition-colors {{ ! $item->is_active ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-900">{{ $item->name }}</span>
                                <div class="text-xs text-gray-500">{{ $item->sku ?? $item->part_number ?? '—' }} @if($item->brand) · {{ $item->brand }} @endif</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $item->category->name ?? '—' }}</td>
                            @if ($branches->isNotEmpty())<td class="px-4 py-3 text-gray-600">{{ $item->branch->name ?? '—' }}</td>@endif
                            <td class="px-4 py-3 text-right font-semibold {{ $item->isLowStock() ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $item->quantity_on_hand }} {{ $item->unit }}
                                @if ($item->isLowStock())
                                    <span class="ml-1 text-xs px-1.5 py-0.5 rounded-full bg-red-100 text-red-700">Low</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ $item->reorder_level }}</td>
                            <td class="px-4 py-3 text-right text-gray-600">PKR {{ number_format($item->cost_price, 2) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @hasanyrole('admin|manager|inventory_manager')
                                        <a href="{{ route('staff.inventory.items.receive', $item) }}" class="text-xs text-green-700 hover:underline whitespace-nowrap">+Stock</a>
                                        <a href="{{ route('staff.inventory.items.issue', $item) }}" class="text-xs text-orange-700 hover:underline whitespace-nowrap">Issue</a>
                                        <a href="{{ route('staff.inventory.items.adjust', $item) }}" class="text-xs text-gray-600 hover:underline whitespace-nowrap">Adjust</a>
                                    @endhasanyrole
                                    <x-action-buttons
                                        :view="route('staff.inventory.items.show', $item)"
                                        :edit="auth()->user()->hasAnyRole(['admin', 'manager', 'inventory_manager']) ? route('staff.inventory.items.edit', $item) : null"
                                        :toggle-url="auth()->user()->hasAnyRole(['admin', 'manager', 'inventory_manager']) ? route('staff.inventory.items.toggle', $item) : null"
                                        :active="$item->is_active"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No inventory items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        {{ $items->links() }}
    </div>
</x-app-layout>
