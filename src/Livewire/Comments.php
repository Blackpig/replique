<?php

namespace BlackpigCreatif\Replique\Livewire;

use BlackpigCreatif\Replique\Enums\CommentStatus;
use BlackpigCreatif\Replique\Enums\TextMode;
use BlackpigCreatif\Replique\Models\BlockedIp;
use BlackpigCreatif\Replique\Models\Comment;
use BlackpigCreatif\Replique\Sanitisers\InjectionSanitiser;
use BlackpigCreatif\Replique\Sanitisers\TextSanitiser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Comments extends Component
{
    public Model $model;

    public string $title = 'Comments';

    public ?int $depth = null;

    public bool $requireAuth = false;

    public bool $allowAnonymous = true;

    public bool $requireApproval = false;

    public string $textMode = 'escaped_html';

    /** @var array<string> */
    public array $reactionTypes = [];

    public string $sortOrder = 'asc';

    public string $sortBy = 'created_at';

    public int $perPage = 20;

    // Form state
    public string $commentText = '';

    public ?string $anonymousEmail = null;

    public ?string $anonymousName = null;

    public ?int $replyingToId = null;

    public int $page = 1;

    public function mount(
        Model $model,
        string $title = 'Comments',
        ?int $depth = -1,
        ?bool $requireAuth = null,
        ?bool $allowAnonymous = null,
        ?bool $requireApproval = null,
        ?string $textMode = null,
        ?array $reactionTypes = null,
        string $sortOrder = 'asc',
        string $sortBy = 'created_at',
        int $perPage = 20,
    ): void {
        $this->model = $model;
        $this->title = $title;
        $this->depth = ($depth === -1) ? config('replique.nesting_depth') : $depth;
        $this->requireAuth = $requireAuth ?? config('replique.require_auth', false);
        $this->allowAnonymous = $allowAnonymous ?? config('replique.allow_anonymous', true);
        $this->requireApproval = $requireApproval ?? config('replique.require_approval', false);
        $this->textMode = $textMode ?? config('replique.text_mode', 'escaped_html');
        $this->reactionTypes = $reactionTypes ?? config('replique.reaction_types', []);
        $this->sortOrder = $sortOrder;
        $this->sortBy = $sortBy;
        $this->perPage = $perPage;
    }

    #[Computed]
    public function comments(): Collection
    {
        return $this->model->comments()
            ->whereNull('parent_id')
            ->where(function ($query): void {
                $query->where('status', CommentStatus::Approved);

                if (auth()->check()) {
                    $query->orWhere(function ($q): void {
                        $q->where('status', CommentStatus::Pending)
                            ->where('commentator_type', auth()->user()->getMorphClass())
                            ->where('commentator_id', auth()->id());
                    });
                }
            })
            ->with([
                'reactions',
                'replies' => fn ($q) => $q->where('status', CommentStatus::Approved)->with('reactions')->orderBy('created_at'),
            ])
            ->orderByDesc('is_pinned')
            ->orderBy($this->sortBy, $this->sortOrder)
            ->limit($this->page * $this->perPage)
            ->get();
    }

    #[Computed]
    public function hasMoreComments(): bool
    {
        return $this->model->comments()
            ->whereNull('parent_id')
            ->where('status', CommentStatus::Approved)
            ->count() > ($this->page * $this->perPage);
    }

    public function submitComment(): void
    {
        $ip = request()->ip();
        $isHoneypotSpam = false;

        // Honeypot check — silently mark as spam rather than rejecting
        if (class_exists(\Spatie\Honeypot\SpamProtection::class)) {
            try {
                app(\Spatie\Honeypot\SpamProtection::class)->check(request()->all());
            } catch (\Spatie\Honeypot\SpamException $e) {
                $isHoneypotSpam = true;
            }
        }

        // IP block check
        if (BlockedIp::where('ip_address', $ip)->exists()) {
            $this->addError('commentText', 'Unable to post comment at this time.');

            return;
        }

        // Auth check
        if ($this->requireAuth && ! auth()->check()) {
            $this->addError('commentText', 'You must be logged in to comment.');

            return;
        }

        // Rate limit
        $rateLimitKey = 'replique-comment:' . $ip;
        $limit = config('replique.rate_limit', 5);

        if (RateLimiter::tooManyAttempts($rateLimitKey, $limit)) {
            $this->addError('commentText', 'Too many comments. Please wait before posting again.');

            return;
        }

        RateLimiter::hit($rateLimitKey, 60);

        $rules = ['commentText' => 'required|max:10000'];

        if (! auth()->check()) {
            if ($this->allowAnonymous) {
                $rules['anonymousEmail'] = 'nullable|email|max:255';
                $rules['anonymousName'] = 'nullable|string|max:100';
            } else {
                $this->addError('commentText', 'You must be logged in to comment.');

                return;
            }
        }

        $this->validate($rules);

        $mode = TextMode::from($this->textMode);
        $sanitiser = new TextSanitiser(new InjectionSanitiser);
        $user = auth()->user();

        $requireApproval = $this->requireApproval;
        $status = match (true) {
            $isHoneypotSpam => CommentStatus::Spam,
            $requireApproval => CommentStatus::Pending,
            default => CommentStatus::Approved,
        };

        $depth = 0;
        if ($this->replyingToId !== null) {
            $parent = Comment::find($this->replyingToId);
            $depth = $parent ? $parent->depth + 1 : 0;
        }

        $comment = new Comment([
            'commentable_type' => $this->model->getMorphClass(),
            'commentable_id' => $this->model->getKey(),
            'commentator_type' => $user?->getMorphClass(),
            'commentator_id' => $user?->getKey(),
            'anonymous_email' => $user ? null : $this->anonymousEmail,
            'anonymous_name' => $user ? null : $this->anonymousName,
            'original_text' => $this->commentText,
            'text' => $sanitiser->process($this->commentText, $mode),
            'text_mode' => $mode,
            'parent_id' => $this->replyingToId,
            'depth' => $depth,
            'status' => $status,
            'approved_at' => $requireApproval ? null : now(),
            'ip_address' => $ip,
        ]);

        $comment->save();

        \BlackpigCreatif\Replique\Events\CommentPosted::dispatch($comment);

        $this->commentText = '';
        $this->anonymousEmail = null;
        $this->anonymousName = null;
        $this->replyingToId = null;

        unset($this->comments);

        $this->dispatch('commentPosted');
    }

    public function setReplyingTo(?int $commentId): void
    {
        $this->replyingToId = $commentId;
        $this->commentText = '';
    }

    public function toggleReaction(int $commentId, string $type): void
    {
        if (! in_array($type, $this->reactionTypes, strict: true)) {
            return;
        }

        $comment = Comment::find($commentId);

        if (! $comment) {
            return;
        }

        $comment->react($type);

        unset($this->comments);
    }

    public function loadMore(): void
    {
        $this->page++;
        unset($this->comments);
    }

    public function render(): \Illuminate\View\View
    {
        return view('replique::livewire.comments');
    }
}
