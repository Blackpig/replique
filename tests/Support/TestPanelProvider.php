<?php

namespace BlackpigCreatif\Replique\Tests\Support;

use BlackpigCreatif\Replique\RepliquePlugin;
use Filament\Panel;
use Filament\PanelProvider;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('test')
            ->path('test')
            ->plugin(RepliquePlugin::make())
            ->resources([TestPostResource::class]);
    }
}
