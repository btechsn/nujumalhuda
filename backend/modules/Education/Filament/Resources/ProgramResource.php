<?php

namespace Modules\Education\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Education\Models\Program;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Éducation';
    protected static ?string $navigationLabel = 'Programmes';
    protected static ?int $navigationSort = 1;

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
                        
                        Forms\Components\TextInput::make('name_i18n.fr')
                            ->label('Nom (Français)')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('name_i18n.en')
                            ->label('Nom (English)')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('name_i18n.ar')
                            ->label('الاسم (عربي)')
                            ->maxLength(255),
                        
                        Forms\Components\RichEditor::make('description_i18n.fr')
                            ->label('Description (Français)')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Caractéristiques')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->options([
                                'coran' => 'Coran',
                                'arabe' => 'Arabe',
                                'baye_niasse' => 'Baye Niasse',
                                'sunnite' => 'Corpus Sunnite',
                            ])
                            ->required(),
                        
                        Forms\Components\Select::make('level')
                            ->options([
                                'debutant' => 'Débutant',
                                'intermediaire' => 'Intermédiaire',
                                'avance' => 'Avancé',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('duration_weeks')
                            ->label('Durée (semaines)')
                            ->numeric()
                            ->minValue(1),
                        
                        Forms\Components\TextInput::make('hours_per_week')
                            ->label('Heures par semaine')
                            ->numeric()
                            ->minValue(1),
                    ])->columns(2),
                
                Forms\Components\Section::make('Tarification')
                    ->schema([
                        Forms\Components\TextInput::make('tuition_amount_minor')
                            ->label('Frais de scolarité (FCFA)')
                            ->numeric()
                            ->suffix('FCFA')
                            ->helperText('Montant en francs CFA'),
                        
                        Forms\Components\TextInput::make('registration_amount_minor')
                            ->label('Frais d\'inscription (FCFA)')
                            ->numeric()
                            ->suffix('FCFA'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Prérequis')
                    ->schema([
                        Forms\Components\TextInput::make('min_age')
                            ->label('Âge minimum')
                            ->numeric()
                            ->minValue(5),
                        
                        Forms\Components\TextInput::make('max_age')
                            ->label('Âge maximum')
                            ->numeric(),
                        
                        Forms\Components\Select::make('prerequisite_program_id')
                            ->label('Programme prérequis')
                            ->relationship('prerequisiteProgram', 'name_i18n->fr')
                            ->searchable(),
                    ])->columns(3),
                
                Forms\Components\Section::make('Options')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Actif')
                            ->default(true),
                        
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Mis en avant'),
                        
                        Forms\Components\TextInput::make('display_order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_i18n.fr')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'coran',
                        'primary' => 'arabe',
                        'warning' => 'baye_niasse',
                        'info' => 'sunnite',
                    ]),
                
                Tables\Columns\BadgeColumn::make('level')
                    ->label('Niveau'),
                
                Tables\Columns\TextColumn::make('duration_weeks')
                    ->label('Durée')
                    ->suffix(' sem.')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Actif')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('En avant')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type'),
                Tables\Filters\SelectFilter::make('level'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Actif'),
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

    public static function getPages(): array
    {
        return [
            'index' => \Modules\Education\Filament\Resources\ProgramResource\Pages\ListPrograms::route('/'),
            'create' => \Modules\Education\Filament\Resources\ProgramResource\Pages\CreateProgram::route('/create'),
            'edit' => \Modules\Education\Filament\Resources\ProgramResource\Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
