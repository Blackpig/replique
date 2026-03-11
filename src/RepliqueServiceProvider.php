<?php

namespace BlackpigCreatif\Replique;

use BlackpigCreatif\Replique\Commands\DiscoverCommentablesCommand;
use BlackpigCreatif\Replique\Events\CommentPosted;
use BlackpigCreatif\Replique\Listeners\SendNewCommentNotification;
use BlackpigCreatif\Replique\Registry\CommentableRegistry;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class RepliqueServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('replique')
            ->hasConfigFile()
            ->hasMigrations([
                '2024_01_01_000001_create_replique_comments_table',
                '2024_01_01_000002_create_replique_reactions_table',
                '2024_01_01_000003_create_replique_blocked_ips_table',
            ])
            ->hasViews('replique')
            ->hasCommand(DiscoverCommentablesCommand::class);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(Replique::class);
        $this->app->singleton(CommentableRegistry::class);
    }

    public function packageBooted(): void
    {
        $this->callAfterResolving('livewire', function (): void {
            Livewire::addNamespace('replique', classNamespace: 'BlackpigCreatif\\Replique\\Livewire');
        });

        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'replique');

        /** @var CommentableRegistry $registry */
        $registry = $this->app->make(CommentableRegistry::class);
        $registry->boot();

        Event::listen(CommentPosted::class, SendNewCommentNotification::class);

        Event::listen(CommandStarting::class, function (CommandStarting $event) use ($registry): void {
            if ($event->command === 'optimize:clear') {
                $registry->forget();
            }
        });

        if (class_exists(\BlackpigCreatif\Atelier\AtelierServiceProvider::class)) {
            $this->registerAtelierBlock();
        }
    }

    public function registerAtelierBlock(): void
    {
        config([
            'atelier.blocks' => array_merge(
                config('atelier.blocks', []),
                [\BlackpigCreatif\Replique\Atelier\RepliqueCommentsBlock::class],
            ),
        ]);
    }
}
