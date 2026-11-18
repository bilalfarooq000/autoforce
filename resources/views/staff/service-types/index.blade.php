<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Service Types</h2>
            @hasanyrole('admin|manager')
                <a href="{{ route('staff.service-types.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-gray-800">
                    + Add Service Type
                </a>
            @endhasanyrole
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <p class="text-sm text-gray-500">Fixed-price packages like "Full Service" or "Oil Change" — pick one on an invoice or quotation instead of typing the description and price every time.</p>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
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
                        <tr class="hover:bg-gray-50 {{ ! $serviceType->is_active ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $serviceType->name }}</div>
                                @if ($serviceType->description)
                                    <div class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($serviceType->description, 60) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 capitalize">{{ $serviceType->type }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $serviceType->branch->name ?? 'All Branches' }}</td>
                            <td class="px-4 py-3 text-right font-medium">PKR {{ number_format($serviceType->default_price, 2) }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @hasanyrole('admin|manager')
                                    <a href="{{ route('staff.service-types.edit', $serviceType) }}" class="text-xs text-red-600 hover:underline">Edit</a>
                                    <form method="POST" action="{{ route('staff.service-types.toggle', $serviceType) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="text-xs text-gray-500 hover:underline ml-2">{{ $serviceType->is_active ? 'Deactivate' : 'Activate' }}</button>
                                    </form>
                                @endhasanyrole
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No service types yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $serviceTypes->links() }}
    </div>
</x-app-layout>
