<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $inspection->vehicle->registration_number }} — {{ $inspection->reason }}
                </h2>
                <p class="text-sm text-gray-500">{{ $inspection->vehicle->customer->name }} · {{ $inspection->vehicle->customer->phone }}</p>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-medium">
                {{ $inspection->statusLabel() }}
            </span>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">

        @error('checklist')
            <div class="rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ $message }}</div>
        @enderror
        @error('tasks')
            <div class="rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ $message }}</div>
        @enderror

        <!-- Lifecycle actions -->
        <div class="bg-white rounded-lg shadow p-4 flex flex-wrap gap-3 items-center">
            @if ($inspection->status === 'checked_in')
                <form method="POST" action="{{ route('staff.inspections.start', $inspection) }}" class="flex items-center gap-2">
                    @csrf @method('PATCH')
                    <select name="technician_id" class="rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Assign technician...</option>
                        @foreach ($technicians as $tech)
                            <option value="{{ $tech->id }}" @selected($inspection->technician_id === $tech->id)>{{ $tech->name }}</option>
                        @endforeach
                    </select>
                    <x-primary-button>Start Inspection</x-primary-button>
                </form>
            @endif

            @if (in_array($inspection->status, ['checked_in', 'inspecting']))
                <form method="POST" action="{{ route('staff.inspections.ready-no-repair', $inspection) }}" onsubmit="return confirm('No repair needed — mark ready for collection?')">
                    @csrf @method('PATCH')
                    <x-secondary-button>No Repair Needed — Ready for Collection</x-secondary-button>
                </form>
            @endif

            @if ($inspection->status === 'in_repair')
                <form method="POST" action="{{ route('staff.inspections.ready', $inspection) }}">
                    @csrf @method('PATCH')
                    <x-primary-button>Mark Repair Complete — Ready for Collection</x-primary-button>
                </form>
            @endif

            @if ($inspection->status === 'ready_for_collection')
                <form method="POST" action="{{ route('staff.inspections.collected', $inspection) }}" onsubmit="return confirm('Confirm vehicle has been collected by the customer?')">
                    @csrf @method('PATCH')
                    <x-primary-button>Mark Vehicle Collected</x-primary-button>
                </form>
            @endif

            @if ($inspection->status === 'completed')
                <span class="text-sm text-green-700">Job closed — collected {{ $inspection->collected_at?->format('d M Y, h:i A') }}</span>
            @endif
        </div>

        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex items-center justify-between gap-4">
            <div>
                <div class="text-sm font-semibold text-gray-800">Secure Customer Link</div>
                <div class="text-xs text-gray-500 break-all">{{ $inspection->portalUrl() }}</div>
            </div>
            <button type="button" onclick="navigator.clipboard.writeText('{{ $inspection->portalUrl() }}'); this.innerText='Copied!'"
                    class="shrink-0 px-3 py-1.5 bg-gray-900 text-white rounded-md text-xs hover:bg-gray-800">Copy Link</button>
        </div>

        @php
            $checklistItems = $inspection->items->whereNotNull('category')->groupBy('category');
            $adHocItems = $inspection->items->whereNull('category');
            $uncheckedCount = $inspection->items->where('is_checked', false)->count();
            $canEdit = in_array($inspection->status, ['checked_in', 'inspecting']);
            $urgencyColor = ['critical' => 'bg-red-100 text-red-700', 'advisory' => 'bg-amber-100 text-amber-700', 'satisfactory' => 'bg-green-100 text-green-700'];
            $partsLabel = ['critical' => 'Faulty', 'advisory' => 'Replaceable', 'satisfactory' => 'OK'];
        @endphp

        <div class="bg-white rounded-lg shadow p-4">
            <div class="font-semibold text-gray-700 mb-3">Vehicle Diagram — Damage / Finding Locations</div>
            <x-vehicle-diagram :marks="$inspection->diagramMarks" :inspection="$inspection" :editable="$canEdit" />
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
             @if ($canEdit)
                 x-data="{
                     items: { @foreach ($checklistItems->flatten() as $item) {{ $item->id }}: {{ $item->urgency ? "'{$item->urgency}'" : 'null' }}, @endforeach },
                     dirty: false,
                     set(id, value) { this.items[id] = value; this.dirty = true; },
                 }"
             @endif>
            <div class="px-4 py-3 border-b font-semibold text-gray-700 flex justify-between items-center">
                <span>Standard Inspection Checklist</span>
                <span class="text-xs {{ $uncheckedCount ? 'text-amber-600' : 'text-green-600' }}">
                    {{ $inspection->items->count() - $uncheckedCount }} / {{ $inspection->items->count() }} checked
                </span>
            </div>

            <form method="POST" action="{{ route('staff.inspections.items.batch-update', $inspection) }}">
                @csrf
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-3 py-2 text-left w-8">#</th>
                            <th class="px-3 py-2 text-left">Part</th>
                            <th class="px-2 py-2 text-center w-20">Good</th>
                            <th class="px-2 py-2 text-center w-20">Moderate</th>
                            <th class="px-2 py-2 text-center w-20">Poor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $num = 0; @endphp
                        @foreach ($checklistItems as $category => $categoryItems)
                            <tr class="bg-gray-50">
                                <td colspan="5" class="px-3 py-1.5 text-xs font-semibold text-gray-500 uppercase">{{ $category }}</td>
                            </tr>
                            @foreach ($categoryItems as $item)
                                @php $num++; @endphp
                                <tr class="border-t">
                                    <td class="px-3 py-2 text-gray-400">{{ $num }}</td>
                                    <td class="px-3 py-2">
                                        <div class="font-medium">{{ $item->title }}</div>
                                    </td>
@php
                                        $btnActiveClass = [
                                            'satisfactory' => 'bg-green-600 border-green-600',
                                            'advisory' => 'bg-amber-500 border-amber-500',
                                            'critical' => 'bg-red-600 border-red-600',
                                        ];
                                    @endphp
                                    @if ($canEdit)
                                        <input type="hidden" :name="`items[{{ $item->id }}][id]`" value="{{ $item->id }}">
                                        <input type="hidden" :name="`items[{{ $item->id }}][urgency]`" :value="items[{{ $item->id }}]">
                                        @foreach (['satisfactory', 'advisory', 'critical'] as $value)
                                            <td class="px-2 py-2 text-center">
                                                <button type="button" @click="set({{ $item->id }}, '{{ $value }}')"
                                                        class="w-6 h-6 rounded-full border-2 transition-colors"
                                                        :class="items[{{ $item->id }}] === '{{ $value }}' ? '{{ $btnActiveClass[$value] }}' : 'border-gray-300 hover:border-gray-400'"></button>
                                            </td>
                                        @endforeach
                                    @else
                                        @foreach (['satisfactory', 'advisory', 'critical'] as $value)
                                            <td class="px-2 py-2 text-center">
                                                @if ($item->urgency === $value)
                                                    <span class="inline-block w-4 h-4 rounded-full {{ ['satisfactory' => 'bg-green-600', 'advisory' => 'bg-amber-500', 'critical' => 'bg-red-600'][$value] }}"></span>
                                                @endif
                                            </td>
                                        @endforeach
                                    @endif
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>

                @if ($canEdit)
                    <div class="px-4 py-3 border-t bg-gray-50 flex items-center justify-between">
                        <p class="text-xs text-gray-500">Click Good / Moderate / Poor for as many parts as you like, then save once — nothing is saved until you click Save.</p>
                        <button type="submit" :disabled="!dirty"
                                class="px-4 py-2 rounded-lg text-sm font-medium text-white shrink-0"
                                :class="dirty ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-300 cursor-not-allowed'">
                            Save Checklist
                        </button>
                    </div>
                @endif
            </form>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-semibold text-gray-700">Additional Findings</div>
            <ul class="divide-y">
                @forelse ($adHocItems as $item)
                    <li class="px-4 py-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-medium">{{ $item->title }}</div>
                                <p class="text-sm text-gray-600 mt-1">{{ $item->technician_comment }}</p>
                            </div>
                            <div class="text-right shrink-0 ml-4">
                                <span class="text-xs px-2 py-1 rounded-full {{ $urgencyColor[$item->urgency] }}">{{ str($item->urgency)->headline() }}</span>
                                @if ($item->estimated_cost)
                                    <div class="text-sm font-medium mt-1">PKR {{ number_format($item->estimated_cost) }}</div>
                                @endif
                            </div>
                        </div>
                        @foreach (['before' => 'Before', 'after' => 'After'] as $stage => $stageLabel)
                            @php $media = $item->media->where('stage', $stage); @endphp
                            @if ($media->count())
                                <div class="mt-3">
                                    <div class="text-xs text-gray-400 uppercase mb-1">{{ $stageLabel }}</div>
                                    <div class="flex gap-2 flex-wrap">
                                        @foreach ($media as $m)
                                            @if ($m->type === 'photo')
                                                <img src="{{ $m->url() }}" class="w-24 h-24 object-cover rounded-md border" />
                                            @else
                                                <video src="{{ $m->url() }}" class="w-24 h-24 object-cover rounded-md border" muted></video>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-gray-500">No additional findings beyond the checklist.</li>
                @endforelse
            </ul>

            @if ($canEdit)
                <form method="POST" action="{{ route('staff.inspections.items.store', $inspection) }}" enctype="multipart/form-data" class="p-4 border-t space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <x-text-input name="title" placeholder="Finding title (e.g. Worn suspension bushes)" class="w-full" required />
                        <select name="urgency" class="rounded-md border-gray-300 shadow-sm text-sm" required>
                            <option value="satisfactory">Satisfactory</option>
                            <option value="advisory" selected>Advisory</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <textarea name="technician_comment" rows="2" class="w-full rounded-md border-gray-300 shadow-sm text-sm" placeholder="Technician comments"></textarea>
                    <x-text-input name="estimated_cost" type="number" step="0.01" placeholder="Estimated cost (PKR, optional)" class="w-full md:w-64" />
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs text-gray-500">Before photos/videos</label>
                            <input type="file" name="before_media[]" multiple accept="image/*,video/*" class="block text-sm mt-1" />
                        </div>
                        <div>
                            <label class="text-xs text-gray-500">After photos/videos (repair evidence)</label>
                            <input type="file" name="after_media[]" multiple accept="image/*,video/*" class="block text-sm mt-1" />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <x-secondary-button>+ Add Finding Outside Checklist</x-secondary-button>
                    </div>
                </form>
            @endif
        </div>

        @if ($canEdit)
            <form method="POST" action="{{ route('staff.inspections.complete-diagnosis', $inspection) }}" class="bg-white rounded-lg shadow p-4 space-y-3">
                @csrf
                @method('PATCH')
                @if ($uncheckedCount)
                    <p class="text-xs text-amber-600">{{ $uncheckedCount }} checklist item(s) still need to be checked before you can complete diagnosis.</p>
                @endif
                <x-input-label for="summary" value="Inspection Summary" />
                <textarea id="summary" name="summary" rows="2" class="w-full rounded-md border-gray-300 shadow-sm text-sm">{{ $inspection->summary }}</textarea>
                <div class="flex justify-end">
                    <x-primary-button>Complete Diagnosis & Notify Customer</x-primary-button>
                </div>
            </form>
        @endif

        @if ($inspection->invoice)
            <div class="bg-white rounded-lg shadow p-4 flex justify-between items-center">
                <div>
                    <div class="font-semibold">Invoice {{ $inspection->invoice->invoice_number }}</div>
                    <div class="text-sm text-gray-500">PKR {{ number_format($inspection->invoice->total) }}</div>
                </div>
                <a href="{{ route('staff.invoices.show', $inspection->invoice) }}" class="text-red-600 hover:underline text-sm">View Invoice</a>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 font-semibold text-gray-800 flex items-center justify-between">
                <span>Job Comments &amp; Progress Updates</span>
                <span class="text-xs text-gray-400 font-normal">Every comment emails the customer</span>
            </div>

            <form method="POST" action="{{ route('staff.inspections.comments.store', $inspection) }}" class="p-4 border-b border-gray-100 space-y-2">
                @csrf
                <textarea name="body" rows="2" required maxlength="2000"
                          class="w-full rounded-lg border-gray-300 text-sm focus:border-red-500 focus:ring-red-500"
                          placeholder="e.g. Suspension parts ordered, expected by tomorrow afternoon."></textarea>
                <div class="flex justify-end">
                    <x-primary-button>Add Comment &amp; Notify Customer</x-primary-button>
                </div>
            </form>

            <ul class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                @forelse ($inspection->comments as $comment)
                    <li class="px-4 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm text-gray-800">{{ $comment->body }}</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    {{ $comment->author->name }}
                                    @if ($comment->stage)
                                        · <span class="text-gray-400">{{ \App\Models\Inspection::STATUSES[$comment->stage] ?? $comment->stage }}</span>
                                    @endif
                                    · {{ $comment->created_at->diffForHumans() }}
                                </div>
                            </div>
                            <span class="shrink-0 text-xs px-2 py-0.5 rounded-full bg-blue-50 text-blue-600 font-medium">Customer Notified</span>
                        </div>
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-gray-500">No progress updates posted yet.</li>
                @endforelse
            </ul>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b font-semibold text-gray-700">Communication Log</div>
            <ul class="divide-y text-sm">
                @forelse ($inspection->notifications as $n)
                    <li class="px-4 py-2 flex justify-between">
                        <span>{{ str($n->type)->headline() }} <span class="text-xs text-gray-400">({{ $n->channel }})</span></span>
                        <span class="text-xs text-gray-400">{{ $n->created_at->diffForHumans() }} · {{ $n->status }}</span>
                    </li>
                @empty
                    <li class="px-4 py-6 text-sm text-gray-500">No notifications sent yet for this job.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>
