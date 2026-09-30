<?php

namespace Modules\Resources\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Resources\Filament\Resources\MuudRamadanRateResource\Pages;
use Modules\Resources\Models\MuudRamadanRate;

class MuudRamadanRateResource extends Resource
{
    protected static ?string $model = MuudRamadanRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationGroup = 'Ressources';

    protected static ?string $navigationLabel = 'Muud Ramadan';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('hijri_year')->numeric()->required()->label('Année hégirienne'),
            Forms\Components\Select::make('madhhab')->options(['maliki' => 'Malikite'])->default('maliki')->required(),
            Forms\Components\TextInput::make('amount_per_person_minor')
                ->label('Montant par personne (XOF)')
                ->numeric()
                ->required(),
            Forms\Components\Select::make('staple')->options([
                'rice' => 'Riz',
                'millet' => 'Mil',
                'wheat' => 'Blé',
                'dates' => 'Dattes',
            ])->default('rice')->required(),
            Forms\Components\TextInput::make('sa_grams')->numeric()->default(2400)->label('Saʿ en grammes'),
            Forms\Components\DatePicker::make('prices_as_of')->required(),
            Forms\Components\Toggle::make('prices_are_indicative')->label('Montant indicatif')->default(true),
            Forms\Components\TextInput::make('label_i18n.fr')->label('Libellé (français)')->required(),
            Forms\Components\TextInput::make('label_i18n.en')->label('Label (English)'),
            Forms\Components\TextInput::make('label_i18n.ar')->label('التسمية'),
            Forms\Components\Textarea::make('source_i18n.fr')->label('Source (français)')->required()->rows(4),
            Forms\Components\Textarea::make('source_i18n.en')->label('Source (English)'),
            Forms\Components\Textarea::make('source_i18n.ar')->label('المصدر'),
            Forms\Components\Toggle::make('is_current')->label('Barème en vigueur'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('hijri_year')->label('AH'),
            Tables\Columns\TextColumn::make('amount_per_person_minor')->label('XOF / pers.'),
            Tables\Columns\TextColumn::make('staple')->badge(),
            Tables\Columns\TextColumn::make('prices_as_of')->date(),
            Tables\Columns\IconColumn::make('prices_are_indicative')->label('Indicatif')->boolean(),
            Tables\Columns\IconColumn::make('is_current')->label('En vigueur')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMuudRamadanRates::route('/'),
            'create' => Pages\CreateMuudRamadanRate::route('/create'),
            'edit' => Pages\EditMuudRamadanRate::route('/{record}/edit'),
        ];
    }
}
