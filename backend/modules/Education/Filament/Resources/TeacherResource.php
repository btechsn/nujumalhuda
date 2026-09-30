<?php

namespace Modules\Education\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Education\Models\Teacher;

class TeacherResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationGroup = 'Éducation';
    protected static ?string $navigationLabel = 'Enseignants';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Utilisateur')
                    ->schema([
                        Forms\Components\Select::make('id')
                            ->label('Utilisateur')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('L\'ID du teacher est le même que celui du user'),
                        
                        Forms\Components\Select::make('organization_id')
                            ->relationship('organization', 'name')
                            ->required()
                            ->default(fn() => auth()->user()->organization_id),
                    ])->columns(2),
                
                Forms\Components\Section::make('Biographie')
                    ->schema([
                        Forms\Components\RichEditor::make('bio_i18n.fr')
                            ->label('Biographie (Français)')
                            ->columnSpanFull(),
                        
                        Forms\Components\RichEditor::make('bio_i18n.en')
                            ->label('Biography (English)')
                            ->columnSpanFull(),
                        
                        Forms\Components\RichEditor::make('bio_i18n.ar')
                            ->label('السيرة الذاتية (عربي)')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Spécialités')
                    ->schema([
                        Forms\Components\TagsInput::make('specialties_i18n.fr')
                            ->label('Spécialités (Français)')
                            ->placeholder('Ex: Tajwid, Fiqh, Hadith')
                            ->helperText('Appuyez sur Entrée pour ajouter'),
                        
                        Forms\Components\TagsInput::make('specialties_i18n.en')
                            ->label('Specialties (English)'),
                        
                        Forms\Components\TagsInput::make('specialties_i18n.ar')
                            ->label('التخصصات (عربي)'),
                    ]),
                
                Forms\Components\Section::make('Qualifications')
                    ->schema([
                        Forms\Components\TagsInput::make('qualifications_i18n.fr')
                            ->label('Diplômes et formations (Français)')
                            ->placeholder('Ex: Licence en études islamiques'),
                        
                        Forms\Components\TagsInput::make('qualifications_i18n.en')
                            ->label('Qualifications (English)'),
                        
                        Forms\Components\TagsInput::make('qualifications_i18n.ar')
                            ->label('المؤهلات (عربي)'),
                    ]),
                
                Forms\Components\Section::make('Ijaza et Sanad')
                    ->schema([
                        Forms\Components\Toggle::make('has_ijaza')
                            ->label('Possède une ijaza')
                            ->live(),
                        
                        Forms\Components\KeyValue::make('ijaza_details')
                            ->label('Détails de l\'ijaza')
                            ->visible(fn($get) => $get('has_ijaza'))
                            ->columnSpanFull(),
                        
                        Forms\Components\Textarea::make('sanad')
                            ->label('Chaîne de transmission (Sanad)')
                            ->rows(4)
                            ->visible(fn($get) => $get('has_ijaza'))
                            ->columnSpanFull()
                            ->helperText('Liste des maîtres dans la chaîne de transmission'),
                    ]),
                
                Forms\Components\Section::make('Disponibilité')
                    ->schema([
                        Forms\Components\Toggle::make('is_available')
                            ->label('Disponible pour enseigner')
                            ->default(true),
                        
                        Forms\Components\KeyValue::make('availability')
                            ->label('Horaires disponibles')
                            ->keyLabel('Jour')
                            ->valueLabel('Heures')
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Médias et réseaux')
                    ->schema([
                        Forms\Components\Select::make('photo_media_id')
                            ->label('Photo de profil')
                            ->relationship('photo', 'filename')
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\TextInput::make('youtube_channel')
                            ->label('Chaîne YouTube')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://youtube.com/@...'),
                        
                        Forms\Components\TextInput::make('facebook_page')
                            ->label('Page Facebook')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://facebook.com/...'),
                    ]),
                
                Forms\Components\Section::make('Options d\'affichage')
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Affiché sur la page enseignants')
                            ->default(false),
                        
                        Forms\Components\TextInput::make('display_order')
                            ->label('Ordre d\'affichage')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('0 = premier'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                
                Tables\Columns\IconColumn::make('has_ijaza')
                    ->label('Ijaza')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('is_available')
                    ->label('Disponible')
                    ->boolean(),
                
                Tables\Columns\IconColumn::make('is_featured')
                    ->label('En avant')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('students_count')
                    ->label('Élèves')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_sessions')
                    ->label('Sessions')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('display_order')
                    ->label('Ordre')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('has_ijaza')->label('Possède ijaza'),
                Tables\Filters\TernaryFilter::make('is_available')->label('Disponible'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('En avant'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('display_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\Education\Filament\Resources\TeacherResource\Pages\ListTeachers::route('/'),
            'create' => \Modules\Education\Filament\Resources\TeacherResource\Pages\CreateTeacher::route('/create'),
            'edit' => \Modules\Education\Filament\Resources\TeacherResource\Pages\EditTeacher::route('/{record}/edit'),
        ];
    }
}
