<?php

namespace Modules\Resources\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Resources\Filament\Resources\LibraryItemResource\Pages;
use Modules\Resources\Models\LibraryResource;

class LibraryItemResource extends Resource
{
    protected static ?string $model = LibraryResource::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    protected static ?string $navigationGroup = 'Ressources';

    protected static ?string $navigationLabel = 'Bibliothèque';

    protected static ?string $modelLabel = 'ressource';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('title_i18n.fr')->label('Titre (français)')->required(),
            Forms\Components\TextInput::make('title_i18n.en')->label('Titre (anglais)'),
            Forms\Components\TextInput::make('title_i18n.ar')->label('العنوان'),
            Forms\Components\Textarea::make('description_i18n.fr')->label('Description (français)'),
            Forms\Components\TextInput::make('author'),
            Forms\Components\Select::make('tradition')->options([
                'baye_niasse' => 'Baye Niasse',
                'sunnite' => 'Texte sunnite classique',
            ])->required(),
            Forms\Components\Select::make('kind')->options([
                'pdf' => 'PDF',
                'audio' => 'Audio',
            ])->required(),
            Forms\Components\TextInput::make('language')->default('ar')->maxLength(5),
            Forms\Components\TextInput::make('file_path')->label('Chemin de fichier (storage)'),
            Forms\Components\TextInput::make('external_url')->url(),
            Forms\Components\Toggle::make('is_public')->default(true),
            Forms\Components\TextInput::make('display_order')->numeric()->default(0),
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
