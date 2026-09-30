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
use Modules\Live\Services\MediaMtxService;

class LiveStreamResource extends Resource
{
    protected static ?string $model = LiveStream::class;

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationGroup = 'Live Streaming';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Stream Information')
                    ->schema([
                        Forms\Components\Select::make('channel_id')
                            ->relationship('channel', 'slug')
                            ->required()
                            ->preload(),

                        Forms\Components\Select::make('type')
                            ->options([
                                'general' => 'General',
                                'khutba' => 'Khutba',
                                'recitation' => 'Recitation',
                                'lecture' => 'Lecture',
                                'event' => 'Event',
                            ])
                            ->default('general')
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'live' => 'Live',
                                'ended' => 'Ended',
                                'archived' => 'Archived',
                            ])
                            ->default('scheduled')
                            ->disabled(fn ($record) => $record?->isLive()),

                        Forms\Components\DateTimePicker::make('scheduled_at')
                            ->label('Scheduled Start Time')
                            ->seconds(false)
                            ->timezone('Africa/Dakar'),
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

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('enable_chat')
                            ->label('Enable Chat')
                            ->default(true),

                        Forms\Components\Toggle::make('enable_reactions')
                            ->label('Enable Reactions')
                            ->default(true),

                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured Stream')
                            ->helperText('Featured streams appear prominently on the homepage'),

                        Forms\Components\FileUpload::make('thumbnail_url')
                            ->label('Thumbnail')
                            ->image()
                            ->directory('streams/thumbnails')
                            ->visibility('public'),
                    ])->columns(2),

                Forms\Components\Section::make('Stream Key')
                    ->schema([
                        Forms\Components\TextInput::make('publish_key')
                            ->label('Publish Key')
                            ->disabled()
                            ->helperText('This key is generated automatically and can be rotated for security'),

                        Forms\Components\Placeholder::make('rtmp_url')
                            ->label('RTMP Ingest URL')
                            ->content(function ($record) {
                                return $record?->getRtmpIngestUrl() ?? 'Will be generated after save';
                            }),
                    ])
                    ->visible(fn ($record) => $record !== null),

                Forms\Components\Section::make('Statistics')
                    ->schema([
                        Forms\Components\TextInput::make('peak_viewers')
                            ->label('Peak Viewers')
                            ->disabled()
                            ->numeric(),

                        Forms\Components\TextInput::make('total_views')
                            ->label('Total Views')
                            ->disabled()
                            ->numeric(),

                        Forms\Components\TextInput::make('chat_messages_count')
                            ->label('Chat Messages')
                            ->disabled()
                            ->numeric(),

                        Forms\Components\TextInput::make('duration_seconds')
                            ->label('Duration (seconds)')
                            ->disabled()
                            ->numeric(),
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
                    ->label('Title')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('channel.slug')
                    ->label('Channel')
                    ->badge()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'scheduled',
                        'success' => 'live',
                        'danger' => 'ended',
                        'secondary' => 'archived',
                    ]),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'general',
                        'success' => 'khutba',
                        'info' => 'recitation',
                        'warning' => 'lecture',
                    ]),

                Tables\Columns\TextColumn::make('current_viewers')
                    ->label('Live Viewers')
                    ->getStateUsing(fn ($record) => $record->isLive() ? $record->getCurrentViewerCount() : '-')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('peak_viewers')
                    ->label('Peak')
                    ->sortable(),

                Tables\Columns\TextColumn::make('scheduled_at')
                    ->label('Scheduled')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'live' => 'Live',
                        'ended' => 'Ended',
                        'archived' => 'Archived',
                    ]),

                Tables\Filters\SelectFilter::make('channel_id')
                    ->relationship('channel', 'slug')
                    ->label('Channel'),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'general' => 'General',
                        'khutba' => 'Khutba',
                        'recitation' => 'Recitation',
                        'lecture' => 'Lecture',
                        'event' => 'Event',
                    ]),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label('Featured'),
            ])
            ->actions([
                Tables\Actions\Action::make('rotate_key')
                    ->label('Rotate Key')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->disabled(fn ($record) => $record->isLive())
                    ->action(function ($record) {
                        $service = app(MediaMtxService::class);
                        $newKey = $service->rotateStreamKey($record);

                        Notification::make()
                            ->title('Stream key rotated successfully')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('view_stream')
                    ->label('View')
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
                                ->title('Live streams cannot be deleted')
                                ->warning()
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('scheduled_at', 'desc');
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
