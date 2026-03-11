<?php

use BlackpigCreatif\Replique\Filament\Resources\CommentResource;
use BlackpigCreatif\Replique\Filament\Widgets\PendingCommentsWidget;
use BlackpigCreatif\Replique\RepliquePlugin;
use Filament\Facades\Filament;

it('registers the comment resource on the panel', function (): void {
    $panel = Filament::getDefaultPanel();

    expect($panel->getResources())->toContain(CommentResource::class);
});

it('does not register the widget by default', function (): void {
    $panel = Filament::getDefaultPanel();

    expect($panel->getWidgets())->not->toContain(PendingCommentsWidget::class);
});

it('registers the widget when withDashboardWidget is called', function (): void {
    $plugin = RepliquePlugin::make()->withDashboardWidget();

    expect($plugin->isWithWidget())->toBeTrue();
});

it('returns the navigation group', function (): void {
    $plugin = RepliquePlugin::get();

    expect($plugin->getNavigationGroup())->toBe('Comments');
});

it('navigationGroup can be customised', function (): void {
    $plugin = RepliquePlugin::make()->navigationGroup('Content');

    expect($plugin->getNavigationGroup())->toBe('Content');
});
