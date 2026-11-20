<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Head Office Overview</h2>
    </x-slot>

    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <x-stat-card label="Branches" :value="$companyStats['total_branches']" icon="building" accent="slate" href="#branches" />
            <x-stat-card label="Total Vehicles" :value="$companyStats['total_vehicles']" icon="car" accent="blue" :href="route('staff.vehicles.index')" />
            <x-stat-card label="Staff" :value="$companyStats['total_staff']" icon="users" accent="slate" :href="route('admin.staff.index')" />
            <x-stat-card label="Customers" :value="$companyStats['total_customers']" icon="users" accent="blue" :href="route('staff.customers.index')" />
            <x-stat-card label="Jobs In Progress" :value="$companyStats['jobs_in_progress']" icon="wrench" accent="amber" href="#charts" />
            <x-stat-card label="Outstanding (PKR)" :value="number_format($companyStats['total_outstanding'])" icon="receipt" accent="red" :href="route('staff.finance.index')" />
            <x-stat-card label="Revenue Collected (PKR)" :value="number_format($companyStats['total_revenue'])" icon="cash" accent="green" :href="route('staff.finance.index')" />
            <x-stat-card label="Inventory Value (PKR)" :value="number_format($companyStats['inventory_value'])" icon="clipboard" accent="slate" :href="route('staff.inventory.items.index')" />
            <x-stat-card label="Low Stock Items" :value="$companyStats['low_stock_items']" icon="wrench" accent="red" :href="route('staff.inventory.reports.index', ['tab' => 'low_stock'])" />
        </div>

        @if ($branches->isNotEmpty())
            @php
                $chartLabels = $branches->pluck('name');
                $chartOutstanding = $branches->pluck('outstanding_amount');
                $chartCollected = $branches->pluck('revenue_collected');
                $chartStaff = $branches->pluck('staff_count');
                $chartVehicles = $branches->pluck('vehicles_count');
                $chartActiveJobs = $branches->pluck('active_jobs');
                $chartAwaiting = $branches->pluck('awaiting_approval');
            @endphp
            <div id="charts" class="grid grid-cols-1 lg:grid-cols-2 gap-6 scroll-mt-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="font-semibold text-gray-800 mb-1">Revenue by Branch</h3>
                    <p class="text-xs text-gray-500 mb-2">Outstanding vs. collected, per branch (PKR)</p>
                    <div id="revenueChartLegend" class="mb-2"></div>
                    <div style="position: relative; height: 260px;">
                        <canvas id="revenueChart" role="img" aria-label="Bar chart comparing outstanding and collected revenue per branch">Revenue by branch, outstanding vs collected.</canvas>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="font-semibold text-gray-800 mb-1">Workshop Activity by Branch</h3>
                    <p class="text-xs text-gray-500 mb-2">Active jobs vs. approvals awaiting decision</p>
                    <div id="activityChartLegend" class="mb-2"></div>
                    <div style="position: relative; height: 260px;">
                        <canvas id="activityChart" role="img" aria-label="Bar chart comparing active jobs and pending approvals per branch">Workshop activity by branch, active jobs vs awaiting approval.</canvas>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="font-semibold text-gray-800 mb-1">Staff &amp; Vehicles by Branch</h3>
                    <p class="text-xs text-gray-500 mb-2">Headcount and registered vehicles, per branch</p>
                    <div id="resourceChartLegend" class="mb-2"></div>
                    <div style="position: relative; height: 260px;">
                        <canvas id="resourceChart" role="img" aria-label="Bar chart comparing staff count and vehicle count per branch">Staff and vehicles by branch.</canvas>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h3 class="font-semibold text-gray-800 mb-1">Company Revenue Split</h3>
                    <p class="text-xs text-gray-500 mb-2">Total outstanding vs. total collected, all branches</p>
                    <div id="splitChartLegend" class="mb-2"></div>
                    <div style="position: relative; height: 260px;">
                        <canvas id="splitChart" role="img" aria-label="Doughnut chart of total outstanding versus total collected revenue">Total outstanding vs total collected revenue.</canvas>
                    </div>
                </div>
            </div>
        @endif

        <div id="branches" class="scroll-mt-4">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-semibold text-gray-800">Branches</h3>
                <a href="{{ route('admin.branches.create') }}" class="text-sm text-red-600 hover:underline font-medium">+ Register Branch</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach ($branches as $branch)
                    <a href="{{ route('staff.dashboard', ['branch' => $branch->id]) }}" class="group block bg-white rounded-xl border border-gray-200 border-t-4 {{ $branch->is_active ? 'border-t-red-600' : 'border-t-gray-300' }} shadow-sm p-5 hover:shadow-lg hover:border-red-200 hover:-translate-y-0.5 transition-all cursor-pointer">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-semibold text-lg text-gray-900">{{ $branch->name }}</div>
                                <div class="text-sm text-gray-500">{{ $branch->city }}</div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full font-medium {{ $branch->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $branch->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-4 text-center">
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
                        <div class="flex flex-wrap justify-between gap-2 mt-3 text-sm">
                            <span class="text-red-600 font-medium">Outstanding: PKR {{ number_format($branch->outstanding_amount) }}</span>
                            <span class="text-green-600 font-medium">Collected: PKR {{ number_format($branch->revenue_collected) }}</span>
                        </div>
                        <div class="mt-3 pt-3 border-t border-gray-100 text-sm font-medium text-red-600 flex items-center justify-between">
                            <span>View full workshop dashboard</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @if ($branches->isNotEmpty())
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
        <script>
            const chartLabels = {{ Js::from($chartLabels) }};
            const brandRed = '#dc2626';
            const brandGreen = '#16a34a';
            const brandBlue = '#2563eb';
            const brandAmber = '#d97706';
            const commonOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } },
                },
            };

            function legend(container, items) {
                const el = document.getElementById(container);
                el.innerHTML = items.map(i => `<span style="display:inline-flex;align-items:center;gap:6px;margin-right:16px;font-size:12px;color:#6b7280"><span style="width:10px;height:10px;border-radius:3px;background:${i.color};display:inline-block"></span>${i.label}</span>`).join('');
            }

            new Chart(document.getElementById('revenueChart'), {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [
                        { label: 'Outstanding', data: {{ Js::from($chartOutstanding) }}, backgroundColor: brandRed, borderRadius: 4, maxBarThickness: 28 },
                        { label: 'Collected', data: {{ Js::from($chartCollected) }}, backgroundColor: brandGreen, borderRadius: 4, maxBarThickness: 28 },
                    ],
                },
                options: {
                    ...commonOptions,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (ctx) => ctx.dataset.label + ': PKR ' + ctx.parsed.y.toLocaleString() } },
                    },
                },
            });
            legend('revenueChartLegend', [{ color: brandRed, label: 'Outstanding' }, { color: brandGreen, label: 'Collected' }]);

            new Chart(document.getElementById('activityChart'), {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [
                        { label: 'Active Jobs', data: {{ Js::from($chartActiveJobs) }}, backgroundColor: brandBlue, borderRadius: 4, maxBarThickness: 28 },
                        { label: 'Awaiting Approval', data: {{ Js::from($chartAwaiting) }}, backgroundColor: brandAmber, borderRadius: 4, maxBarThickness: 28 },
                    ],
                },
                options: commonOptions,
            });
            legend('activityChartLegend', [{ color: brandBlue, label: 'Active Jobs' }, { color: brandAmber, label: 'Awaiting Approval' }]);

            new Chart(document.getElementById('resourceChart'), {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [
                        { label: 'Staff', data: {{ Js::from($chartStaff) }}, backgroundColor: '#64748b', borderRadius: 4, maxBarThickness: 28 },
                        { label: 'Vehicles', data: {{ Js::from($chartVehicles) }}, backgroundColor: brandBlue, borderRadius: 4, maxBarThickness: 28 },
                    ],
                },
                options: commonOptions,
            });
            legend('resourceChartLegend', [{ color: '#64748b', label: 'Staff' }, { color: brandBlue, label: 'Vehicles' }]);

            new Chart(document.getElementById('splitChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Outstanding', 'Collected'],
                    datasets: [{
                        data: [{{ $companyStats['total_outstanding'] }}, {{ $companyStats['total_revenue'] }}],
                        backgroundColor: [brandRed, brandGreen],
                        borderColor: '#ffffff',
                        borderWidth: 3,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (ctx) => ctx.label + ': PKR ' + ctx.parsed.toLocaleString() } },
                    },
                },
            });
            legend('splitChartLegend', [{ color: brandRed, label: 'Outstanding' }, { color: brandGreen, label: 'Collected' }]);
        </script>
    @endif
</x-app-layout>
