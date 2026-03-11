<?php

namespace BlackpigCreatif\Replique;

use BlackpigCreatif\Replique\Filament\Clusters\RepliqueDocumentationCluster;
use BlackpigCreatif\Replique\Filament\Resources\CommentResource;
use BlackpigCreatif\Replique\Filament\Widgets\PendingCommentsWidget;
use Filament\Contracts\Plugin;
use Filament\Panel;

class RepliquePlugin implements Plugin
{
    private string $navigationGroup;

    private bool $withWidget = false;

    public function __construct()
    {
        $this->navigationGroup = config('replique.filament_navigation_group', 'Comments');
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function getId(): string
    {
        return 'replique';
    }

    public function navigationGroup(string $group): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function getNavigationGroup(): string
    {
        return $this->navigationGroup;
    }

    public function withDashboardWidget(bool $enabled = true): static
    {
        $this->withWidget = $enabled;

        return $this;
    }

    public function isWithWidget(): bool
    {
        return $this->withWidget;
    }

    public function register(Panel $panel): void
    {
        $panel->resources([CommentResource::class]);

        if ($this->withWidget) {
            $panel->widgets([PendingCommentsWidget::class]);
        }

        if (class_exists(\BlackpigCreatif\Grimoire\Facades\Grimoire::class)) {
            \BlackpigCreatif\Grimoire\Facades\Grimoire::registerTome(
                id: 'replique',
                label: 'Comments',
                icon: 'heroicon-o-chat-bubble-left-right',
                path: dirname(__DIR__) . '/resources/grimoire/replique',
                clusterClass: RepliqueDocumentationCluster::class,
                slug: 'replique',
            );

            $panel->discoverClusters(
                in: __DIR__ . '/Filament/Clusters',
                for: 'BlackpigCreatif\\Replique\\Filament\\Clusters',
            );

            $panel->discoverPages(
                in: __DIR__ . '/Filament/Pages',
                for: 'BlackpigCreatif\\Replique\\Filament\\Pages',
            );
        }
    }

    public function boot(Panel $panel): void
    {
    }
}
