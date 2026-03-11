<?php

namespace BlackpigCreatif\Replique\Filament\Widgets;

use BlackpigCreatif\Replique\Enums\CommentStatus;
use BlackpigCreatif\Replique\Models\Comment;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingCommentsWidget extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        return [
            Stat::make('Pending Review', Comment::query()->where('status', CommentStatus::Pending)->count())
                ->description('Awaiting moderation')
                ->color('warning')
                ->icon(Heroicon::Clock),

            Stat::make(
                'Approved Today',
                Comment::query()
                    ->where('status', CommentStatus::Approved)
                    ->whereDate('approved_at', today())
                    ->count(),
            )
                ->description('Approved today')
                ->color('success')
                ->icon(Heroicon::CheckCircle),

            Stat::make('Spam Caught', Comment::query()->where('status', CommentStatus::Spam)->count())
                ->description('Total marked as spam')
                ->color('gray')
                ->icon(Heroicon::ShieldExclamation),
        ];
    }
}
