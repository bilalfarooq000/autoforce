<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Branches</h2>
            <a href="{{ route('admin.branches.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm hover:bg-gray-700">
                + Register Branch
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($branches as $branch)
                <div class="bg-white rounded-lg shadow p-5">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-semibold text-lg">{{ $branch->name }}</div>
                            <div class="text-sm text-gray-500">{{ $branch->city }}</div>
                            @if ($branch->address)<div class="text-xs text-gray-400">{{ $branch->address }}</div>@endif
                            @if ($branch->phone)<div class="text-xs text-gray-400">{{ $branch->phone }}</div>@endif
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs px-2 py-1 rounded-full {{ $branch->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <a href="{{ route('admin.branches.edit', $branch) }}" class="text-xs text-red-600 hover:underline">Edit</a>
                        </div>
                    </div>
                    <div class="grid grid-cols-4 gap-2 mt-4 text-center">
                        <div class="bg-gray-50 rounded-md p-2">
                            <div class="text-lg font-bold">{{ $branch->users_count }}</div>
                            <div class="text-xs text-gray-500">Staff</div>
                        </div>
                        <div class="bg-gray-50 rounded-md p-2">
                            <div class="text-lg font-bold">{{ $branch->vehicles_count }}</div>
                            <div class="text-xs text-gray-500">Vehicles</div>
                        </div>
                        <div class="bg-gray-50 rounded-md p-2">
                            <div class="text-lg font-bold">{{ $branch->active_jobs_count }}</div>
                            <div class="text-xs text-gray-500">Active Jobs</div>
                        </div>
                        <div class="bg-gray-50 rounded-md p-2">
                            <div class="text-lg font-bold">{{ number_format($branch->outstanding_amount, 0) }}</div>
                            <div class="text-xs text-gray-500">Due (PKR)</div>
                        </div>
                    </div>
                    <a href="{{ route('staff.dashboard', ['branch' => $branch->id]) }}" class="inline-block mt-3 text-sm text-red-600 hover:underline">
                        View this branch's job board →
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
