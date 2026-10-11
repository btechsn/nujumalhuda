<?php

namespace Modules\Live\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Live\Filament\Resources\VodRecordingResource\Pages;
use Modules\Live\Models\VodRecording;

class VodRecordingResource extends Resource
{
    protected static ?string $model = VodRecording::class;

    protected static ?string $navigationIcon = 'heroicon-o-video-camera';

    protected static ?string $navigationGroup = 'Direct';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Enregistrements';

    protected static ?string $modelLabel = 'enregistrement';

    protected static ?string $pluralModelLabel = 'Enregistrements';

    protected static bool $shouldRegisterNavigation = false;

    public static function canViewAny(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('L’enregistrement')
                    ->schema([
                        Forms\Components\Select::make('stream_id')
                            ->label('Séance')
                            ->relationship('stream', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record): string => $record->title['fr'] ?? (string) $record->id)
                            ->required()
                            ->disabled()
                            ->helperText('L’enregistrement est créé automatiquement quand une séance se termine.'),

                        Forms\Components\TextInput::make('slug')
                            ->label('Identifiant')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('Texte utilisé dans l’adresse de la page.'),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options(self::statusOptions())
                            ->required(),
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

                Forms\Components\Section::make('Fichiers vidéo')
                    ->schema([
                        Forms\Components\TextInput::make('hls_url')
                            ->label('Adresse HLS')
                            ->url()
                            ->required(),

                        Forms\Components\TextInput::make('mp4_url')
                            ->label('Adresse MP4 (facultative)')
                            ->url(),

                        Forms\Components\FileUpload::make('thumbnail_url')
                            ->label('Image')
                            ->image()
                            ->directory('vod/thumbnails')
                            ->visibility('public'),
                    ])->columns(2),

                Forms\Components\Section::make('Publication')
                    ->schema([
                        Forms\Components\Toggle::make('is_public')
                            ->label('Visible au public')
                            ->default(true),

                        Forms\Components\Toggle::make('is_downloadable')
                            ->label('Téléchargeable')
                            ->default(false),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Date de publication')
                            ->timezone('Africa/Dakar'),
                    ])->columns(3),

                Forms\Components\Section::make('Détails techniques')
                    ->schema([
                        Forms\Components\TextInput::make('duration_seconds')
                            ->label('Durée (secondes)')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('file_size_bytes')
                            ->label('Taille du fichier (octets)')
                            ->numeric(),

                        Forms\Components\TextInput::make('resolution')
                            ->label('Résolution')
                            ->helperText('Par exemple 1080p ou 720p.'),

                        Forms\Components\TextInput::make('bitrate')
                            ->label('Débit (kbit/s)')
                            ->numeric(),
                    ])->columns(4),

                Forms\Components\Section::make('Chapitres')
                    ->schema([
                        Forms\Components\KeyValue::make('chapters')
                            ->label('Chapitres de la vidéo')
                            ->keyLabel('Temps (secondes)')
                            ->valueLabel('Titre du chapitre')
                            ->helperText('Par exemple 0 pour l’introduction, 300 pour le sujet principal.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_url')
                    ->label('Image')
                    ->square()
                    ->size(60),

                Tables\Columns\TextColumn::make('title.fr')
                    ->label('Titre')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('stream.channel.name')
                    ->label('Canal')
                    ->formatStateUsing(fn ($state): string => is_array($state) ? ($state['fr'] ?? '') : (string) $state)
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => self::statusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'processing' => 'warning',
                        'ready' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('duration_formatted')
                    ->label('Durée')
                    ->getStateUsing(fn ($record) => $record->getFormattedDuration()),

                Tables\Columns\TextColumn::make('file_size_formatted')
                    ->label('Taille')
                    ->getStateUsing(fn ($record) => $record->getFormattedFileSize()),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Vues')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Publié le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options(self::statusOptions()),

                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Public'),

                Tables\Filters\TernaryFilter::make('is_downloadable')
                    ->label('Téléchargeable'),

                Tables\Filters\Filter::make('published_at')
                    ->form([
                        Forms\Components\DatePicker::make('published_from')->label('Publié à partir du'),
                        Forms\Components\DatePicker::make('published_until')->label('Publié jusqu’au'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['published_from'], fn ($q, $date) => $q->whereDate('published_at', '>=', $date))
                            ->when($data['published_until'], fn ($q, $date) => $q->whereDate('published_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Voir')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => $record->hls_url)
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('published_at', 'desc');
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'processing' => 'En traitement',
            'ready' => 'Prêt',
            'failed' => 'Échec',
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
            'index' => Pages\ListVodRecordings::route('/'),
            'edit' => Pages\EditVodRecording::route('/{record}/edit'),
        ];
    }
}
