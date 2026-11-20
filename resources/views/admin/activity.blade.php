<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">System Activity Log</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-900 text-left text-gray-300 text-xs font-semibold uppercase tracking-wider border-b-2 border-red-600">
                    <tr><th class="px-4 py-3">When</th><th class="px-4 py-3">Who</th><th class="px-4 py-3">Action</th><th class="px-4 py-3">On</th></tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($activities as $activity)
                        <tr class="hover:bg-red-50/50 transition-colors">
                            <td class="px-4 py-3 text-gray-500">{{ $activity->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-4 py-3">{{ $activity->causer?->name ?? 'System / Customer' }}</td>
                            <td class="px-4 py-3">
                                {{ $activity->description }}
                                @if ($activity->event === 'updated' && $activity->changes()->isNotEmpty())
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ collect($activity->changes()->get('attributes'))->except(['updated_at'])->map(fn ($v, $k) => "{$k}: {$v}")->implode(', ') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No activity recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        {{ $activities->links() }}
    </div>
</x-app-layout>
