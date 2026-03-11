<?php

namespace BlackpigCreatif\Replique\Commands;

use BlackpigCreatif\Replique\Registry\CommentableRegistry;
use Illuminate\Console\Command;

class DiscoverCommentablesCommand extends Command
{
    protected $signature = 'replique:discover';

    protected $description = 'Discover and cache all models using the #[Commentable] attribute';

    public function handle(CommentableRegistry $registry): int
    {
        $registry->forget();
        $registry->boot();

        $models = $registry->all();

        if (empty($models)) {
            $this->info('No #[Commentable] models discovered.');

            return self::SUCCESS;
        }

        $this->info('Discovered ' . count($models) . ' commentable model(s):');

        foreach ($models as $class => $label) {
            $this->line("  {$label} ({$class})");
        }

        return self::SUCCESS;
    }
}
