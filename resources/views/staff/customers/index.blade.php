<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Customers</h2>
            <a href="{{ route('staff.customers.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-gray-800">
                + Add Customer
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or phone"
                   class="w-full rounded-md border-gray-300 shadow-sm text-sm" />
            <button class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm">Search</button>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Vehicles</th>
                        <th class="px-4 py-3">Total Invoiced</th>
                        <th class="px-4 py-3">Outstanding</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($customers as $customer)
                        <tr>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $customer->name }}</div>
                                <div class="text-xs text-gray-500">{{ $customer->phone }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $customer->vehicles_count }}</td>
                            <td class="px-4 py-3">PKR {{ number_format($customer->total_invoiced) }}</td>
                            <td class="px-4 py-3 {{ $customer->total_outstanding > 0 ? 'text-red-600 font-medium' : '' }}">
                                PKR {{ number_format($customer->total_outstanding) }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-3 whitespace-nowrap">
                                <a href="{{ route('staff.vehicles.create', ['customer' => $customer->id]) }}" class="text-gray-600 hover:underline text-xs">+ Register Vehicle</a>
                                <a href="{{ route('staff.customers.show', $customer) }}" class="text-red-600 hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $customers->links() }}
    </div>
</x-app-layout>
