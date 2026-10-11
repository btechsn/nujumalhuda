<?php

namespace Modules\Live\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Live\Filament\Resources\LiveStreamResource\Pages;
use Modules\Live\Models\LiveStream;

class LiveStreamResource extends Resource
{
    protected static ?string $model = LiveStream::class;

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationGroup = 'Direct';

    protected static ?string $navigationLabel = 'Séances en direct';

    protected static ?string $modelLabel = 'séance';

    protected static ?string $pluralModelLabel = 'Séances en direct';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('La séance')
                    ->schema([
                        Forms\Components\Select::make('channel_id')
                            ->label('Canal')
                            ->relationship('channel', 'slug')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => $record->getLocalizedName('fr') ?: (string) $record->slug)
                            ->required()
                            ->preload(),

                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options(self::typeOptions())
                            ->default('general')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(self::statusOptions())
                            ->default('scheduled')
                            ->helperText('Passez sur En direct au début. Le site passe tout seul sur Terminée quand YouTube coupe le direct. Vous pouvez aussi l’arrêter avec le bouton Terminer.'),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Début prévu')
                            ->seconds(false)
                            ->timezone('Africa/Dakar'),

                        Forms\Components\TextInput::make('external_url')
                            ->label('Lien du direct YouTube')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->helperText('Collez le lien YouTube. C’est cette vidéo que le site affiche. Aucun serveur de diffusion n’est nécessaire.'),
                    ])->columns(2),

                Forms\Components\Section::make('Titre affiché')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('title.fr')
                                    ->label('Titre (français)')
                                    ->required(),

                                Forms\Components\TextInput::make('title.en')
                                    ->label('Titre (anglais)'),

                                Forms\Components\TextInput::make('title.ar')
                                    ->label('Titre (arabe)'),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\RichEditor::make('description.fr')
                                    ->label('Description (français)')
                                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList']),

                                Forms\Components\RichEditor::make('description.en')
                                    ->label('Description (anglais)')
                                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList']),

                                Forms\Components\RichEditor::make('description.ar')
                                    ->label('Description (arabe)')
                                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList']),
                            ]),
                    ]),

                Forms\Components\Section::make('Options')
                    ->schema([
                        Forms\Components\Toggle::make('enable_chat')
                            ->label('Autoriser le chat')
                            ->default(true),

                        Forms\Components\Toggle::make('enable_reactions')
                            ->label('Autoriser les réactions')
                            ->default(true),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Mettre en avant')
                            ->helperText('La séance apparaît en évidence sur la page d’accueil.'),

                        Forms\Components\FileUpload::make('thumbnail_url')
                            ->label('Image')
                            ->image()
                            ->directory('streams/thumbnails')
                            ->visibility('public'),
                    ])->columns(2),

                Forms\Components\Section::make('Statistiques')
                    ->schema([
                        Forms\Components\Placeholder::make('peak_viewers')
                            ->label('Pic sur le site')
                            ->content(fn ($record) => (string) ($record?->peak_viewers ?? 0))
                            ->helperText('Personnes en même temps sur le site. YouTube ne transmet pas son propre pic.'),

                        Forms\Components\Placeholder::make('total_views')
                            ->label('Vues YouTube')
                            ->content(fn ($record) => (string) ($record?->total_views ?? 0))
                            ->helperText('Nombre de vues comptées par YouTube.'),

                        Forms\Components\Placeholder::make('chat_messages_count')
                            ->label('Messages du chat')
                            ->content(fn ($record) => (string) ($record?->chat_messages_count ?? 0))
                            ->helperText('Messages écrits dans le chat du site, pas celui de YouTube.'),

                        Forms\Components\Placeholder::make('duration_seconds')
                            ->label('Durée')
                            ->content(fn ($record) => self::formatDuration($record?->duration_seconds))
                            ->helperText('Durée du direct sur YouTube, une fois qu’il est terminé.'),
                    ])
                    ->columns(4)
                    ->visible(fn ($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title.fr')
                    ->label('Titre')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Canal')
                    ->formatStateUsing(fn ($state): string => is_array($state) ? ($state['fr'] ?? '') : (string) $state)
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'warning',
                        'live' => 'success',
                        'ended' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::typeOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'khutba' => 'success',
                        'recitation' => 'info',
                        'lecture' => 'warning',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Prévue le')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('En avant')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créée le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(self::statusOptions()),

                Tables\Filters\SelectFilter::make('channel_id')
                    ->label('Canal')
                    ->options(fn (): array => \Modules\Live\Models\LiveChannel::query()
                        ->orderByDesc('priority')
                        ->get()
                        ->mapWithKeys(fn ($channel): array => [$channel->id => $channel->getLocalizedName('fr') ?: $channel->slug])
                        ->all()),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options(self::typeOptions()),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('En avant'),
            ])
            ->actions([
                Tables\Actions\Action::make('end_live')
                    ->label('Terminer')
                    ->icon('heroicon-o-stop')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Terminer le direct')
                    ->modalDescription('La séance ne s’affichera plus comme étant en direct sur le site.')
                    ->visible(fn ($record) => $record->isLive())
                    ->action(function ($record) {
                        $record->markEnded();

                        Notification::make()
                            ->title('Direct terminé')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('view_stream')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.live-streams.view', $record))
                    ->visible(fn ($record) => $record->isLive()),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                if (!$record->isLive()) {
                                    $record->delete();
                                }
                            }

                            Notification::make()
                                ->title('Les séances en cours ne peuvent pas être supprimées')
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc');
    }

    public static function formatDuration(?int $seconds): string
    {
        if (! $seconds) {
            return 'Pas encore connue';
        }

        $minutes = intdiv($seconds, 60);
        $rest = $seconds % 60;

        if ($minutes === 0) {
            return $rest.' s';
        }

        return $rest > 0 ? "{$minutes} min {$rest} s" : "{$minutes} min";
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            'general' => 'Général',
            'khutba' => 'Khutba',
            'recitation' => 'Récitation',
            'lecture' => 'Cours',
            'event' => 'Événement',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'scheduled' => 'Planifiée',
            'live' => 'En direct',
            'ended' => 'Terminée',
            'archived' => 'Archivée',
        ];
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLiveStreams::route('/'),
            'create' => Pages\CreateLiveStream::route('/create'),
            'edit' => Pages\EditLiveStream::route('/{record}/edit'),
            'view' => Pages\ViewLiveStream::route('/{record}'),
        ];
    }
}
