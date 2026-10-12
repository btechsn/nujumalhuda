<?php

namespace Modules\Resources\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Resources\Filament\Resources\LibraryItemResource\Pages;
use Modules\Resources\Models\LibraryResource;

class LibraryItemResource extends Resource
{
    public static function canViewAny(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('resources');
    }

    protected static ?string $model = LibraryResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Ressources';

    protected static ?string $navigationLabel = 'Bibliothèque';

    protected static ?string $modelLabel = 'ressource';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('slug')->label('Identifiant')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('title_i18n.fr')->label('Titre (français)')->required(),
            Forms\Components\TextInput::make('title_i18n.en')->label('Titre (anglais)'),
            Forms\Components\TextInput::make('title_i18n.ar')->label('Titre (arabe)'),
            Forms\Components\Textarea::make('description_i18n.fr')->label('Description (français)'),
            Forms\Components\FileUpload::make('cover_path')
                ->label('Image de couverture')
                ->disk('public')
                ->directory('library/covers')
                ->visibility('public')
                ->image()
                ->maxSize(5120)
                ->helperText('Facultatif. Sans image, la carte garde le fond coloré.'),
            Forms\Components\TextInput::make('author')->label('Auteur'),
            Forms\Components\Select::make('tradition')->label('Tradition')->options([
                'baye_niasse' => 'Baye Niasse',
                'sunnite' => 'Texte sunnite classique',
            ])->required(),
            Forms\Components\Select::make('kind')->label('Type')->options([
                'pdf' => 'PDF',
                'audio' => 'Audio',
            ])->required()->live(),
            Forms\Components\TextInput::make('language')->label('Langue')->default('ar')->maxLength(5),
            Forms\Components\FileUpload::make('file_path')
                ->label('Fichier')
                ->disk('public')
                ->directory('library')
                ->visibility('public')
                ->acceptedFileTypes(fn (Get $get): array => $get('kind') === 'audio'
                    ? [
                        'audio/mpeg',
                        'audio/mp3',
                        'audio/wav',
                        'audio/x-wav',
                        'audio/ogg',
                        'audio/mp4',
                        'audio/x-m4a',
                        'audio/aac',
                        'audio/webm',
                    ]
                    : ['application/pdf'])
                ->maxSize(102400)
                ->downloadable()
                ->openable()
                ->helperText(fn (Get $get): string => $get('kind') === 'audio'
                    ? 'MP3, M4A, WAV ou OGG, 100 Mo maximum. Inutile si vous collez un lien.'
                    : 'PDF, 100 Mo maximum. Inutile si vous collez un lien.'),
            Forms\Components\TextInput::make('external_url')
                ->label('Lien externe')
                ->url()
                ->maxLength(255)
                ->helperText('Collez une adresse si vous ne téléversez pas de fichier. Le fichier est utilisé en priorité.'),
            Forms\Components\Toggle::make('is_public')->label('Visible sur le site')->default(true),
            Forms\Components\TextInput::make('display_order')->label('Ordre d’affichage')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title_i18n.fr')->label('Titre')->searchable(),
                Tables\Columns\TextColumn::make('author')->searchable(),
                Tables\Columns\TextColumn::make('tradition')->badge(),
                Tables\Columns\TextColumn::make('kind')->badge(),
                Tables\Columns\TextColumn::make('download_count')->label('Téléchargements'),
                Tables\Columns\IconColumn::make('is_public')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('display_order');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLibraryItems::route('/'),
            'create' => Pages\CreateLibraryItem::route('/create'),
            'edit' => Pages\EditLibraryItem::route('/{record}/edit'),
        ];
    }
}
