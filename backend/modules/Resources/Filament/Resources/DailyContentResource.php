<?php

namespace Modules\Resources\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Resources\Filament\Resources\DailyContentResource\Pages;
use Modules\Resources\Models\DailyContent;

class DailyContentResource extends Resource
{
    protected static ?string $model = DailyContent::class;

    protected static ?string $navigationIcon = 'heroicon-o-sun';

    protected static ?string $navigationGroup = 'Ressources';

    protected static ?string $navigationLabel = 'Verset et hadith du jour';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('display_date')->required(),
            Forms\Components\Select::make('type')->options([
                'verse' => 'Verset',
                'hadith' => 'Hadith',
            ])->required(),
            Forms\Components\Textarea::make('arabic_text')->label('Texte arabe')->required()->rows(4),
            Forms\Components\Textarea::make('translation_i18n.fr')->label('Traduction française')->required(),
            Forms\Components\Textarea::make('translation_i18n.en')->label('English translation'),
            Forms\Components\Textarea::make('translation_i18n.ar')->label('الترجمة')->helperText('Le texte arabe source reste dans le champ dédié. Ne pas le remplacer par une traduction automatique.'),
            Forms\Components\TextInput::make('source')->required()->helperText('Ex. Coran, Sahih al-Bukhari'),
            Forms\Components\TextInput::make('reference')->helperText('Ex. 2:255, n° 1'),
            Forms\Components\Toggle::make('is_published')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('display_date')->date()->sortable(),
            Tables\Columns\TextColumn::make('type')->badge(),
            Tables\Columns\TextColumn::make('reference'),
            Tables\Columns\TextColumn::make('source'),
            Tables\Columns\IconColumn::make('is_published')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()])->defaultSort('display_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyContents::route('/'),
            'create' => Pages\CreateDailyContent::route('/create'),
            'edit' => Pages\EditDailyContent::route('/{record}/edit'),
        ];
    }
}
