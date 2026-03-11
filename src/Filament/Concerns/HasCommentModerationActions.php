<?php

namespace BlackpigCreatif\Replique\Filament\Concerns;

use BlackpigCreatif\Replique\Enums\CommentStatus;
use BlackpigCreatif\Replique\Models\Comment;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

trait HasCommentModerationActions
{
    protected function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Approve')
            ->icon(Heroicon::CheckCircle)
            ->color('success')
            ->hidden(
                fn (Comment $record): bool => $record->status === CommentStatus::Approved || filled($record->deleted_at)
            )
            ->action(function (Comment $record): void {
                $record->approve();
            })
            ->successNotificationTitle('Comment approved.');
    }

    protected function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->icon(Heroicon::XCircle)
            ->color('danger')
            ->hidden(
                fn (Comment $record): bool => $record->status === CommentStatus::Rejected || filled($record->deleted_at)
            )
            ->requiresConfirmation()
            ->modalHeading('Reject comment')
            ->modalDescription('Are you sure you want to reject this comment?')
            ->action(function (Comment $record): void {
                $record->reject();
            })
            ->successNotificationTitle('Comment rejected.');
    }

    protected function markAsSpamAction(): Action
    {
        return Action::make('markAsSpam')
            ->label('Mark as Spam')
            ->icon(Heroicon::ShieldExclamation)
            ->color('warning')
            ->hidden(
                fn (Comment $record): bool => $record->status === CommentStatus::Spam || filled($record->deleted_at)
            )
            ->requiresConfirmation()
            ->modalHeading('Mark as spam')
            ->modalDescription('Are you sure you want to mark this comment as spam?')
            ->action(function (Comment $record): void {
                $record->markAsSpam();
            })
            ->successNotificationTitle('Comment marked as spam.');
    }
}
