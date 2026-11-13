<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Invoice — Select a Customer</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <p class="text-sm text-gray-500">Pick a customer to see their full record — every vehicle and job — then initiate the invoice from the right job.</p>

        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search customer name or phone"
                   class="w-full rounded-lg border-gray-300 shadow-sm text-sm" />
            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm">Search</button>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Vehicles</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $customer->name }}</div>
                                <div class="text-xs text-gray-500">{{ $customer->phone }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $customer->vehicles_count }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('staff.customers.show', $customer) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-900 text-white rounded-lg text-xs font-medium hover:bg-gray-800">
                                    View Record
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $customers->links() }}
    </div>
</x-app-layout>
