<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Suppliers</h2>
            <a href="{{ route('staff.suppliers.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-gray-800">
                + Add Supplier
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search supplier name"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm focus:border-red-500 focus:ring-red-500" />
            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm">Search</button>
        </form>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3">Branch</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($suppliers as $supplier)
                        <tr class="hover:bg-gray-50 {{ ! $supplier->is_active ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $supplier->name }}</div>
                                @if ($supplier->contact_person)
                                    <div class="text-xs text-gray-500">{{ $supplier->contact_person }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $supplier->phone ?? $supplier->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $supplier->branch->name ?? 'All Branches' }}</td>
                            <td class="px-4 py-3">
                                <x-action-buttons
                                    :edit="route('staff.suppliers.edit', $supplier)"
                                    :toggle-url="route('staff.suppliers.toggle', $supplier)"
                                    :active="$supplier->is_active"
                                />
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No suppliers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $suppliers->links() }}
    </div>
</x-app-layout>
