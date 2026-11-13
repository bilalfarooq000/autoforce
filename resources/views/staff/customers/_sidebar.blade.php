@php $activeId = $activeId ?? null; @endphp

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col h-full">
    <div class="p-3 border-b border-gray-200 space-y-2">
        <a href="{{ route('staff.customers.create') }}" class="flex items-center justify-center gap-1.5 w-full px-3 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Customer
        </a>
        <form method="GET" action="{{ url()->current() }}">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search customers..."
                   class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
        </form>
    </div>

    <ul class="divide-y divide-gray-100 overflow-y-auto flex-1">
        @forelse ($sidebarCustomers as $sc)
            <li>
                <a href="{{ route('staff.customers.show', $sc) }}" class="block px-3 py-2.5 hover:bg-gray-50 {{ $activeId === $sc->id ? 'bg-red-50 border-l-2 border-red-600' : '' }}">
                    <div class="text-sm font-medium text-gray-900 truncate">{{ $sc->name }}</div>
                    <div class="text-xs text-gray-500 flex justify-between">
                        <span>{{ $sc->phone }}</span>
                        <span class="shrink-0">{{ $sc->vehicles_count }} {{ $sc->vehicles_count === 1 ? 'vehicle' : 'vehicles' }}</span>
                    </div>
                </a>
            </li>
        @empty
            <li class="px-3 py-6 text-sm text-gray-400 text-center">No customers yet.</li>
        @endforelse
    </ul>

    @if ($sidebarCustomers->hasPages())
        <div class="p-2 border-t border-gray-200 text-xs">
            {{ $sidebarCustomers->onEachSide(0)->links() }}
        </div>
    @endif
</div>
