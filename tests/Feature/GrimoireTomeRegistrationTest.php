<?php

use BlackpigCreatif\Grimoire\Services\TomeRegistry;
use BlackpigCreatif\Replique\Filament\Clusters\RepliqueDocumentationCluster;
use BlackpigCreatif\Replique\RepliquePlugin;
use Filament\Panel;

it('registers the Replique documentation Tome when the package boots', function () {
    expect(app(TomeRegistry::class)->find('replique')?->clusterClass)->toBe(RepliqueDocumentationCluster::class);
});

it('can be added to several panels without registering the Tome twice', function () {
    $first = Panel::make()->id('first')->plugin(RepliquePlugin::make());
    $second = Panel::make()->id('second')->plugin(RepliquePlugin::make());

    expect($first->getPages())->toContain(RepliqueDocumentationCluster::class)
        ->and($second->getPages())->toContain(RepliqueDocumentationCluster::class);
});
