<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Staff Accounts</h2>
            <a href="{{ route('admin.staff.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">
                + Add Staff
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-4">
        <form method="GET" class="flex gap-2">
            <select name="branch_id" class="rounded-md border-gray-300 shadow-sm text-sm" onchange="this.form.submit()">
                <option value="">All branches</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
        </form>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50/80 text-left text-gray-500 text-xs font-semibold uppercase tracking-wide border-b border-gray-200">
                    <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Role</th><th class="px-4 py-3">Branch</th><th class="px-4 py-3">Status</th><th></th></tr>
                </thead>
                <tbody class="divide-y">
                    @foreach ($staff as $member)
                        <tr>
                            <td class="px-4 py-3">{{ $member->name }}<br><span class="text-xs text-gray-500">{{ $member->email }}</span></td>
                            <td class="px-4 py-3">{{ str($member->roles->first()->name ?? '—')->headline() }}</td>
                            <td class="px-4 py-3">{{ $member->branch->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="text-xs px-2 py-1 rounded-full {{ $member->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $member->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.staff.edit', $member) }}" class="text-red-600 hover:underline mr-3">Edit</a>
                                @if ($member->is_active)
                                    <form method="POST" action="{{ route('admin.staff.destroy', $member) }}" onsubmit="return confirm('Deactivate this staff account?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline">Deactivate</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $staff->links() }}
    </div>
</x-app-layout>
