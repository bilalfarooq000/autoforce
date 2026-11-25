<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">System Activity Log</h2>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <form method="GET" class="bg-white rounded-xl border border-gray-200 shadow-sm p-4 flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="text-xs text-gray-500">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description"
                       class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
            </div>
            <div>
                <label class="text-xs text-gray-500">User</label>
                <select name="user_id" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">Everyone</option>
                    @foreach ($causers as $causer)
                        <option value="{{ $causer->id }}" @selected((string) request('user_id') === (string) $causer->id)>{{ $causer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">On</label>
                <select name="subject_type" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">Any record type</option>
                    @foreach ($subjectTypes as $type)
                        <option value="{{ $type }}" @selected(request('subject_type') === $type)>{{ class_basename($type) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">Action</label>
                <select name="event" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500">
                    <option value="">Any action</option>
                    <option value="created" @selected(request('event') === 'created')>Created</option>
                    <option value="updated" @selected(request('event') === 'updated')>Updated</option>
                    <option value="deleted" @selected(request('event') === 'deleted')>Deleted</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
            </div>
            <div>
                <label class="text-xs text-gray-500">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500" />
            </div>
            <button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm">Filter</button>
            @if (request()->hasAny(['search', 'user_id', 'subject_type', 'event', 'from', 'to']))
                <a href="{{ route('admin.activity') }}" class="text-xs text-red-600 hover:underline pb-2">Clear</a>
            @endif
        </form>

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
                                        {{ collect($activity->changes()->get('attributes'))->except(['updated_at'])->map(function ($v, $k) {
                                            if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $v)) {
                                                $v = \Illuminate\Support\Carbon::parse($v)->timezone(config('app.timezone'))->format('d M Y, h:i A');
                                            }
                                            return "{$k}: {$v}";
                                        })->implode(', ') }}
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
