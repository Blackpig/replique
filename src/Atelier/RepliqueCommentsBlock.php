<?php

namespace BlackpigCreatif\Replique\Atelier;

use BlackpigCreatif\Atelier\Abstracts\BaseBlock;
use BlackpigCreatif\Replique\Enums\TextMode;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Contracts\View\View;

class RepliqueCommentsBlock extends BaseBlock
{
    public static function getLabel(): string
    {
        return 'Comments (Réplique)';
    }

    public static function getDescription(): ?string
    {
        return 'Add a comments section to your page.';
    }

    public static function getIcon(): string
    {
        return 'heroicon-o-chat-bubble-left-right';
    }

    public static function getSchema(): array
    {
        return [
            static::getPublishedField(),

            TextInput::make('title')
                ->label('Section heading')
                ->default('Comments')
                ->maxLength(100),

            Toggle::make('allow_anonymous')
                ->label('Allow anonymous comments')
                ->default(fn (): bool => (bool) config('replique.allow_anonymous', true)),

            Toggle::make('require_auth')
                ->label('Require login to comment')
                ->default(fn (): bool => (bool) config('replique.require_auth', false)),

            Select::make('nesting_depth')
                ->label('Reply depth')
                ->options([
                    '0'         => 'Flat (no replies)',
                    '1'         => 'One level of replies',
                    'unlimited' => 'Unlimited depth',
                ])
                ->default(fn (): string => config('replique.nesting_depth') === null ? 'unlimited' : (string) config('replique.nesting_depth', '1'))
                ->selectablePlaceholder(false),

            Select::make('text_mode')
                ->label('Text mode')
                ->options(TextMode::class)
                ->default(fn (): string => config('replique.text_mode', 'escaped_html'))
                ->selectablePlaceholder(false),

            CheckboxList::make('reaction_types')
                ->label('Reaction types')
                ->options(
                    fn (): array => collect(config('replique.reaction_types', []))
                        ->mapWithKeys(fn (string $type): array => [$type => ucfirst($type)])
                        ->toArray()
                )
                ->default(fn (): array => config('replique.reaction_types', [])),

            Toggle::make('require_approval')
                ->label('Require approval before display')
                ->default(fn (): bool => (bool) config('replique.require_approval', false)),

            ...static::getCommonOptionsSchema(),
        ];
    }

    public static function getViewPath(): string
    {
        return 'replique::atelier.comments-block';
    }

    public function render(): View
    {
        $model = $this->resolveModel();

        if (! $model) {
            logger()->warning('Réplique block: could not resolve commentable model.', $this->data);
        }

        if ($model && ! $this->isFirstBlockForModel()) {
            return view('replique::atelier.comments-block-duplicate');
        }

        return view(static::getViewPath(), array_merge($this->getViewData(), [
            'commentable' => $model,
        ]));
    }

    private function resolveModel(): ?object
    {
        return \BlackpigCreatif\Atelier\Models\AtelierBlock::find($this->blockId)?->blockable;
    }

    private function isFirstBlockForModel(): bool
    {
        $current = \BlackpigCreatif\Atelier\Models\AtelierBlock::find($this->blockId);

        if (! $current) {
            return true;
        }

        $firstId = \BlackpigCreatif\Atelier\Models\AtelierBlock::where('block_type', static::class)
            ->where('blockable_type', $current->blockable_type)
            ->where('blockable_id', $current->blockable_id)
            ->orderBy('id')
            ->value('id');

        return $firstId === $this->blockId;
    }
}
