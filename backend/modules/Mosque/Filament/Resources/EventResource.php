<?php

namespace Modules\Mosque\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Mosque\Filament\Resources\EventResource\Pages;
use Modules\Mosque\Filament\Resources\EventResource\RelationManagers;
use Modules\Mosque\Models\MosqueEvent;

class EventResource extends Resource
{
    protected static ?string $model = MosqueEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Zawiya';
    protected static ?string $navigationLabel = 'Événements';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informations générales')
                    ->schema([
                        Forms\Components\Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->required()
                            ->default(fn() => auth()->user()->organization_id),
                        
                        Forms\Components\Select::make('type')
                            ->options([
                                'lecture' => 'Conférence',
                                'conference' => 'Séminaire',
                                'special_prayer' => 'Prière spéciale',
                                'fundraising' => 'Collecte de fonds',
                                'community' => 'Événement communautaire',
                                'gamou' => 'Gamou',
                            ])
                            ->required(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Titre et description')
                    ->schema([
                        Forms\Components\TextInput::make('title_i18n.fr')
                            ->label('Titre (Français)')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('title_i18n.en')
                            ->label('Title (English)')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('title_i18n.ar')
                            ->label('العنوان (عربي)')
                            ->maxLength(255),
                        
                        Forms\Components\RichEditor::make('description_i18n.fr')
                            ->label('Description (Français)')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Dates et horaires')
                    ->schema([
                        Forms\Components\DateTimePicker::make('start_at')
                            ->label('Début')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\DateTimePicker::make('end_at')
                            ->label('Fin')
                            ->after('start_at'),
                        
                        Forms\Components\Toggle::make('is_recurring')
                            ->label('Événement récurrent')
                            ->live(),
                        
                        Forms\Components\Select::make('recurrence_pattern')
                            ->label('Type de récurrence')
                            ->options([
                                'weekly' => 'Hebdomadaire',
                                'monthly' => 'Mensuel',
                                'yearly' => 'Annuel',
                            ])
                            ->visible(fn($get) => $get('is_recurring')),
                    ])->columns(2),
                
                Forms\Components\Section::make('Lieu')
                    ->schema([
                        Forms\Components\TextInput::make('location')
                            ->label('Lieu')
                            ->maxLength(255)
                            ->placeholder('Mosquée Nujum Al-Huda'),
                        
                        Forms\Components\Textarea::make('location_details')
                            ->label('Détails du lieu')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Intervenant')
                    ->schema([
                        Forms\Components\Select::make('speaker_id')
                            ->label('Intervenant (enseignant)')
                            ->relationship('speaker', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\TextInput::make('speaker_name')
                            ->label('Intervenant externe')
                            ->maxLength(255),
                    ])->columns(2),
                
                Forms\Components\Section::make('Capacité et inscriptions')
                    ->schema([
                        Forms\Components\TextInput::make('capacity')
                            ->label('Capacité maximale')
                            ->numeric()
                            ->minValue(1),
                        
                        Forms\Components\Toggle::make('requires_registration')
                            ->label('Inscription requise'),
                        
                        Forms\Components\TextInput::make('registered_count')
                            ->label('Inscrits')
                            ->numeric()
                            ->disabled()
                            ->default(0),
                    ])->columns(3),
                
                Forms\Components\Section::make('Image et vidéo')
                    ->schema([
                        Forms\Components\Select::make('image_media_id')
                            ->label('Image de couverture')
                            ->relationship('image', 'filename')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('youtube_url')
                            ->label('Vidéo YouTube (après l\'événement)')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://youtube.com/watch?v=...'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Options')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'upcoming' => 'À venir',
                                'ongoing' => 'En cours',
                                'completed' => 'Terminé',
                                'cancelled' => 'Annulé',
                            ])
                            ->required()
                            ->default('upcoming'),
                        
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Mis en avant')
                            ->default(false),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title_i18n.fr')
                    ->label('Titre')
                    ->searchable()
                    ->limit(40),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->colors([
                        'primary' => 'lecture',
                        'success' => 'conference',
                        'warning' => 'special_prayer',
                        'danger' => 'fundraising',
                        'info' => 'community',
                        'gray' => 'gamou',
                    ]),
                
                Tables\Columns\TextColumn::make('start_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacité')
                    ->suffix(' pers.'),
                
                Tables\Columns\TextColumn::make('registered_count')
                    ->label('Inscrits')
                    ->suffix(' pers.')
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'upcoming',
                        'success' => 'ongoing',
                        'secondary' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('En avant')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Mis en avant'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('start_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\RegistrationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\Mosque\Filament\Resources\EventResource\Pages\ListEvents::route('/'),
            'create' => \Modules\Mosque\Filament\Resources\EventResource\Pages\CreateEvent::route('/create'),
            'edit' => \Modules\Mosque\Filament\Resources\EventResource\Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
