<div class="flex items-center gap-3 mt-2">
    @foreach ($reactionTypes as $type)
        @php
            $active = auth()->check() && $comment->relationLoaded('reactions')
                ? $comment->reactions->where('type', $type)->where('reactor_id', auth()->id())->isNotEmpty()
                : false;
        @endphp

        <button
            wire:click="toggleReaction({{ $comment->id }}, '{{ $type }}')"
            type="button"
            class="flex items-center gap-1 text-sm transition-colors {{ $active ? 'text-gray-900' : 'text-gray-400 hover:text-gray-700' }}"
            title="{{ ucfirst($type) }}"
        >
            <x-replique::reaction-icon :type="$type" :active="$active" />
            <span class="font-medium tabular-nums">{{ $comment->reactionCount($type) }}</span>
        </button>
    @endforeach
</div>
