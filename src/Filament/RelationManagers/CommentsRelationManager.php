<?php

namespace BlackpigCreatif\Replique\Filament\RelationManagers;

use BlackpigCreatif\Replique\Enums\CommentStatus;
use BlackpigCreatif\Replique\Enums\TextMode;
use BlackpigCreatif\Replique\Events\CommentPosted;
use BlackpigCreatif\Replique\Filament\Concerns\HasCommentAncestry;
use BlackpigCreatif\Replique\Filament\Concerns\HasCommentModerationActions;
use BlackpigCreatif\Replique\Models\Comment;
use BlackpigCreatif\Replique\Sanitisers\InjectionSanitiser;
use BlackpigCreatif\Replique\Sanitisers\TextSanitiser;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CommentsRelationManager extends RelationManager
{
    use HasCommentAncestry;
    use HasCommentModerationActions;

    protected static string $relationship = 'comments';

    protected static ?string $title = 'Comments';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            static::ancestrySection(),

            Textarea::make('original_text')
                ->label('Comment text')
                ->required()
                ->maxLength(10000)
                ->rows(5),

            Select::make('status')
                ->options(CommentStatus::class)
                ->required(),

            Toggle::make('is_pinned')
                ->label('Pinned'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('text')
            ->defaultSort('created_at', 'asc')
            ->modifyQueryUsing(fn ($query) => $query->withTrashed())
            ->filters([
                TrashedFilter::make(),
            ])
            ->columns([
                TextColumn::make('in_reply_to')
                    ->label('Depth')
                    ->state(
                        fn (Comment $record): string => $record->parent_id ? '↩ Reply' : 'Root'
                    )
                    ->badge()
                    ->color(
                        fn (Comment $record): string => $record->parent_id ? 'primary' : 'gray'
                    ),

                TextColumn::make('commentator')
                    ->label('From')
                    ->state(fn (Comment $record): string => self::buildCommentatorLabel($record)),

                TextColumn::make('text')
                    ->label('Comment')
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (CommentStatus $state): string | array | null => $state->getColor()),

                TextColumn::make('reactions_summary')
                    ->label('Reactions')
                    ->state(fn (Comment $record): string => self::buildReactionSummary($record)),

                IconColumn::make('is_pinned')
                    ->boolean()
                    ->label('Pinned'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->actions([
                ActionGroup::make([
                    self::replyInRelationManager(),

                    $this->approveAction(),
                    $this->rejectAction(),
                    $this->markAsSpamAction(),

                    EditAction::make()
                        ->using(
                            fn (Comment $record, array $data): Comment => self::saveEdit($record, $data)
                        ),

                    RestoreAction::make()
                        ->visible(fn (Comment $record): bool => filled($record->deleted_at))
                        ->successNotificationTitle('Comment restored.'),

                    DeleteAction::make(),
                ])->icon(Heroicon::EllipsisVertical),
            ]);
    }

    private static function replyInRelationManager(): Action
    {
        return Action::make('reply')
            ->label('Reply')
            ->icon(Heroicon::ChatBubbleLeftEllipsis)
            ->color('primary')
            ->hidden(fn (Comment $record): bool => filled($record->deleted_at))
            ->form([
                Hidden::make('parent_id'),

                Textarea::make('original_text')
                    ->label('Reply text')
                    ->required()
                    ->maxLength(10000)
                    ->rows(5),

                Select::make('text_mode')
                    ->label('Text mode')
                    ->options(TextMode::class)
                    ->required()
                    ->default(fn (): string => config('replique.text_mode', 'escaped_html')),
            ])
            ->fillForm(fn (Comment $record): array => ['parent_id' => $record->id])
            ->modalHeading('Post a reply')
            ->modalSubmitActionLabel('Post reply')
            ->action(function (Comment $record, array $data): void {
                $mode = $data['text_mode'] instanceof TextMode ? $data['text_mode'] : TextMode::from($data['text_mode']);
                $sanitiser = new TextSanitiser(new InjectionSanitiser);
                $user = auth()->user();

                $reply = new Comment([
                    'commentable_type' => $record->commentable_type,
                    'commentable_id' => $record->commentable_id,
                    'commentator_type' => $user?->getMorphClass(),
                    'commentator_id' => $user?->getKey(),
                    'parent_id' => $record->id,
                    'depth' => $record->depth + 1,
                    'original_text' => $data['original_text'],
                    'text' => $sanitiser->process($data['original_text'], $mode),
                    'text_mode' => $mode,
                    'status' => CommentStatus::Approved,
                    'approved_at' => now(),
                    'approved_by' => $user?->getKey(),
                    'ip_address' => request()->ip(),
                ]);

                $reply->save();

                CommentPosted::dispatch($reply);
            })
            ->successNotificationTitle('Reply posted.');
    }

    private static function saveEdit(Comment $record, array $data): Comment
    {
        $mode = TextMode::from($data['text_mode'] ?? $record->text_mode->value);
        $sanitiser = new TextSanitiser(new InjectionSanitiser);

        $record->original_text = $data['original_text'];
        $record->text = $sanitiser->process($data['original_text'], $mode);
        $record->status = CommentStatus::from($data['status']);
        $record->is_pinned = (bool) ($data['is_pinned'] ?? false);

        if ($record->status === CommentStatus::Approved && $record->approved_at === null) {
            $record->approved_at = now();
            $record->approved_by = auth()->id();
        }

        $record->save();

        return $record;
    }

    private static function buildCommentatorLabel(Comment $record): string
    {
        if ($record->commentator) {
            return $record->commentator->name ?? $record->commentator->email ?? 'User #' . $record->commentator_id;
        }

        $label = 'Anonymous';

        if ($record->anonymous_name) {
            $label .= ' (' . $record->anonymous_name . ')';
        }

        return $label;
    }

    private static function buildReactionSummary(Comment $record): string
    {
        $types = config('replique.reaction_types', []);

        if (empty($types)) {
            return '—';
        }

        return collect($types)
            ->map(
                fn (string $type): string => $type . ': ' . $record->reactions()->where('type', $type)->count()
            )
            ->join(' · ');
    }
}
