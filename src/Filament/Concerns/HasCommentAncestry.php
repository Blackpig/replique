<?php

namespace BlackpigCreatif\Replique\Filament\Concerns;

use BlackpigCreatif\Replique\Models\Comment;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;

trait HasCommentAncestry
{
    public static function ancestrySection(): Section
    {
        return Section::make('In reply to')
            ->columnSpanFull()
            ->visible(fn (?Comment $record): bool => $record?->parent_id !== null)
            ->schema([
                View::make('replique::filament.comment-ancestry'),
            ]);
    }
}
