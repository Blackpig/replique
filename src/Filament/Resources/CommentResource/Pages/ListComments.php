<?php

namespace BlackpigCreatif\Replique\Filament\Resources\CommentResource\Pages;

use BlackpigCreatif\Replique\Events\CommentPosted;
use BlackpigCreatif\Replique\Filament\Resources\CommentResource;
use BlackpigCreatif\Replique\Models\Comment;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComments extends ListRecords
{
    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->form(fn ($form) => CommentResource::createForm($form))
                ->mutateFormDataUsing(
                    fn (array $data): array => CommentResource::mutateFormDataBeforeCreate($data)
                )
                ->after(function (Comment $record): void {
                    CommentPosted::dispatch($record);
                }),
        ];
    }
}
