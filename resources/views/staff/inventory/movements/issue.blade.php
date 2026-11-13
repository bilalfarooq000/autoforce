<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Issue Stock — {{ $item->name }}</h2>
    </x-slot>

    <div class="max-w-xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 mb-4 text-sm">
            <div class="text-xs text-gray-500">Current stock</div>
            <div class="text-2xl font-bold text-gray-900">{{ $item->quantity_on_hand }} {{ $item->unit }}</div>
        </div>

        <form method="POST" action="{{ route('staff.inventory.items.issue.store', $item) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-5">
            @csrf
            <div>
                <x-input-label for="quantity" value="Quantity to Issue" />
                <x-text-input id="quantity" name="quantity" type="number" min="1" max="{{ $item->quantity_on_hand }}" class="block mt-1 w-full" required autofocus />
                <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="inspection_id" value="Job (optional)" />
                <select id="inspection_id" name="inspection_id" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Not tied to a specific job</option>
                    @foreach ($openJobs as $job)
                        <option value="{{ $job->id }}">{{ $job->vehicle->registration_number ?? '—' }} — {{ $job->reason }} ({{ $job->statusLabel() }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('inspection_id')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="reference" value="Note (optional)" />
                <x-text-input id="reference" name="reference" class="block mt-1 w-full" />
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('staff.inventory.items.show', $item) }}" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <x-primary-button>Issue Stock</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
