<div class="border rounded-lg p-4 {{ $depth > 0 ? 'ml-6 mt-2' : '' }}">
    <div class="flex items-start gap-3">
        <div class="flex-1">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <span class="font-medium text-gray-800">
                    @if ($comment->commentator)
                        {{ $comment->commentator->name ?? $comment->commentator->email ?? 'User' }}
                    @elseif ($comment->anonymous_name)
                        {{ $comment->anonymous_name }}
                    @else
                        Anonymous
                    @endif
                </span>
                <span aria-hidden="true">·</span>
                <time datetime="{{ $comment->created_at->toIso8601String() }}">
                    {{ $comment->created_at->diffForHumans() }}
                </time>
                @if ($comment->is_pinned)
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-blue-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" class="w-3.5 h-3.5">
                            <path fill-rule="evenodd" d="M6.32 2.577a49.255 49.255 0 0 1 11.36 0c1.497.174 2.57 1.46 2.57 2.93V21a.75.75 0 0 1-1.085.67L12 18.089l-7.165 3.583A.75.75 0 0 1 3.75 21V5.507c0-1.47 1.073-2.756 2.57-2.93Z" clip-rule="evenodd"/>
                        </svg>
                        Pinned
                    </span>
                @endif
            </div>

            @if ($comment->status->value === 'pending')
                <p class="text-xs text-amber-600 mb-1">Your comment is awaiting moderation.</p>
            @endif

            <div class="prose prose-sm max-w-none">
                {!! $comment->text !!}
            </div>

            {{-- Reactions --}}
            @if (! empty($reactionTypes))
                @include('replique::livewire.reactions', ['comment' => $comment])
            @endif

            {{-- Reply button --}}
            @php
                $canReply = $this->depth === null || $depth < $this->depth;
            @endphp

            @if ($canReply)
                <button
                    wire:click="setReplyingTo({{ $replyingToId === $comment->id ? 'null' : $comment->id }})"
                    type="button"
                    class="mt-2 text-xs text-gray-500 underline"
                >
                    {{ $replyingToId === $comment->id ? 'Cancel reply' : 'Reply' }}
                </button>
            @endif

            {{-- Inline reply form --}}
            @if ($replyingToId === $comment->id)
                <div class="mt-3">
                    @include('replique::livewire.comment-form', ['isReply' => true])
                </div>
            @endif
        </div>
    </div>

    {{-- Replies --}}
    @if ($comment->replies->isNotEmpty() && ($this->depth === null || $depth < $this->depth))
        <div class="mt-3 space-y-2">
            @foreach ($comment->replies as $reply)
                @include('replique::livewire.comment-item', ['comment' => $reply, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
