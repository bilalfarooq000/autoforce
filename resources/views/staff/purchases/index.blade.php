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
