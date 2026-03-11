<?php

namespace BlackpigCreatif\Replique\Listeners;

use BlackpigCreatif\Replique\Events\CommentPosted;
use BlackpigCreatif\Replique\Notifications\NewCommentNotification;

class SendNewCommentNotification
{
    public function handle(CommentPosted $event): void
    {
        if (! config('replique.notifications.on_new_comment', false)) {
            return;
        }

        $comment = $event->comment;
        $commentable = $comment->commentable;

        if (! $commentable) {
            return;
        }

        /**
         * Host models implement notifyOnComment() returning a notifiable,
         * or the config provides a callable resolver.
         *
         * @example
         * class Post extends Model {
         *     public function notifyOnComment(): ?User { return $this->author; }
         * }
         */
        $notifiable = null;

        if (method_exists($commentable, 'notifyOnComment')) {
            $notifiable = $commentable->notifyOnComment();
        } elseif (is_callable($resolver = config('replique.notifications.notifiable_resolver'))) {
            $notifiable = $resolver($commentable, $comment);
        }

        if (! $notifiable) {
            return;
        }

        $notifiable->notify(new NewCommentNotification($comment));
    }
}
