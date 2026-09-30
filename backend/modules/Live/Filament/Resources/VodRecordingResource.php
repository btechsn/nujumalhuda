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

    protected static ?string $navigationGroup = 'Live Streaming';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'VOD Recordings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Recording Information')
                    ->schema([
                        Forms\Components\Select::make('stream_id')
                            ->relationship('stream', 'id')
                            ->required()
                            ->disabled()
                            ->helperText('Recordings are automatically created from ended streams'),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly identifier'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'processing' => 'Processing',
                                'ready' => 'Ready',
                                'failed' => 'Failed',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Translations')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('title.fr')
                                    ->label('Title (French)')
                                    ->required(),

                                Forms\Components\TextInput::make('title.en')
                                    ->label('Title (English)'),

                                Forms\Components\TextInput::make('title.ar')
                                    ->label('Title (Arabic)'),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\RichEditor::make('description.fr')
                                    ->label('Description (French)')
                                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList']),

                                Forms\Components\RichEditor::make('description.en')
                                    ->label('Description (English)')
                                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList']),

                                Forms\Components\RichEditor::make('description.ar')
                                    ->label('Description (Arabic)')
                                    ->toolbarButtons(['bold', 'italic', 'link', 'bulletList']),
                            ]),
                    ]),

                Forms\Components\Section::make('Video Files')
                    ->schema([
                        Forms\Components\TextInput::make('hls_url')
                            ->label('HLS URL')
                            ->url()
                            ->required(),

                        Forms\Components\TextInput::make('mp4_url')
                            ->label('MP4 URL (Optional)')
                            ->url(),

                        Forms\Components\FileUpload::make('thumbnail_url')
                            ->label('Thumbnail')
                            ->image()
                            ->directory('vod/thumbnails')
                            ->visibility('public'),
                    ])->columns(2),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_public')
                            ->label('Public')
                            ->default(true),

                        Forms\Components\Toggle::make('is_downloadable')
                            ->label('Downloadable')
                            ->default(false),

                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->timezone('Africa/Dakar'),
                    ])->columns(3),

                Forms\Components\Section::make('Technical Details')
                    ->schema([
                        Forms\Components\TextInput::make('duration_seconds')
                            ->label('Duration (seconds)')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('file_size_bytes')
                            ->label('File Size (bytes)')
                            ->numeric(),

                        Forms\Components\TextInput::make('resolution')
                            ->label('Resolution')
                            ->helperText('e.g., 1080p, 720p'),

                        Forms\Components\TextInput::make('bitrate')
                            ->label('Bitrate (kbps)')
                            ->numeric(),
                    ])->columns(4),

                Forms\Components\Section::make('Chapters')
                    ->schema([
                        Forms\Components\KeyValue::make('chapters')
                            ->label('Video Chapters')
                            ->keyLabel('Time (seconds)')
                            ->valueLabel('Chapter Title')
                            ->helperText('Add chapters for navigation (e.g., 0: Introduction, 300: Main Topic)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_url')
                    ->label('Thumbnail')
                    ->square()
                    ->size(60),

                Tables\Columns\TextColumn::make('title.fr')
                    ->label('Title')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('stream.channel.slug')
                    ->label('Channel')
                    ->badge(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'processing',
                        'success' => 'ready',
                        'danger' => 'failed',
                    ]),

                Tables\Columns\TextColumn::make('duration_formatted')
                    ->label('Duration')
                    ->getStateUsing(fn ($record) => $record->getFormattedDuration()),

                Tables\Columns\TextColumn::make('file_size_formatted')
                    ->label('Size')
                    ->getStateUsing(fn ($record) => $record->getFormattedFileSize()),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean(),

                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'processing' => 'Processing',
                        'ready' => 'Ready',
                        'failed' => 'Failed',
                    ]),

                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Public'),

                Tables\Filters\TernaryFilter::make('is_downloadable')
                    ->label('Downloadable'),

                Tables\Filters\Filter::make('published_at')
                    ->form([
                        Forms\Components\DatePicker::make('published_from'),
                        Forms\Components\DatePicker::make('published_until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['published_from'], fn ($q, $date) => $q->whereDate('published_at', '>=', $date))
                            ->when($data['published_until'], fn ($q, $date) => $q->whereDate('published_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('View')
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
