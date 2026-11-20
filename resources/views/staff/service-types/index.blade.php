<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Service Types</h2>
            @hasanyrole('admin|manager')
                <a href="{{ route('staff.service-types.create') }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-red-700">
                    + Add Service Type
                </a>
            @endhasanyrole
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <p class="text-sm text-gray-500">Fixed-price packages like "Full Service" or "Oil Change" — pick one on an invoice or quotation instead of typing the description and price every time.</p>

        <form method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name"
                   class="w-full sm:w-auto sm:flex-1 rounded-lg border-gray-300 shadow-sm text-sm focus:border-red-500 focus:ring-red-500" />
            <select name="type" class="rounded-lg border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                <option value="">All types</option>
                <option value="labor" @selected(request('type') === 'labor')>Labor</option>
                <option value="part" @selected(request('type') === 'part')>Part</option>
            </select>
            <select name="status" class="rounded-lg border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>
            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm">Search</button>
            @if (request()->hasAny(['search', 'type', 'status']))
                <a href="{{ route('staff.service-types.index') }}" class="text-xs text-red-600 hover:underline self-center">Clear</a>
            @endif
        </form>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-900 text-left text-gray-300 text-xs font-semibold uppercase tracking-wider border-b-2 border-red-600">
                    <tr>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Branch</th>
                        <th class="px-4 py-3 text-right">Default Price</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($serviceTypes as $serviceType)
                        <tr class="hover:bg-red-50/50 transition-colors {{ ! $serviceType->is_active ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $serviceType->name }}</div>
                                @if ($serviceType->description)
                                    <div class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($serviceType->description, 60) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 capitalize">{{ $serviceType->type }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $serviceType->branch->name ?? 'All Branches' }}</td>
                            <td class="px-4 py-3 text-right font-medium">PKR {{ number_format($serviceType->default_price, 2) }}</td>
                            <td class="px-4 py-3">
                                @hasanyrole('admin|manager')
                                    <x-action-buttons
                                        :edit="route('staff.service-types.edit', $serviceType)"
                                        :toggle-url="route('staff.service-types.toggle', $serviceType)"
                                        :active="$serviceType->is_active"
                                    />
                                @endhasanyrole
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No service types yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        {{ $serviceTypes->links() }}
    </div>
</x-app-layout>
