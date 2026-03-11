<?php

namespace BlackpigCreatif\Replique\Tests\Support;

use BackedEnum;
use BlackpigCreatif\Replique\Filament\RelationManagers\CommentsRelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TestPostResource extends Resource
{
    protected static ?string $model = TestPost::class;

    protected static BackedEnum | string | null $navigationIcon = Heroicon::Document;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getRelations(): array
    {
        return [
            CommentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => TestPostResource\Pages\ListTestPosts::route('/'),
            'edit' => TestPostResource\Pages\EditTestPost::route('/{record}/edit'),
        ];
    }
}
