<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Action Center</h2>
            <p class="text-sm text-gray-500 mt-0.5">Everything that needs your attention right now, in one place.</p>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 border-l-4 {{ $overdueInvoices->count() ? 'border-l-red-600' : 'border-l-gray-200' }}">
                <div class="text-xs text-gray-500">Overdue Invoices</div>
                <div class="text-2xl font-bold {{ $overdueInvoices->count() ? 'text-red-600' : 'text-gray-900' }}">{{ $overdueInvoices->count() }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 border-l-4 {{ $pendingApprovals->count() ? 'border-l-amber-500' : 'border-l-gray-200' }}">
                <div class="text-xs text-gray-500">Pending Approvals</div>
                <div class="text-2xl font-bold {{ $pendingApprovals->count() ? 'text-amber-600' : 'text-gray-900' }}">{{ $pendingApprovals->count() }}</div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 border-l-4 {{ $lowStockItems->count() ? 'border-l-red-600' : 'border-l-gray-200' }}">
                <div class="text-xs text-gray-500">Low Stock Alerts</div>
                <div class="text-2xl font-bold {{ $lowStockItems->count() ? 'text-red-600' : 'text-gray-900' }}">{{ $lowStockItems->count() }}</div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-800">Overdue Invoices <span class="text-xs font-normal text-gray-400">(7+ days since issued, still unpaid)</span></div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2">Invoice</th>
                        <th class="px-4 py-2">Customer</th>
                        <th class="px-4 py-2">Issued</th>
                        <th class="px-4 py-2">Days Overdue</th>
                        <th class="px-4 py-2 text-right">Balance</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($overdueInvoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium">{{ $invoice->invoice_number }}</td>
                            <td class="px-4 py-2">{{ $invoice->customer->name }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $invoice->issued_at->format('d M Y') }}</td>
                            <td class="px-4 py-2 text-red-600 font-medium">{{ $invoice->issued_at->diffInDays(now()) }}</td>
                            <td class="px-4 py-2 text-right font-medium">PKR {{ number_format($invoice->balanceDue()) }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('staff.invoices.show', $invoice) }}" class="text-xs text-red-600 hover:underline font-medium">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Nothing overdue.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-800">Pending Approvals</div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2">Vehicle</th>
                        <th class="px-4 py-2">Customer</th>
                        <th class="px-4 py-2">Sent</th>
                        <th class="px-4 py-2 text-right">Amount</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($pendingApprovals as $quotation)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium">{{ $quotation->inspection->vehicle->registration_number }}</td>
                            <td class="px-4 py-2">{{ $quotation->inspection->vehicle->customer->name }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $quotation->created_at->diffForHumans() }}</td>
                            <td class="px-4 py-2 text-right font-medium">PKR {{ number_format($quotation->total_amount) }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('staff.inspections.show', $quotation->inspection) }}" class="text-xs text-red-600 hover:underline font-medium">Open Job</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No pending approvals.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-800">Low Stock Alerts</div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2">Item</th>
                        <th class="px-4 py-2">Branch</th>
                        <th class="px-4 py-2 text-right">On Hand</th>
                        <th class="px-4 py-2 text-right">Reorder At</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($lowStockItems as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 font-medium text-red-600">{{ $item->name }}</td>
                            <td class="px-4 py-2 text-gray-500">{{ $item->branch->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-right">{{ $item->quantity_on_hand }} {{ $item->unit }}</td>
                            <td class="px-4 py-2 text-right text-gray-500">{{ $item->reorder_level }}</td>
                            <td class="px-4 py-2 text-right">
                                <a href="{{ route('staff.inventory.items.receive', $item) }}" class="text-xs text-red-600 hover:underline font-medium">Restock</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">Stock levels are healthy.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
