@php
    use BlackpigCreatif\Replique\Models\Comment;
    use Illuminate\Support\Str;

    $ancestors = [];
    $cursor = $record;
    $safety = 10;

    while ($cursor->parent_id !== null && $safety-- > 0) {
        $parent = $cursor->parent()->with('commentator')->first();
        if (! $parent) {
            break;
        }
        $ancestors[] = $parent;
        $cursor = $parent;
    }

    $ancestors = array_reverse($ancestors);

    $authorOf = function (Comment $c): string {
        if ($c->commentator) {
            return $c->commentator->name ?? $c->commentator->email ?? 'User #' . $c->commentator_id;
        }

        return $c->anonymous_name ?? $c->anonymous_email ?? 'Anonymous';
    };
@endphp

@if (count($ancestors) > 0)
    <ol class="not-prose space-y-2.5 text-sm">
        @foreach ($ancestors as $i => $ancestor)
            <li
                class="flex flex-col gap-1 border-l-2 border-gray-300 pl-3 dark:border-gray-600"
                style="margin-left: {{ $i * 1.25 }}rem"
            >
                <div class="flex flex-wrap items-center gap-x-1.5 gap-y-0.5 text-xs text-gray-500 dark:text-gray-400">
                    <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $authorOf($ancestor) }}</span>
                    <span aria-hidden="true">·</span>
                    <time datetime="{{ $ancestor->created_at?->toIso8601String() }}">
                        {{ $ancestor->created_at?->diffForHumans() }}
                    </time>
                </div>
                <p class="leading-snug text-gray-600 dark:text-gray-300">
                    {{ Str::limit(strip_tags($ancestor->text ?? $ancestor->original_text ?? ''), 150) }}
                </p>
            </li>
        @endforeach
    </ol>
@endif
