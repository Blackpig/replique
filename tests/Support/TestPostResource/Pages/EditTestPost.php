<?php

namespace BlackpigCreatif\Replique\Tests\Support\TestPostResource\Pages;

use BlackpigCreatif\Replique\Tests\Support\TestPostResource;
use Filament\Resources\Pages\EditRecord;

class EditTestPost extends EditRecord
{
    protected static string $resource = TestPostResource::class;
}
