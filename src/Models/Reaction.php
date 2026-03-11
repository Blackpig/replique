<?php

namespace BlackpigCreatif\Replique\Models;

use BlackpigCreatif\Replique\Database\Factories\ReactionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Reaction extends Model
{
    use HasFactory;

    protected $table = 'replique_reactions';

    protected $guarded = [];

    protected static function newFactory(): ReactionFactory
    {
        return ReactionFactory::new();
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function reactor(): MorphTo
    {
        return $this->morphTo();
    }
}
