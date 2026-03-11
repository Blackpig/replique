<?php

declare(strict_types=1);

namespace BlackpigCreatif\Replique\Filament\Pages;

use BlackpigCreatif\Grimoire\Filament\Pages\GrimoireChapterPage;
use BlackpigCreatif\Replique\Filament\Clusters\RepliqueDocumentationCluster;

final class RepliqueDocumentationIpBlockingPage extends GrimoireChapterPage
{
    public static string $tomeId = 'replique';
    public static string $chapterSlug = 'ip-blocking';
    protected static ?string $cluster = RepliqueDocumentationCluster::class;
}
