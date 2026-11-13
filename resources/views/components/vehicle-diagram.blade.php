@props(['marks', 'inspection' => null, 'editable' => false])

@php
    $severityColor = ['critical' => '#dc2626', 'advisory' => '#d97706', 'satisfactory' => '#16a34a'];
    $views = \App\Support\InspectionChecklist::VIEWS;
@endphp

<div
    x-data="{
        marksByView: {{ $marks->groupBy('view')->map(fn ($g) => $g->map(fn ($m) => ['id' => $m->id, 'x' => (float) $m->x, 'y' => (float) $m->y, 'severity' => $m->severity, 'note' => $m->note])->values())->toJson() }},
        pendingView: null,
        pendingX: 0,
        pendingY: 0,
        marksFor(view) { return this.marksByView[view] || []; },
        place(view, e) {
            @if ($editable)
                const rect = e.currentTarget.getBoundingClientRect();
                this.pendingView = view;
                this.pendingX = Math.round(((e.clientX - rect.left) / rect.width) * 1000) / 10;
                this.pendingY = Math.round(((e.clientY - rect.top) / rect.height) * 1000) / 10;
            @endif
        },
    }"
    class="space-y-3"
>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        @foreach ($views as $viewKey => $viewLabel)
            <div>
                <div class="text-xs font-semibold text-gray-500 text-center mb-1">{{ $viewLabel }}</div>
                <div class="relative bg-gray-50 border rounded-lg" @if($editable) @click="place('{{ $viewKey }}', $event)" @endif>
                    <svg viewBox="0 0 240 240" preserveAspectRatio="xMidYMid meet" class="w-full h-auto text-gray-400 {{ $editable ? 'cursor-crosshair' : '' }}" fill="none" stroke="currentColor" stroke-width="2">
                        @if ($viewKey === 'top')
                            <g transform="translate(66,12) scale(0.45)">
                                <path d="M70,15 C70,8 170,8 170,15 L178,55 C182,90 182,150 182,150 L182,330 C182,330 182,390 178,425 L170,465 C170,472 70,472 70,465 L62,425 C58,390 58,330 58,330 L58,150 C58,150 58,90 62,55 Z" />
                                <path d="M68,70 L172,70 L162,108 L78,108 Z" />
                                <path d="M68,412 L172,412 L162,374 L78,374 Z" />
                                <rect x="78" y="108" width="84" height="266" rx="6" />
                                <rect x="38" y="95" width="20" height="55" rx="6" />
                                <rect x="182" y="95" width="20" height="55" rx="6" />
                                <rect x="38" y="330" width="20" height="55" rx="6" />
                                <rect x="182" y="330" width="20" height="55" rx="6" />
                            </g>
                        @elseif ($viewKey === 'front')
                            <g transform="translate(0,45)">
                                <rect x="30" y="40" width="180" height="100" rx="20" />
                                <path d="M60,40 Q120,8 180,40" />
                                <ellipse cx="55" cy="75" rx="14" ry="10" />
                                <ellipse cx="185" cy="75" rx="14" ry="10" />
                                <rect x="95" y="85" width="50" height="30" rx="4" />
                                <line x1="30" y1="130" x2="210" y2="130" />
                            </g>
                        @elseif ($viewKey === 'rear')
                            <g transform="translate(0,45)">
                                <rect x="30" y="40" width="180" height="100" rx="20" />
                                <path d="M60,40 Q120,15 180,40" />
                                <rect x="42" y="65" width="22" height="14" rx="3" />
                                <rect x="176" y="65" width="22" height="14" rx="3" />
                                <rect x="90" y="90" width="60" height="20" rx="3" />
                                <line x1="30" y1="130" x2="210" y2="130" />
                            </g>
                        @elseif ($viewKey === 'left')
                            <g transform="translate(-10,50) scale(0.8)">
                                <path d="M30,110 L30,90 C30,80 40,55 70,50 L110,30 C130,20 190,20 210,30 L250,50 C275,55 290,75 290,90 L290,110 Z" />
                                <path d="M95,47 L130,33 L195,33 L222,48 L205,50 L100,50 Z" />
                                <line x1="170" y1="50" x2="170" y2="110" />
                                <circle cx="80" cy="112" r="22" />
                                <circle cx="240" cy="112" r="22" />
                            </g>
                        @else
                            <g transform="translate(250,50) scale(-0.8,0.8)">
                                <path d="M30,110 L30,90 C30,80 40,55 70,50 L110,30 C130,20 190,20 210,30 L250,50 C275,55 290,75 290,90 L290,110 Z" />
                                <path d="M95,47 L130,33 L195,33 L222,48 L205,50 L100,50 Z" />
                                <line x1="170" y1="50" x2="170" y2="110" />
                                <circle cx="80" cy="112" r="22" />
                                <circle cx="240" cy="112" r="22" />
                            </g>
                        @endif
                    </svg>

                    <template x-for="m in marksFor('{{ $viewKey }}')" :key="m.id">
                        <div class="absolute w-3 h-3 rounded-full border-2 border-white shadow -translate-x-1/2 -translate-y-1/2 group"
                             :style="`left: ${m.x}%; top: ${m.y}%; background-color: ${ {critical:'#dc2626',advisory:'#d97706',satisfactory:'#16a34a'}[m.severity] }`">
                            <div class="hidden group-hover:block absolute z-10 left-1/2 -translate-x-1/2 bottom-5 bg-gray-900 text-white text-xs rounded px-2 py-1 whitespace-nowrap">
                                <span x-text="m.note"></span>
                            </div>
                        </div>
                    </template>

                    <template x-if="pendingView === '{{ $viewKey }}'">
                        <div class="absolute w-3 h-3 rounded-full bg-red-500 border-2 border-white -translate-x-1/2 -translate-y-1/2 animate-pulse"
                             :style="`left: ${pendingX}%; top: ${pendingY}%`"></div>
                    </template>
                </div>
            </div>
        @endforeach
    </div>

    @if ($editable)
        <form method="POST" action="{{ route('staff.inspections.diagram.store', $inspection) }}" x-show="pendingView" x-cloak class="flex flex-wrap gap-2 items-center justify-center" @click.stop>
            @csrf
            <input type="hidden" name="view" :value="pendingView">
            <input type="hidden" name="x" :value="pendingX">
            <input type="hidden" name="y" :value="pendingY">
            <select name="severity" class="rounded-md border-gray-300 shadow-sm text-xs">
                <option value="advisory">Moderate</option>
                <option value="critical">Poor</option>
                <option value="satisfactory">Good</option>
            </select>
            <input type="text" name="note" placeholder="e.g. Scratch on rear bumper" class="rounded-md border-gray-300 shadow-sm text-xs w-48" required>
            <button class="px-3 py-1.5 bg-red-600 text-white rounded-md text-xs">Pin It</button>
            <button type="button" @click="pendingView = null" class="text-xs text-gray-500">Cancel</button>
        </form>
        <p class="text-xs text-gray-400 text-center">Click any view above to pin a damage/finding location.</p>

        @if ($marks->count())
            <ul class="text-xs divide-y border rounded-md">
                @foreach ($marks as $mark)
                    <li class="px-3 py-1.5 flex justify-between items-center">
                        <span><span class="inline-block w-2 h-2 rounded-full mr-1" style="background-color: {{ $severityColor[$mark->severity] }}"></span>{{ $views[$mark->view] }}: {{ $mark->note }}</span>
                        <form method="POST" action="{{ route('staff.inspections.diagram.destroy', [$inspection, $mark]) }}">
                            @csrf @method('DELETE')
                            <button class="text-gray-400 hover:text-red-600">&times;</button>
                        </form>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</div>
