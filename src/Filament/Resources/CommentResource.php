<?php

namespace BlackpigCreatif\Replique\Filament\Resources;

use BackedEnum;
use BlackpigCreatif\Replique\Enums\CommentStatus;
use BlackpigCreatif\Replique\Enums\TextMode;
use BlackpigCreatif\Replique\Events\CommentPosted;
use BlackpigCreatif\Replique\Facades\Replique;
use BlackpigCreatif\Replique\Filament\Concerns\HasCommentAncestry;
use BlackpigCreatif\Replique\Filament\Resources\CommentResource\Pages\ListComments;
use BlackpigCreatif\Replique\Models\Comment;
use BlackpigCreatif\Replique\Registry\CommentableRegistry;
use BlackpigCreatif\Replique\RepliquePlugin;
use BlackpigCreatif\Replique\Sanitisers\InjectionSanitiser;
use BlackpigCreatif\Replique\Sanitisers\TextSanitiser;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use UnitEnum;

class CommentResource extends Resource
{
    use HasCommentAncestry;

    protected static ?string $model = Comment::class;

    protected static ?string $recordTitleAttribute = 'text';

    protected static BackedEnum | string | null $navigationIcon = Heroicon::ChatBubbleLeftRight;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return RepliquePlugin::get()->getNavigationGroup();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['text', 'anonymous_email', 'anonymous_name', 'ip_address'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            static::ancestrySection(),

            Section::make('Comment Text')
                ->columnSpanFull()
                ->schema([
                    Textarea::make('original_text')
                        ->label('Comment text (editable)')
                        ->required()
                        ->maxLength(10000)
                        ->rows(6)
                        ->helperText('Editing will reprocess the sanitised output on save.'),

                    Select::make('text_mode')
                        ->label('Text mode')
                        ->options(TextMode::class)
                        ->required(),
                ]),

            Section::make('Moderation')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->options(CommentStatus::class)
                        ->required(),

                    Toggle::make('is_pinned')
                        ->label('Pinned')
                        ->inline(false),
                ]),

            Section::make('Attribution')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextInput::make('commentable_type')
                        ->label('Model type')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('commentable_id')
                        ->label('Model ID')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('anonymous_email')
                        ->label('Anonymous email')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('anonymous_name')
                        ->label('Anonymous name')
                        ->disabled()
                        ->dehydrated(false),

                    TextInput::make('ip_address')
                        ->label('IP address')
                        ->disabled()
                        ->dehydrated(false),
                ]),
        ]);
    }

    public static function createForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Comment')
                ->columnSpanFull()
                ->columns(1)
                ->schema([
                    Select::make('commentable_type')
                        ->label('Model type')
                        ->options(fn (): array => self::commentableOptions())
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('commentable_id', null)),

                    TextInput::make('commentable_id')
                        ->label('Model ID (numeric)')
                        ->integer()
                        ->required()
                        ->minValue(1)
                        ->helperText('Enter the ID of the specific record to attach this comment to.'),

                    Textarea::make('original_text')
                        ->label('Comment text')
                        ->required()
                        ->maxLength(10000)
                        ->rows(6),

                    Select::make('text_mode')
                        ->label('Text mode')
                        ->options(TextMode::class)
                        ->required()
                        ->default(fn (): string => config('replique.text_mode', 'escaped_html')),
                ]),

            Section::make('Moderation')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->options(CommentStatus::class)
                        ->required()
                        ->default(CommentStatus::Approved->value),

                    Toggle::make('is_pinned')
                        ->label('Pinned')
                        ->inline(false)
                        ->default(false),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            static::ancestrySection(),

            Section::make('Comment')
                ->columnSpanFull()
                ->columns(1)
                ->schema([
                    TextEntry::make('text')
                        ->label('Processed output')
                        ->html()
                        ->columnSpanFull(),

                    TextEntry::make('original_text')
                        ->label('Original input')
                        ->columnSpanFull(),

                    TextEntry::make('text_mode')
                        ->label('Text mode')
                        ->formatStateUsing(fn (TextMode $state): string => $state->getLabel()),
                ]),

            Section::make('Moderation')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn (CommentStatus $state): string | array | null => $state->getColor()),

                    IconEntry::make('is_pinned')
                        ->boolean()
                        ->label('Pinned'),

                    TextEntry::make('approved_at')
                        ->label('Approved at')
                        ->dateTime()
                        ->placeholder('—'),

                    TextEntry::make('approved_by')
                        ->label('Approved by')
                        ->placeholder('—'),
                ]),

            Section::make('Attribution')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextEntry::make('commentable_type')
                        ->label('Model type'),

                    TextEntry::make('commentable_id')
                        ->label('Model ID'),

                    TextEntry::make('parent_id')
                        ->label('Reply to comment #')
                        ->placeholder('Top-level comment'),

                    TextEntry::make('anonymous_email')
                        ->label('Anonymous email')
                        ->placeholder('—'),

                    TextEntry::make('anonymous_name')
                        ->label('Anonymous name')
                        ->placeholder('—'),

                    TextEntry::make('ip_address')
                        ->label('IP address')
                        ->copyable()
                        ->placeholder('—'),

                    TextEntry::make('created_at')
                        ->label('Posted')
                        ->dateTime(),

                    TextEntry::make('reactions_summary')
                        ->label('Reactions')
                        ->state(fn (Comment $record): string => self::buildReactionSummary($record)),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('parent'))
            ->groups([
                Group::make('commentable_type')
                    ->label('Record')
                    ->getKeyFromRecordUsing(
                        fn (Comment $record): string => $record->commentable_type . ':' . $record->commentable_id
                    )
                    ->getTitleFromRecordUsing(
                        fn (Comment $record): string => class_basename($record->commentable_type) . ' #' . $record->commentable_id
                    )
                    ->collapsible(),
            ])
            ->defaultGroup('commentable_type')
            ->columns([
                TextColumn::make('commentable')
                    ->label('On')
                    ->state(
                        fn (Comment $record): string => class_basename($record->commentable_type) . ' #' . $record->commentable_id
                    )
                    ->searchable(
                        query: fn (Builder $query, string $search): Builder => $query->where('commentable_type', 'like', "%{$search}%")
                    ),

                TextColumn::make('in_reply_to')
                    ->label('Reply to')
                    ->state(
                        fn (Comment $record): string => $record->parent_id ? '↩ #' . $record->parent_id : '—'
                    )
                    ->color(fn (Comment $record): string => $record->parent_id ? 'primary' : 'gray')
                    ->tooltip(
                        fn (Comment $record): ?string => $record->parent_id
                        ? Str::limit(strip_tags($record->parent?->text ?? ''), 100)
                        : null
                    ),

                TextColumn::make('commentator')
                    ->label('From')
                    ->state(fn (Comment $record): string => self::buildCommentatorLabel($record))
                    ->searchable(
                        query: fn (Builder $query, string $search): Builder => $query->where('anonymous_email', 'like', "%{$search}%")
                            ->orWhere('anonymous_name', 'like', "%{$search}%")
                    ),

                TextColumn::make('text')
                    ->label('Comment')
                    ->limit(80)
                    ->tooltip(fn (Comment $record): string => $record->text)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (CommentStatus $state): string | array | null => $state->getColor())
                    ->formatStateUsing(fn (CommentStatus $state): string => $state->getLabel())
                    ->sortable(),

                TextColumn::make('reactions_summary')
                    ->label('Reactions')
                    ->state(fn (Comment $record): string => self::buildReactionSummary($record))
                    ->alignCenter()
                    ->tooltip('Per-type reaction counts'),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Posted')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CommentStatus::class)
                    ->multiple()
                    ->label('Status'),

                SelectFilter::make('commentable_type')
                    ->label('Model type')
                    ->options(
                        fn (): array => Comment::query()
                            ->distinct()
                            ->pluck('commentable_type')
                            ->mapWithKeys(fn (string $type): array => [$type => class_basename($type)])
                            ->toArray()
                    )
                    ->query(
                        fn (Builder $query, array $data): Builder => $query->when(
                            $data['value'] ?? null,
                            fn (Builder $q): Builder => $q->where('commentable_type', $data['value'])
                        )
                    ),

                TernaryFilter::make('pending_only')
                    ->label('Pending only')
                    ->queries(
                        true: fn (Builder $q): Builder => $q->where('status', CommentStatus::Pending),
                        false: fn (Builder $q): Builder => $q->where('status', '!=', CommentStatus::Pending->value),
                        blank: fn (Builder $q): Builder => $q,
                    ),

                TrashedFilter::make(),

                Filter::make('created_at')
                    ->label('Date range')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('From date')
                            ->native(false),
                        DatePicker::make('created_until')
                            ->label('Until date')
                            ->native(false),
                    ])
                    ->query(
                        fn (Builder $query, array $data): Builder => $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn (Builder $q): Builder => $q->whereDate('created_at', '>=', $data['created_from'])
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn (Builder $q): Builder => $q->whereDate('created_at', '<=', $data['created_until'])
                            )
                    ),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),

                    EditAction::make()
                        ->mutateFormDataUsing(fn (array $data): array => $data)
                        ->using(fn (Comment $record, array $data): Comment => self::saveEdit($record, $data)),

                    self::replyRowAction(),

                    Action::make('approve')
                        ->label('Approve')
                        ->icon(Heroicon::CheckCircle)
                        ->color('success')
                        ->hidden(
                            fn (Comment $record): bool => $record->status === CommentStatus::Approved || filled($record->deleted_at)
                        )
                        ->action(fn (Comment $record) => $record->approve())
                        ->successNotificationTitle('Comment approved.'),

                    Action::make('reject')
                        ->label('Reject')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->hidden(
                            fn (Comment $record): bool => $record->status === CommentStatus::Rejected || filled($record->deleted_at)
                        )
                        ->requiresConfirmation()
                        ->action(fn (Comment $record) => $record->reject())
                        ->successNotificationTitle('Comment rejected.'),

                    Action::make('markAsSpam')
                        ->label('Mark as Spam')
                        ->icon(Heroicon::ShieldExclamation)
                        ->color('warning')
                        ->hidden(
                            fn (Comment $record): bool => $record->status === CommentStatus::Spam || filled($record->deleted_at)
                        )
                        ->requiresConfirmation()
                        ->action(fn (Comment $record) => $record->markAsSpam())
                        ->successNotificationTitle('Comment marked as spam.'),

                    self::blockIpAction(),

                    RestoreAction::make()
                        ->visible(fn (Comment $record): bool => filled($record->deleted_at))
                        ->successNotificationTitle('Comment restored.'),

                    DeleteAction::make(),
                ])->icon(Heroicon::EllipsisVertical),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('approveSelected')
                        ->label('Approve selected')
                        ->icon(Heroicon::CheckCircle)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->approve())
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Selected comments approved.'),

                    BulkAction::make('rejectSelected')
                        ->label('Reject selected')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->reject())
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Selected comments rejected.'),

                    BulkAction::make('spamSelected')
                        ->label('Mark as spam')
                        ->icon(Heroicon::ShieldExclamation)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->markAsSpam())
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Selected comments marked as spam.'),

                    RestoreBulkAction::make(),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function replyRowAction(): Action
    {
        return Action::make('reply')
            ->label('Reply')
            ->icon(Heroicon::ChatBubbleLeftEllipsis)
            ->color('primary')
            ->hidden(fn (Comment $record): bool => filled($record->deleted_at))
            ->form([
                Hidden::make('parent_id'),
                Hidden::make('commentable_type'),
                Hidden::make('commentable_id'),

                Textarea::make('original_text')
                    ->label('Your reply')
                    ->required()
                    ->maxLength(10000)
                    ->rows(5)
                    ->autofocus(),

                Select::make('text_mode')
                    ->label('Text mode')
                    ->options(TextMode::class)
                    ->required()
                    ->default(fn (): string => config('replique.text_mode', 'escaped_html')),
            ])
            ->fillForm(fn (Comment $record): array => [
                'parent_id' => $record->id,
                'commentable_type' => $record->commentable_type,
                'commentable_id' => $record->commentable_id,
            ])
            ->modalHeading('Post a reply')
            ->modalDescription('Reply will be posted as the currently authenticated admin user.')
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

    private static function blockIpAction(): Action
    {
        return Action::make('blockIp')
            ->label('Block IP')
            ->icon(Heroicon::NoSymbol)
            ->color('danger')
            ->hidden(fn (Comment $record): bool => blank($record->ip_address))
            ->form([
                TextInput::make('ip_address')
                    ->label('IP address')
                    ->disabled()
                    ->dehydrated(false),

                Textarea::make('reason')
                    ->label('Reason (optional)')
                    ->nullable()
                    ->maxLength(500)
                    ->rows(3),
            ])
            ->fillForm(fn (Comment $record): array => [
                'ip_address' => $record->ip_address,
            ])
            ->modalHeading('Block IP Address')
            ->modalDescription(
                fn (Comment $record): string => "Block {$record->ip_address} from posting further comments."
            )
            ->modalSubmitActionLabel('Block IP')
            ->action(function (Comment $record, array $data): void {
                Replique::blockIp(
                    $record->ip_address,
                    reason: $data['reason'] ?? null,
                );
            })
            ->successNotificationTitle(
                fn (Comment $record): string => "IP address {$record->ip_address} has been blocked."
            );
    }

    private static function saveEdit(Comment $record, array $data): Comment
    {
        $mode = $data['text_mode'] instanceof TextMode ? $data['text_mode'] : TextMode::from($data['text_mode']);
        $sanitiser = new TextSanitiser(new InjectionSanitiser);

        $record->original_text = $data['original_text'];
        $record->text = $sanitiser->process($data['original_text'], $mode);
        $record->text_mode = $mode;
        $record->status = $data['status'] instanceof CommentStatus ? $data['status'] : CommentStatus::from($data['status']);
        $record->is_pinned = (bool) ($data['is_pinned'] ?? false);

        if ($record->status === CommentStatus::Approved && $record->approved_at === null) {
            $record->approved_at = now();
            $record->approved_by = auth()->id();
        }

        $record->save();

        return $record;
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $textMode = $data['text_mode'] ?? config('replique.text_mode', 'escaped_html');
        $mode = $textMode instanceof TextMode ? $textMode : TextMode::from($textMode);
        $sanitiser = new TextSanitiser(new InjectionSanitiser);
        $user = auth()->user();

        $data['text'] = $sanitiser->process($data['original_text'], $mode);
        $data['commentator_type'] = $user?->getMorphClass();
        $data['commentator_id'] = $user?->getKey();
        $data['depth'] = 0;
        $data['ip_address'] = request()->ip();

        if (($data['status'] instanceof CommentStatus ? $data['status'] : CommentStatus::from($data['status'])) === CommentStatus::Approved) {
            $data['approved_at'] = now();
            $data['approved_by'] = $user?->getKey();
        }

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComments::route('/'),
        ];
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

    private static function commentableOptions(): array
    {
        $registry = app(CommentableRegistry::class);

        return collect(config('replique.commentable_models', []))
            ->merge($registry->all())
            ->toArray();
    }
}
