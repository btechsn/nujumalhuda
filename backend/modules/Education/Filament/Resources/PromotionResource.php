<?php

namespace Modules\Education\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Education\Models\Promotion;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Éducation';
    protected static ?string $navigationLabel = 'Promotions';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Programme')
                    ->schema([
                        Forms\Components\Select::make('program_id')
                            ->relationship('program', 'name_i18n->fr')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->required()
                            ->default(fn() => auth()->user()->organization_id),
                    ])->columns(2),
                
                Forms\Components\Section::make('Identification')
                    ->schema([
                        Forms\Components\TextInput::make('name_i18n.fr')
                            ->label('Nom de la promotion (Français)')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Promotion Coran 2026-2027'),
                        
                        Forms\Components\TextInput::make('name_i18n.en')
                            ->label('Nom (English)')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('name_i18n.ar')
                            ->label('الاسم (عربي)')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('code')
                            ->label('Code unique')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->placeholder('CORAN-2026-A')
                            ->helperText('Ex: CORAN-2026-A, ARABE-2027-B'),
                        
                        Forms\Components\TextInput::make('academic_year')
                            ->label('Année académique')
                            ->required()
                            ->numeric()
                            ->default(date('Y'))
                            ->minValue(2020)
                            ->maxValue(2050),
                    ]),
                
                Forms\Components\Section::make('Période')
                    ->schema([
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date de début')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date de fin')
                            ->after('start_date'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Capacité')
                    ->schema([
                        Forms\Components\TextInput::make('capacity')
                            ->label('Capacité maximale')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Nombre maximal d\'élèves'),
                        
                        Forms\Components\TextInput::make('min_students')
                            ->label('Minimum d\'élèves')
                            ->numeric()
                            ->minValue(1)
                            ->helperText('Seuil minimal pour ouvrir la promotion'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Enseignant et horaires')
                    ->schema([
                        Forms\Components\Select::make('main_teacher_id')
                            ->label('Enseignant principal')
                            ->relationship('mainTeacher', 'name')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\TextInput::make('location')
                            ->label('Lieu')
                            ->maxLength(255)
                            ->placeholder('Salle A, Bâtiment principal'),
                        
                        Forms\Components\KeyValue::make('schedule')
                            ->label('Horaires')
                            ->keyLabel('Jour')
                            ->valueLabel('Heures')
                            ->helperText('Ex: Lundi → 14h-16h')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('État')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'upcoming' => 'À venir',
                                'ongoing' => 'En cours',
                                'completed' => 'Terminée',
                                'cancelled' => 'Annulée',
                            ])
                            ->required()
                            ->default('upcoming'),
                        
                        Forms\Components\Toggle::make('is_open_for_enrollment')
                            ->label('Ouverte aux inscriptions')
                            ->default(true),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name_i18n.fr')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                
                Tables\Columns\TextColumn::make('program.name_i18n.fr')
                    ->label('Programme')
                    ->sortable()
                    ->limit(30),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'upcoming',
                        'success' => 'ongoing',
                        'secondary' => 'completed',
                        'danger' => 'cancelled',
                    ]),
                
                Tables\Columns\TextColumn::make('enrolled_count')
                    ->label('Inscrits')
                    ->sortable()
                    ->suffix(' élèves'),
                
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Capacité')
                    ->suffix(' max'),
                
                Tables\Columns\IconColumn::make('is_open_for_enrollment')
                    ->label('Inscriptions')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('program_id')
                    ->relationship('program', 'name_i18n->fr')
                    ->label('Programme'),
                Tables\Filters\TernaryFilter::make('is_open_for_enrollment')
                    ->label('Inscriptions ouvertes'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('enrollments')
                    ->label('Inscriptions')
                    ->icon('heroicon-o-users')
                    ->url(fn($record) => route('filament.admin.resources.enrollments.index', [
                        'tableFilters[promotion_id][value]' => $record->id,
                    ])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\Education\Filament\Resources\PromotionResource\Pages\ListPromotions::route('/'),
            'create' => \Modules\Education\Filament\Resources\PromotionResource\Pages\CreatePromotion::route('/create'),
            'edit' => \Modules\Education\Filament\Resources\PromotionResource\Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
