<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Customers</h2>
            <a href="{{ route('staff.customers.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-red-700">
                + Add Customer
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or phone"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm" />
            <button class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm">Search</button>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-900 text-left text-gray-300 text-xs font-semibold uppercase tracking-wider border-b-2 border-red-600">
                    <tr>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Vehicles</th>
                        <th class="px-4 py-3">Total Invoiced</th>
                        <th class="px-4 py-3">Outstanding</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-red-50/50 transition-colors {{ ! $customer->is_active ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $customer->name }}</div>
                                <div class="text-xs text-gray-500">{{ $customer->phone }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $customer->vehicles_count }}</td>
                            <td class="px-4 py-3">PKR {{ number_format($customer->total_invoiced) }}</td>
                            <td class="px-4 py-3 {{ $customer->total_outstanding > 0 ? 'text-red-600 font-medium' : '' }}">
                                PKR {{ number_format($customer->total_outstanding) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('staff.vehicles.create', ['customer' => $customer->id]) }}" class="text-gray-500 hover:underline text-xs whitespace-nowrap">+ Vehicle</a>
                                    <x-action-buttons
                                        :view="route('staff.customers.show', $customer)"
                                        :edit="route('staff.customers.edit', $customer)"
                                        :toggle-url="route('staff.customers.toggle', $customer)"
                                        :active="$customer->is_active"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        {{ $customers->links() }}
    </div>
</x-app-layout>
