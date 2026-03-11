<form wire:submit="submitComment" class="mt-4 space-y-3">
    @if (class_exists(\Spatie\Honeypot\HoneypotFormFields::class))
        @honeypot
    @endif

    @if (! auth()->check() && $allowAnonymous)
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="replique-name" class="block text-sm font-medium text-gray-700">Name (optional)</label>
                <input
                    id="replique-name"
                    type="text"
                    wire:model="anonymousName"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                    maxlength="100"
                >
                @error('anonymousName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="replique-email" class="block text-sm font-medium text-gray-700">Email (optional)</label>
                <input
                    id="replique-email"
                    type="email"
                    wire:model="anonymousEmail"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
                    maxlength="255"
                >
                @error('anonymousEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    @endif

    <div>
        <label for="replique-comment" class="block text-sm font-medium text-gray-700">
            {{ isset($isReply) && $isReply ? 'Your reply' : 'Leave a comment' }}
        </label>
        <textarea
            id="replique-comment"
            wire:model="commentText"
            rows="4"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm"
            maxlength="10000"
            required
        ></textarea>
        @error('commentText') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-3">
        <button
            type="submit"
            wire:loading.attr="disabled"
            class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700 disabled:opacity-50"
        >
            <span wire:loading.remove>Post</span>
            <span wire:loading>Posting…</span>
        </button>

        @if ($requireApproval)
            <p class="text-xs text-gray-500">Your comment will be reviewed before appearing.</p>
        @endif
    </div>

</form>
