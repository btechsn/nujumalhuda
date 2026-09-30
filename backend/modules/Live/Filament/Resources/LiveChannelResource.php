<?php

namespace Modules\Live\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Live\Filament\Resources\LiveChannelResource\Pages;
use Modules\Live\Models\LiveChannel;

class LiveChannelResource extends Resource
{
    protected static ?string $model = LiveChannel::class;

    protected static ?string $navigationIcon = 'heroicon-o-tv';

    protected static ?string $navigationGroup = 'Live Streaming';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Channel Information')
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Select::make('type')
                            ->options([
                                'video' => 'Video',
                                'audio' => 'Audio Only',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('priority')
                            ->numeric()
                            ->default(0)
                            ->helperText('Higher priority channels appear first'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        Forms\Components\Toggle::make('requires_moderation')
                            ->label('Requires Chat Moderation')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Translations')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('name.fr')
                                    ->label('Name (French)')
                                    ->required(),

                                Forms\Components\TextInput::make('name.en')
                                    ->label('Name (English)'),

                                Forms\Components\TextInput::make('name.ar')
                                    ->label('Name (Arabic)'),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Textarea::make('description.fr')
                                    ->label('Description (French)')
                                    ->rows(3),

                                Forms\Components\Textarea::make('description.en')
                                    ->label('Description (English)')
                                    ->rows(3),

                                Forms\Components\Textarea::make('description.ar')
                                    ->label('Description (Arabic)')
                                    ->rows(3),
                            ]),
                    ]),

                Forms\Components\Section::make('Technical Configuration')
                    ->schema([
                        Forms\Components\TextInput::make('rtmp_ingest_url')
                            ->label('RTMP Ingest URL')
                            ->required()
                            ->url()
                            ->helperText('e.g., rtmp://ingest.nujumalhuda.com:1935/main'),

                        Forms\Components\TextInput::make('whep_url')
                            ->label('WHEP URL (WebRTC)')
                            ->required()
                            ->url()
                            ->helperText('e.g., https://stream.nujumalhuda.com/whep/main'),

                        Forms\Components\TextInput::make('hls_url')
                            ->label('HLS URL')
                            ->required()
                            ->url()
                            ->helperText('e.g., https://stream.nujumalhuda.com/hls/main'),

                        Forms\Components\TextInput::make('max_bitrate')
                            ->label('Max Bitrate (kbps)')
                            ->numeric()
                            ->helperText('Maximum allowed bitrate in kilobits per second'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name.fr')
                    ->label('Name')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'video',
                        'warning' => 'audio',
                    ]),

                Tables\Columns\TextColumn::make('streams_count')
                    ->counts('streams')
                    ->label('Total Streams'),

                Tables\Columns\TextColumn::make('active_streams_count')
                    ->counts('activeStreams')
                    ->label('Live Now')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('priority')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),

                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'video' => 'Video',
                        'audio' => 'Audio Only',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListLiveChannels::route('/'),
            'create' => Pages\CreateLiveChannel::route('/create'),
            'edit' => Pages\EditLiveChannel::route('/{record}/edit'),
        ];
    }
}
