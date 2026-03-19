<?php

namespace BlackpigCreatif\Replique\Notifications;

use BlackpigCreatif\Replique\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Comment $comment) {}

    /**
     * @return array<string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $commentatorLabel = $this->comment->commentator
            ? ($this->comment->commentator->name ?? $this->comment->commentator->email ?? 'A user')
            : ($this->comment->anonymous_name ?? $this->comment->anonymous_email ?? 'An anonymous visitor');

        return (new MailMessage)
            ->subject('New comment posted')
            ->line("{$commentatorLabel} posted a new comment:")
            ->line('"' . Str::limit(strip_tags($this->comment->text), 200) . '"')
            ->when(
                $this->comment->status->value === 'pending',
                fn (MailMessage $mail): MailMessage => $mail->line('This comment is awaiting your approval.'),
            );
    }
}
