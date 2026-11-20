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

        <form method="GET" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="text-xs text-gray-500">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice #, registration or customer"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
            </div>
            @if ($branches->count())
                <div>
                    <label class="text-xs text-gray-500">Branch</label>
                    <select name="branch" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" onchange="this.form.submit()">
                        <option value="">All branches</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) $branchId === (string) $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="text-xs text-gray-500">Status</label>
                <select name="status" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    <option value="unpaid" @selected(request('status') === 'unpaid')>Unpaid</option>
                    <option value="partial" @selected(request('status') === 'partial')>Partial</option>
                    <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">Issued from</label>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
            </div>
            <div>
                <label class="text-xs text-gray-500">Issued to</label>
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
            </div>
            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm">Filter</button>
            @if (request()->hasAny(['search', 'branch', 'status', 'from', 'to']))
                <a href="{{ route('staff.finance.index') }}" class="text-xs text-red-600 hover:underline pb-2">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-900 text-left text-gray-300 text-xs font-semibold uppercase tracking-wider border-b-2 border-red-600">
                    <tr class="hover:bg-red-50/50 transition-colors">
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
                        <tr class="hover:bg-red-50/50 transition-colors">
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
        </div>
        {{ $invoices->links() }}
    </div>
</x-app-layout>
