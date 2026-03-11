<?php

declare(strict_types=1);

namespace BlackpigCreatif\Replique\Filament\Pages;

use BlackpigCreatif\Grimoire\Filament\Pages\GrimoireChapterPage;
use BlackpigCreatif\Replique\Filament\Clusters\RepliqueDocumentationCluster;

final class RepliqueDocumentationPostingRepliesPage extends GrimoireChapterPage
{
    public static string $tomeId = 'replique';
    public static string $chapterSlug = 'posting-replies';
    protected static ?string $cluster = RepliqueDocumentationCluster::class;
}
