<?php

declare(strict_types=1);

namespace BlackpigCreatif\Replique\Filament\Pages;

use BlackpigCreatif\Grimoire\Filament\Pages\GrimoireChapterPage;
use BlackpigCreatif\Replique\Filament\Clusters\RepliqueDocumentationCluster;

final class RepliqueDocumentationPinningPage extends GrimoireChapterPage
{
    public static string $tomeId = 'replique';

    public static string $chapterSlug = 'pinning';

    protected static ?string $cluster = RepliqueDocumentationCluster::class;
}
