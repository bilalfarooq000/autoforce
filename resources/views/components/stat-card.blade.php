@props([
    'label' => '',
    'value' => '',
    'icon' => 'grid',
    'accent' => 'slate',
])

@php
    $accents = [
        'slate' => ['ring' => 'bg-slate-100 text-slate-600', 'bar' => 'bg-slate-400'],
        'red' => ['ring' => 'bg-red-50 text-red-600', 'bar' => 'bg-red-500'],
        'amber' => ['ring' => 'bg-amber-50 text-amber-600', 'bar' => 'bg-amber-400'],
        'orange' => ['ring' => 'bg-orange-50 text-orange-600', 'bar' => 'bg-orange-400'],
        'blue' => ['ring' => 'bg-blue-50 text-blue-600', 'bar' => 'bg-blue-500'],
        'green' => ['ring' => 'bg-green-50 text-green-600', 'bar' => 'bg-green-500'],
    ];
    $a = $accents[$accent] ?? $accents['slate'];

    $icons = [
        'grid' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>',
        'car' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13l1.5-4.5A2 2 0 016.4 7h11.2a2 2 0 011.9 1.5L21 13M5 13h14a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1v-4a1 1 0 011-1zM7 17.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM20 17.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>',
        'building' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h1m-1 4h1m4-4h1m-1 4h1M9 21v-4h6v4"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-3a3 3 0 100-6 3 3 0 000 6zm7 1a3 3 0 100-6 3 3 0 000 6z"/>',
        'clipboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 0h6m-9 4h12M6 9a2 2 0 00-2 2v9a2 2 0 002 2h12a2 2 0 002-2v-9a2 2 0 00-2-2"/>',
        'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'wrench' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
        'check-circle' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        'receipt' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m-6 4h6m-6 4h4M5 3h14a1 1 0 011 1v16l-3-2-2 2-2-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 011-1z"/>',
        'cash' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2M3 12a9 9 0 1018 0 9 9 0 00-18 0z"/>',
    ];
    $svg = $icons[$icon] ?? $icons['grid'];
@endphp

<div {{ $attributes->merge(['class' => 'relative bg-white rounded-xl border border-gray-200 shadow-sm p-4 overflow-hidden']) }}>
    <div class="absolute inset-y-0 left-0 w-1 {{ $a['bar'] }}"></div>
    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <div class="text-xs font-medium text-gray-500 leading-snug">{{ $label }}</div>
            <div class="text-2xl font-bold text-gray-900 mt-1 tabular-nums">{{ $value }}</div>
        </div>
        <div class="shrink-0 w-9 h-9 rounded-lg {{ $a['ring'] }} flex items-center justify-center">
            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $svg !!}</svg>
        </div>
    </div>
</div>
