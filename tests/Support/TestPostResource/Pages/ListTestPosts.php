<?php

namespace BlackpigCreatif\Replique\Tests\Support\TestPostResource\Pages;

use BlackpigCreatif\Replique\Tests\Support\TestPostResource;
use Filament\Resources\Pages\ListRecords;

class ListTestPosts extends ListRecords
{
    protected static string $resource = TestPostResource::class;
}
