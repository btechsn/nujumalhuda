<?php

namespace Modules\Community\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
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

    protected static ?string $navigationLabel = 'Galerie';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('kind')->options(['photo' => 'Photo', 'video' => 'Vidéo'])->required(),
            Forms\Components\TextInput::make('event_name')->label('Nom de l\'événement')->required(),
            Forms\Components\TextInput::make('media_url')->url()->required()->helperText('URL de la photo ou de la vidéo'),
            Forms\Components\TextInput::make('thumbnail_url')->url()->label('Vignette (vidéo)')->helperText('Optionnel pour les vidéos'),
            Forms\Components\TextInput::make('caption_i18n.fr')->label('Légende (FR)'),
            Forms\Components\TextInput::make('caption_i18n.en')->label('Caption (EN)'),
            Forms\Components\TextInput::make('caption_i18n.ar')->label('التعليق'),
            Forms\Components\DatePicker::make('taken_on')->label('Date'),
            Forms\Components\Toggle::make('is_public')->helperText('Ne publier une photo d\'élève qu\'avec l\'autorisation du tuteur.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('kind')->badge(),
            Tables\Columns\TextColumn::make('event_name')->label('Événement')->searchable(),
            Tables\Columns\TextColumn::make('caption_i18n.fr')->label('Légende'),
            Tables\Columns\IconColumn::make('is_public')->boolean(),
            Tables\Columns\TextColumn::make('taken_on')->date(),
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
