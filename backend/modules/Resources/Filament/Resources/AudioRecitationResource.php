<?php

namespace Modules\Resources\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Resources\Filament\Resources\AudioRecitationResource\Pages;
use Modules\Resources\Models\AudioRecitation;

class AudioRecitationResource extends Resource
{
    public static function canViewAny(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('resources');
    }

    protected static ?string $model = AudioRecitation::class;

    protected static ?string $navigationIcon = 'heroicon-o-musical-note';

    protected static ?string $navigationGroup = 'Ressources';

    protected static ?string $navigationLabel = 'Récitations';

    protected static ?string $modelLabel = 'récitation';

    protected static ?string $pluralModelLabel = 'Récitations';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('surah_number')
                ->label('Numéro de sourate')
                ->numeric()
                ->minValue(1)
                ->maxValue(114)
                ->required(),
            Forms\Components\TextInput::make('surah_name_i18n.fr')
                ->label('Sourate (français)')
                ->required(),
            Forms\Components\TextInput::make('surah_name_i18n.en')
                ->label('Sourate (anglais)'),
            Forms\Components\TextInput::make('surah_name_i18n.ar')
                ->label('Sourate (arabe)')
                ->required(),
            Forms\Components\TextInput::make('reciter')
                ->label('Récitant')
                ->required(),
            Forms\Components\FileUpload::make('audio_path')
                ->label('Fichier audio')
                ->disk('public')
                ->directory('recitations')
                ->visibility('public')
                ->acceptedFileTypes([
                    'audio/mpeg',
                    'audio/mp3',
                    'audio/wav',
                    'audio/x-wav',
                    'audio/ogg',
                    'audio/mp4',
                    'audio/x-m4a',
                    'audio/aac',
                    'audio/webm',
                ])
                ->maxSize(51200)
                ->required(fn (Get $get): bool => blank($get('audio_url')))
                ->helperText('MP3, M4A, WAV ou OGG, 50 Mo maximum. Si un fichier est choisi, c’est lui qui est lu sur le site.'),
            Forms\Components\TextInput::make('audio_url')
                ->label('Lien audio')
                ->url()
                ->maxLength(255)
                ->required(fn (Get $get): bool => blank($get('audio_path')))
                ->helperText('Collez une adresse si vous ne téléversez pas de fichier.'),
            Forms\Components\TextInput::make('duration_seconds')
                ->label('Durée (secondes)')
                ->numeric(),
            Forms\Components\Toggle::make('is_public')
                ->label('Visible sur le site')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('surah_number')->label('N°')->sortable(),
            Tables\Columns\TextColumn::make('surah_name_i18n.fr')->label('Sourate'),
            Tables\Columns\TextColumn::make('reciter')->label('Récitant')->searchable(),
            Tables\Columns\TextColumn::make('play_count')->label('Écoutes'),
            Tables\Columns\IconColumn::make('is_public')->label('Visible')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()])->defaultSort('surah_number');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAudioRecitations::route('/'),
            'create' => Pages\CreateAudioRecitation::route('/create'),
            'edit' => Pages\EditAudioRecitation::route('/{record}/edit'),
        ];
    }
}
