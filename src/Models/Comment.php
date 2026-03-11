<?php

namespace BlackpigCreatif\Replique\Models;

use BlackpigCreatif\Replique\Concerns\HasComments;
use BlackpigCreatif\Replique\Database\Factories\CommentFactory;
use BlackpigCreatif\Replique\Enums\CommentStatus;
use BlackpigCreatif\Replique\Enums\TextMode;
use BlackpigCreatif\Replique\Events\CommentApproved;
use BlackpigCreatif\Replique\Events\CommentMarkedAsSpam;
use BlackpigCreatif\Replique\Events\CommentRejected;
use BlackpigCreatif\Replique\Events\ReactionToggled;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Comment extends Model
{
    use HasComments;
    use HasFactory;
    use SoftDeletes;

    protected $table = 'replique_comments';

    protected $guarded = [];

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }

    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
            'text_mode' => TextMode::class,
            'is_pinned' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function commentator(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    protected static function booted(): void
    {
        static::saved(function (Comment $comment): void {
            if ($comment->is_pinned && $comment->wasChanged('is_pinned')) {
                static::query()
                    ->where('commentable_type', $comment->commentable_type)
                    ->where('commentable_id', $comment->commentable_id)
                    ->where('id', '!=', $comment->id)
                    ->where('is_pinned', true)
                    ->update(['is_pinned' => false]);
            }
        });
    }

    public function approve(): static
    {
        $this->status = CommentStatus::Approved;
        $this->approved_at = now();
        $this->approved_by = Auth::id();
        $this->save();

        CommentApproved::dispatch($this);

        return $this;
    }

    public function reject(): static
    {
        $this->status = CommentStatus::Rejected;
        $this->save();

        CommentRejected::dispatch($this);

        return $this;
    }

    public function markAsSpam(): static
    {
        $this->status = CommentStatus::Spam;
        $this->save();

        CommentMarkedAsSpam::dispatch($this);

        return $this;
    }

    public function react(string $type): static
    {
        $reactor = Auth::user();

        $existing = $this->reactions()
            ->where('type', $type)
            ->when(
                $reactor,
                fn ($q) => $q->where('reactor_type', $reactor->getMorphClass())
                    ->where('reactor_id', $reactor->getKey()),
            )
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            $this->reactions()->create([
                'reactor_type' => $reactor?->getMorphClass(),
                'reactor_id' => $reactor?->getKey(),
                'type' => $type,
            ]);
        }

        ReactionToggled::dispatch($this, $type);

        return $this;
    }

    public function reactionCount(string $type): int
    {
        if ($this->relationLoaded('reactions')) {
            return $this->reactions->where('type', $type)->count();
        }

        return $this->reactions()->where('type', $type)->count();
    }

    /**
     * @return array<string, int>
     */
    public function reactionSummary(): array
    {
        $types = config('replique.reaction_types', []);

        return collect($types)
            ->mapWithKeys(fn (string $type): array => [
                $type => $this->reactionCount($type),
            ])
            ->toArray();
    }

    public function scopeApproved(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', CommentStatus::Approved);
    }

    public function scopeTopLevel(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopePending(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('status', CommentStatus::Pending);
    }
}
