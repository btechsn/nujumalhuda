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
    public static function canViewAny(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('live_setup');
    }

    protected static ?string $model = LiveChannel::class;

    protected static ?string $navigationIcon = 'heroicon-o-tv';

    protected static ?string $navigationGroup = 'Direct';

    protected static ?string $navigationLabel = 'Canaux';

    protected static ?string $modelLabel = 'canal';

    protected static ?string $pluralModelLabel = 'Canaux';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Le canal')
                    ->description('Un regroupement de séances (cours, récitation, khutba). Le direct se lit sur le site depuis YouTube.')
                    ->schema([
                        Forms\Components\TextInput::make('slug')
                            ->label('Identifiant')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Nom technique, par exemple main ou recitation. Il ne s’affiche pas au public.'),

                        Forms\Components\Select::make('type')
                            ->label('Type')
                            ->options([
                                'video' => 'Vidéo',
                                'audio' => 'Audio seul',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('priority')
                            ->label('Priorité')
                            ->numeric()
                            ->default(0)
                            ->helperText('Le canal le plus prioritaire s’affiche en premier.'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),

                        Forms\Components\Toggle::make('requires_moderation')
                            ->label('Modérer le chat')
                            ->default(false),
                    ])->columns(2),

                Forms\Components\Section::make('Nom affiché')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('name.fr')
                                    ->label('Nom (français)')
                                    ->required(),

                                Forms\Components\TextInput::make('name.en')
                                    ->label('Nom (anglais)'),

                                Forms\Components\TextInput::make('name.ar')
                                    ->label('Nom (arabe)'),
                            ]),

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Textarea::make('description.fr')
                                    ->label('Description (français)')
                                    ->rows(3),

                                Forms\Components\Textarea::make('description.en')
                                    ->label('Description (anglais)')
                                    ->rows(3),

                                Forms\Components\Textarea::make('description.ar')
                                    ->label('Description (arabe)')
                                    ->rows(3),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('slug')
                    ->label('Identifiant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('name.fr')
                    ->label('Nom')
                    ->searchable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'audio' ? 'Audio' : 'Vidéo')
                    ->color(fn (string $state): string => $state === 'audio' ? 'warning' : 'primary'),

                Tables\Columns\TextColumn::make('streams_count')
                    ->counts('streams')
                    ->label('Séances'),

                Tables\Columns\TextColumn::make('active_streams_count')
                    ->counts('activeStreams')
                    ->label('En direct')
                    ->badge()
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Priorité')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Actif'),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'video' => 'Vidéo',
                        'audio' => 'Audio seul',
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
