<?php

namespace Modules\Resources\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Resources\Filament\Resources\AudioRecitationResource\Pages;
use Modules\Resources\Models\AudioRecitation;

class AudioRecitationResource extends Resource
{
    protected static ?string $model = AudioRecitation::class;

    protected static ?string $navigationIcon = 'heroicon-o-musical-note';

    protected static ?string $navigationGroup = 'Ressources';

    protected static ?string $navigationLabel = 'Récitations';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('surah_number')->numeric()->minValue(1)->maxValue(114)->required(),
            Forms\Components\TextInput::make('surah_name_i18n.fr')->label('Sourate (français)')->required(),
            Forms\Components\TextInput::make('surah_name_i18n.en')->label('Surah (English)'),
            Forms\Components\TextInput::make('surah_name_i18n.ar')->label('السورة')->required(),
            Forms\Components\TextInput::make('reciter')->required(),
            Forms\Components\TextInput::make('audio_url')->url()->required(),
            Forms\Components\TextInput::make('duration_seconds')->numeric(),
            Forms\Components\Toggle::make('is_public')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('surah_number')->label('N°')->sortable(),
            Tables\Columns\TextColumn::make('surah_name_i18n.ar')->label('Sourate'),
            Tables\Columns\TextColumn::make('reciter')->searchable(),
            Tables\Columns\TextColumn::make('play_count')->label('Écoutes'),
            Tables\Columns\IconColumn::make('is_public')->boolean(),
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
