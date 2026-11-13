@php
    $user = auth()->user();
    $brandContext = $user->hasRole('admin')
        ? 'Head Office'
        : ($user->hasRole('customer') ? 'Customer Portal' : ($user->branch->name ?? 'Workshop'));
@endphp

<aside class="flex flex-col w-64 bg-black text-slate-200 shrink-0 h-full">
    <div class="px-5 py-5 border-b border-white/10">
        <img src="{{ asset('images/autoforce-logo-white.png') }}" alt="AutoForce" class="h-7 w-auto">
        <div class="text-[10px] text-slate-500 font-semibold tracking-wider uppercase mt-1.5">AutoForce DMS</div>
        <div class="text-xs text-slate-400 leading-tight">{{ $brandContext }}</div>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-3">
        @php
            $navItem = function ($route, $label, $icon, $active) {
                $base = 'flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition';
                $state = $active ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white';
                return "<a href=\"{$route}\" class=\"{$base} {$state}\">{$icon}<span>{$label}</span></a>";
            };
            $icons = [
                'grid' => '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>',
                'car' => '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13l1.5-4.5A2 2 0 016.4 7h11.2a2 2 0 011.9 1.5L21 13M5 13h14a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1v-4a1 1 0 011-1zM7 17.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM20 17.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/></svg>',
                'building' => '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m-1 4h1m4-4h1m-1 4h1M9 21v-4h6v4"/></svg>',
                'users' => '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-3a3 3 0 100-6 3 3 0 000 6zm7 1a3 3 0 100-6 3 3 0 000 6z"/></svg>',
                'clock' => '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                'cash' => '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2M3 12a9 9 0 1018 0 9 9 0 00-18 0z"/></svg>',
                'receipt' => '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-6 4h6m-6 4h4M5 3h14a1 1 0 011 1v16l-3-2-2 2-2-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 011-1z"/></svg>',
                'box' => '<svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7.5l-8-4-8 4m16 0l-8 4m8-4v9l-8 4m0-9L4 7.5m8 4v9m-8-9v9l8 4"/></svg>',
            ];
        @endphp

        {!! $navItem(route('dashboard'), 'Dashboard', $icons['grid'], request()->routeIs('dashboard', 'staff.dashboard', 'admin.dashboard', 'customer.dashboard')) !!}

        @hasanyrole('admin|manager|service_advisor|technician|inventory_manager')
            <div class="pt-3 pb-1 px-3 text-xs font-semibold text-slate-500 uppercase">Workshop</div>
            {!! $navItem(route('staff.customers.index'), 'Customers', $icons['users'], request()->routeIs('staff.customers.*')) !!}
            {!! $navItem(route('staff.vehicles.index'), 'Vehicles', $icons['car'], request()->routeIs('staff.vehicles.*', 'staff.inspections.*')) !!}
            {!! $navItem(route('staff.invoices.index'), 'Invoices', $icons['receipt'], request()->routeIs('staff.invoices.*')) !!}
            {!! $navItem(route('staff.finance.index'), 'Finance', $icons['cash'], request()->routeIs('staff.finance.*')) !!}

            <div class="pt-3 pb-1 px-3 text-xs font-semibold text-slate-500 uppercase">Inventory</div>
            {!! $navItem(route('staff.inventory.items.index'), 'Stock', $icons['box'], request()->routeIs('staff.inventory.items.*')) !!}
            {!! $navItem(route('staff.inventory.movements.index'), 'Movements', $icons['clock'], request()->routeIs('staff.inventory.movements.*')) !!}
            {!! $navItem(route('staff.inventory.reports.index'), 'Reports', $icons['cash'], request()->routeIs('staff.inventory.reports.*')) !!}
        @endhasanyrole

        @hasrole('admin')
            <div class="pt-3 pb-1 px-3 text-xs font-semibold text-slate-500 uppercase">Head Office</div>
            {!! $navItem(route('admin.branches.index'), 'Branches', $icons['building'], request()->routeIs('admin.branches.*')) !!}
            {!! $navItem(route('admin.staff.index'), 'Staff', $icons['users'], request()->routeIs('admin.staff.*')) !!}
            {!! $navItem(route('admin.activity'), 'Activity Log', $icons['clock'], request()->routeIs('admin.activity')) !!}
        @endhasrole

        @hasrole('customer')
            {!! $navItem(route('customer.dashboard'), 'My Vehicles', $icons['car'], request()->routeIs('customer.*')) !!}
        @endhasrole
    </nav>

    <div class="px-3 py-4 border-t border-slate-800">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800 text-sm">
            <div class="w-7 h-7 rounded-full bg-slate-700 flex items-center justify-center text-xs font-semibold text-white">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-white truncate">{{ $user->name }}</div>
                <div class="text-xs text-slate-400 truncate">{{ str($user->roles->first()->name ?? 'user')->headline() }}</div>
            </div>
        </a>
        <form method="POST" action="{{ route('logout') }}" class="mt-1">
            @csrf
            <button type="submit" class="w-full text-left px-3 py-2 rounded-md text-sm text-slate-400 hover:bg-slate-800 hover:text-white">
                Log Out
            </button>
        </form>
    </div>
</aside>
