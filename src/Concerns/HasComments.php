<?php

namespace BlackpigCreatif\Replique\Concerns;

use BlackpigCreatif\Replique\Enums\CommentStatus;
use BlackpigCreatif\Replique\Enums\TextMode;
use BlackpigCreatif\Replique\Events\CommentPosted;
use BlackpigCreatif\Replique\Models\Comment;
use BlackpigCreatif\Replique\Sanitisers\InjectionSanitiser;
use BlackpigCreatif\Replique\Sanitisers\TextSanitiser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasComments
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Post a comment on this model.
     *
     * @param  Model|null  $commentator  Explicit commentator; falls back to auth()->user()
     * @param  string|null  $email  Anonymous email (used when no commentator)
     * @param  string|null  $name  Anonymous display name
     * @param  int|null  $parentId  Parent comment ID for replies
     * @param  TextMode|null  $textMode  Override config text_mode
     */
    public function comment(
        string $text,
        ?Model $commentator = null,
        ?string $email = null,
        ?string $name = null,
        ?int $parentId = null,
        ?TextMode $textMode = null,
    ): Comment {
        $commentator ??= auth()->user();
        $mode = $textMode ?? TextMode::from(config('replique.text_mode', 'escaped_html'));
        $sanitiser = new TextSanitiser(new InjectionSanitiser);

        $depth = 0;
        if ($parentId !== null) {
            $parent = Comment::find($parentId);
            $depth = $parent ? $parent->depth + 1 : 0;
        }

        $requireApproval = config('replique.require_approval', false);
        $status = $requireApproval ? CommentStatus::Pending : CommentStatus::Approved;

        $comment = new Comment([
            'original_text' => $text,
            'text' => $sanitiser->process($text, $mode),
            'text_mode' => $mode,
            'parent_id' => $parentId,
            'depth' => $depth,
            'status' => $status,
            'approved_at' => $requireApproval ? null : now(),
            'ip_address' => request()->ip(),
            'anonymous_email' => $commentator ? null : $email,
            'anonymous_name' => $commentator ? null : $name,
        ]);

        if ($commentator) {
            $comment->commentator_type = $commentator->getMorphClass();
            $comment->commentator_id = $commentator->getKey();
        }

        $this->comments()->save($comment);

        CommentPosted::dispatch($comment);

        return $comment;
    }
}
