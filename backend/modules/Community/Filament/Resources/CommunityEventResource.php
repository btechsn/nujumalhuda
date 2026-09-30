<?php

namespace Modules\Community\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Community\Filament\Resources\CommunityEventResource\Pages;
use Modules\Community\Filament\Resources\CommunityEventResource\RelationManagers;
use Modules\Community\Models\CommunityEvent;

class CommunityEventResource extends Resource
{
    protected static ?string $model = CommunityEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Événements';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title_i18n.fr')->label('Titre')->required(),
            Forms\Components\TextInput::make('title_i18n.en')->label('Title'),
            Forms\Components\TextInput::make('title_i18n.ar')->label('العنوان'),
            Forms\Components\Textarea::make('description_i18n.fr')->label('Description'),
            Forms\Components\DateTimePicker::make('starts_at')->required(),
            Forms\Components\DateTimePicker::make('ends_at'),
            Forms\Components\TextInput::make('location'),
            Forms\Components\TextInput::make('capacity')->numeric(),
            Forms\Components\Toggle::make('is_published'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title_i18n.fr')->label('Titre'),
            Tables\Columns\TextColumn::make('starts_at')->dateTime(),
            Tables\Columns\TextColumn::make('registrations_count')->counts('registrations')->label('Inscrits'),
            Tables\Columns\IconColumn::make('is_published')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
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
            'index' => Pages\ListCommunityEvents::route('/'),
            'create' => Pages\CreateCommunityEvent::route('/create'),
            'edit' => Pages\EditCommunityEvent::route('/{record}/edit'),
        ];
    }
}
