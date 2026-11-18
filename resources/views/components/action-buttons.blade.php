@props([
    'view' => null,
    'edit' => null,
    'toggleUrl' => null,
    'active' => true,
    'deleteUrl' => null,
    'deleteConfirm' => 'Are you sure?',
])

<div {{ $attributes->merge(['class' => 'flex items-center justify-end gap-1.5']) }}>
    @if ($view)
        <a href="{{ $view }}" title="View"
           class="inline-flex items-center justify-center w-7 h-7 rounded-md text-blue-600 bg-blue-50 hover:bg-blue-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </a>
    @endif

    @if ($edit)
        <a href="{{ $edit }}" title="Edit"
           class="inline-flex items-center justify-center w-7 h-7 rounded-md text-amber-600 bg-amber-50 hover:bg-amber-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
        </a>
    @endif

    @if ($toggleUrl)
        <form method="POST" action="{{ $toggleUrl }}" onsubmit="return confirm('{{ $active ? 'Deactivate this record?' : 'Reactivate this record?' }}')">
            @csrf
            @method('PATCH')
            <button type="submit" title="{{ $active ? 'Deactivate' : 'Reactivate' }}"
                    class="inline-flex items-center justify-center w-7 h-7 rounded-md {{ $active ? 'text-red-600 bg-red-50 hover:bg-red-100' : 'text-green-600 bg-green-50 hover:bg-green-100' }}">
                @if ($active)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                @endif
            </button>
        </form>
    @endif

    @if ($deleteUrl)
        <form method="POST" action="{{ $deleteUrl }}" onsubmit="return confirm('{{ $deleteConfirm }}')">
            @csrf
            @method('DELETE')
            <button type="submit" title="Delete"
                    class="inline-flex items-center justify-center w-7 h-7 rounded-md text-red-600 bg-red-50 hover:bg-red-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </form>
    @endif
</div>
