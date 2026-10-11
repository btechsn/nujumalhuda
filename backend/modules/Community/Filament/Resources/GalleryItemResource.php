<?php

namespace Modules\Community\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Community\Filament\Resources\GalleryItemResource\Pages;
use Modules\Community\Models\GalleryItem;

class GalleryItemResource extends Resource
{
    protected static ?string $model = GalleryItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Médiathèque';

    protected static ?string $modelLabel = 'média';

    protected static ?string $pluralModelLabel = 'Médiathèque';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('kind')
                ->label('Type')
                ->options(['photo' => 'Photo', 'video' => 'Vidéo'])
                ->required()
                ->live(),
            Forms\Components\TextInput::make('event_name')->label('Événement')->required(),
            Forms\Components\FileUpload::make('media_path')
                ->label('Fichier')
                ->disk('public')
                ->directory('gallery')
                ->visibility('public')
                ->acceptedFileTypes(fn (Get $get): array => $get('kind') === 'video'
                    ? ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime', 'video/x-m4v']
                    : ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                ->maxSize(fn (Get $get): int => $get('kind') === 'video' ? 102400 : 10240)
                ->required(fn (Get $get): bool => blank($get('media_url')))
                ->helperText(fn (Get $get): string => $get('kind') === 'video'
                    ? 'MP4, WEBM ou MOV, 100 Mo maximum. Inutile si vous collez un lien.'
                    : 'JPG, PNG, WEBP ou GIF, 10 Mo maximum. Inutile si vous collez un lien.'),
            Forms\Components\FileUpload::make('thumbnail_path')
                ->label('Vignette')
                ->disk('public')
                ->directory('gallery/thumbnails')
                ->visibility('public')
                ->image()
                ->maxSize(5120)
                ->helperText('Facultatif. Utile pour une vidéo, afin d’afficher une image sur la carte.'),
            Forms\Components\TextInput::make('media_url')
                ->label('Lien du média')
                ->url()
                ->maxLength(255)
                ->required(fn (Get $get): bool => blank($get('media_path')))
                ->helperText('Collez une adresse si vous ne téléversez pas de fichier. Le fichier est utilisé en priorité.'),
            Forms\Components\TextInput::make('thumbnail_url')
                ->url()
                ->maxLength(255)
                ->label('Lien de la vignette')
                ->helperText('Facultatif si vous téléversez une vignette.'),
            Forms\Components\TextInput::make('caption_i18n.fr')->label('Légende (français)'),
            Forms\Components\TextInput::make('caption_i18n.en')->label('Légende (anglais)'),
            Forms\Components\TextInput::make('caption_i18n.ar')->label('Légende (arabe)'),
            Forms\Components\DatePicker::make('taken_on')->label('Date'),
            Forms\Components\Toggle::make('is_public')
                ->label('Visible sur le site')
                ->helperText('Ne publier une photo d\'élève qu\'avec l\'autorisation du tuteur.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('kind')
                ->label('Type')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => $state === 'video' ? 'Vidéo' : 'Photo'),
            Tables\Columns\TextColumn::make('event_name')->label('Événement')->searchable(),
            Tables\Columns\TextColumn::make('caption_i18n.fr')->label('Légende'),
            Tables\Columns\IconColumn::make('is_public')->label('Visible')->boolean(),
            Tables\Columns\TextColumn::make('taken_on')->label('Date')->date(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleryItems::route('/'),
            'create' => Pages\CreateGalleryItem::route('/create'),
            'edit' => Pages\EditGalleryItem::route('/{record}/edit'),
        ];
    }
}
