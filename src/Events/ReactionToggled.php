<?php

namespace BlackpigCreatif\Replique\Events;

use BlackpigCreatif\Replique\Models\Comment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReactionToggled
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly Comment $comment,
        public readonly string $type,
    ) {}
}
