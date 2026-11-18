<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bookings</h2>
            @hasanyrole('admin|manager|service_advisor')
                <a href="{{ route('staff.bookings.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium shadow-sm hover:bg-gray-800">
                    + New Booking
                </a>
            @endhasanyrole
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        @if ($branches->isNotEmpty())
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Public Booking Links</div>
                <div class="space-y-1">
                    @foreach ($branches as $branch)
                        <div class="flex items-center gap-2 text-sm">
                            <span class="text-gray-600 w-28 shrink-0">{{ $branch->name }}:</span>
                            <code class="text-xs bg-gray-50 px-2 py-1 rounded text-gray-700 truncate">{{ route('portal.book.show', $branch) }}</code>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-2">Share this link with customers (website, WhatsApp) — no login needed for them to request a slot.</p>
            </div>
        @endif

        <div class="flex gap-2">
            <a href="{{ route('staff.bookings.index') }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ ! request('status') ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">Upcoming</a>
            @foreach (\App\Models\Booking::STATUSES as $value => $label)
                <a href="{{ route('staff.bookings.index', ['status' => $value]) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('status') === $value ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600' }}">{{ $label }}</a>
            @endforeach
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3">When</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Vehicle</th>
                        <th class="px-4 py-3">Service</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">{{ $booking->requested_at->format('d M, h:i A') }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $booking->customer_name }}</div>
                                <div class="text-xs text-gray-500">{{ $booking->customer_phone }}</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $booking->vehicle_registration_number ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $booking->serviceType->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $badge = ['pending' => 'bg-amber-100 text-amber-700', 'confirmed' => 'bg-blue-100 text-blue-700', 'completed' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-gray-100 text-gray-500', 'no_show' => 'bg-red-100 text-red-700'];
                                @endphp
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $badge[$booking->status] ?? 'bg-gray-100' }}">{{ $booking->statusLabel() }}</span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if (in_array($booking->status, ['pending', 'confirmed']))
                                    @hasanyrole('admin|manager|service_advisor')
                                        @if ($booking->status === 'pending')
                                            <form method="POST" action="{{ route('staff.bookings.status', $booking) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <input type="hidden" name="status" value="confirmed">
                                                <button type="submit" class="text-xs text-blue-600 hover:underline">Confirm</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('staff.bookings.convert.create', $booking) }}" class="text-xs text-red-600 hover:underline ml-2">Check In</a>
                                        <form method="POST" action="{{ route('staff.bookings.status', $booking) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="text-xs text-gray-500 hover:underline ml-2">Cancel</button>
                                        </form>
                                    @endhasanyrole
                                @elseif ($booking->inspection_id)
                                    <a href="{{ route('staff.inspections.show', $booking->inspection_id) }}" class="text-xs text-red-600 hover:underline">View Job</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No bookings here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $bookings->links() }}
    </div>
</x-app-layout>
