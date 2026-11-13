<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Finance</h2>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-xs text-gray-500">Total Invoiced</div>
                <div class="text-xl font-bold">PKR {{ number_format($stats['total_invoiced']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-xs text-gray-500">Total Collected</div>
                <div class="text-xl font-bold text-green-600">PKR {{ number_format($stats['total_collected']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-xs text-gray-500">Outstanding</div>
                <div class="text-xl font-bold text-red-600">PKR {{ number_format($stats['total_outstanding']) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-xs text-gray-500">Unpaid Invoices</div>
                <div class="text-xl font-bold">{{ $stats['unpaid_count'] }}</div>
            </div>
        </div>

        <form method="GET" class="flex gap-2">
            @if ($branches->count())
                <select name="branch" class="rounded-md border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                    <option value="">All branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) $branchId === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="status" class="rounded-md border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                <option value="partial" @selected(request('status') === 'partial')>Partial</option>
                <option value="paid" @selected(request('status') === 'paid')>Paid</option>
            </select>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3">Invoice</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Branch</th>
                        <th class="px-4 py-3">Issued</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3">Balance</th>
                        <th class="px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($invoices as $invoice)
                        <tr>
                            <td class="px-4 py-3">
                                <a href="{{ route('staff.invoices.show', $invoice) }}" class="text-red-600 hover:underline font-medium">{{ $invoice->invoice_number }}</a>
                            </td>
                            <td class="px-4 py-3">{{ $invoice->customer->name }}</td>
                            <td class="px-4 py-3">{{ $invoice->vehicle->branch->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $invoice->issued_at->format('d M Y') }}</td>
                            <td class="px-4 py-3">PKR {{ number_format($invoice->total) }}</td>
                            <td class="px-4 py-3 {{ $invoice->balanceDue() > 0 ? 'text-red-600 font-medium' : '' }}">PKR {{ number_format($invoice->balanceDue()) }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : ($invoice->status === 'partial' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700') }}">
                                    {{ str($invoice->status)->headline() }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">No invoices found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $invoices->links() }}
    </div>
</x-app-layout>
