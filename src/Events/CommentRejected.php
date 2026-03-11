<?php

namespace BlackpigCreatif\Replique\Events;

use BlackpigCreatif\Replique\Models\Comment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentRejected
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly Comment $comment) {}
}
