<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Head Office Overview</h2>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <x-stat-card label="Branches" :value="$companyStats['total_branches']" icon="building" accent="slate" />
            <x-stat-card label="Total Vehicles" :value="$companyStats['total_vehicles']" icon="car" accent="blue" />
            <x-stat-card label="Staff" :value="$companyStats['total_staff']" icon="users" accent="slate" />
            <x-stat-card label="Jobs In Progress" :value="$companyStats['jobs_in_progress']" icon="wrench" accent="amber" />
            <x-stat-card label="Outstanding (PKR)" :value="number_format($companyStats['total_outstanding'])" icon="receipt" accent="red" />
            <x-stat-card label="Revenue Collected (PKR)" :value="number_format($companyStats['total_revenue'])" icon="cash" accent="green" />
        </div>

        <div>
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-gray-800">Branches</h3>
                <a href="{{ route('admin.branches.create') }}" class="text-sm text-red-600 hover:underline font-medium">+ Register Branch</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($branches as $branch)
                    <a href="{{ route('staff.dashboard', ['branch' => $branch->id]) }}" class="block bg-white rounded-xl border border-gray-200 border-t-4 {{ $branch->is_active ? 'border-t-red-600' : 'border-t-gray-300' }} shadow-sm p-5 hover:shadow-md transition">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-semibold text-lg text-gray-900">{{ $branch->name }}</div>
                                <div class="text-sm text-gray-500">{{ $branch->city }}</div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full font-medium {{ $branch->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="grid grid-cols-4 gap-2 mt-4 text-center">
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="text-lg font-bold text-gray-900">{{ $branch->staff_count }}</div>
                                <div class="text-xs text-gray-500">Staff</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="text-lg font-bold text-gray-900">{{ $branch->vehicles_count }}</div>
                                <div class="text-xs text-gray-500">Vehicles</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="text-lg font-bold text-gray-900">{{ $branch->active_jobs }}</div>
                                <div class="text-xs text-gray-500">Active Jobs</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <div class="text-lg font-bold {{ $branch->awaiting_approval ? 'text-amber-600' : 'text-gray-900' }}">{{ $branch->awaiting_approval }}</div>
                                <div class="text-xs text-gray-500">Awaiting Approval</div>
                            </div>
                        </div>
                        <div class="flex justify-between mt-3 text-sm">
                            <span class="text-red-600 font-medium">Outstanding: PKR {{ number_format($branch->outstanding_amount) }}</span>
                            <span class="text-green-600 font-medium">Collected: PKR {{ number_format($branch->revenue_collected) }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</x-app-layout>
