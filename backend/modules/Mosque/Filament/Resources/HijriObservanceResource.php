<?php

namespace Modules\Mosque\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Mosque\Filament\Resources\HijriObservanceResource\Pages;
use Modules\Mosque\Models\HijriObservance;

class HijriObservanceResource extends Resource
{
    protected static ?string $model = HijriObservance::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationGroup = 'Mosquée';

    protected static ?string $navigationLabel = 'Calendrier hégirien';

    protected static ?string $modelLabel = 'date observée';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('help')
                ->label('Observation au Sénégal')
                ->content('Saisissez la date grégorienne annoncée localement. Elle remplace le calcul pour cette échéance. Supprimer la ligne rétablit le calendrier tabulaire.'),
            Forms\Components\TextInput::make('hijri_year')
                ->label('Année hégirienne')
                ->numeric()
                ->minValue(1400)
                ->maxValue(1600)
                ->required(),
            Forms\Components\Select::make('kind')
                ->label('Échéance')
                ->options(HijriObservance::KINDS)
                ->required(),
            Forms\Components\DatePicker::make('gregorian_date')
                ->label('Date grégorienne annoncée')
                ->required()
                ->native(false),
            Forms\Components\Textarea::make('note')
                ->label('Note')
                ->rows(2)
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('hijri_year')->label('Année')->sortable(),
                Tables\Columns\TextColumn::make('kind')
                    ->label('Échéance')
                    ->formatStateUsing(fn (string $state): string => HijriObservance::KINDS[$state] ?? $state),
                Tables\Columns\TextColumn::make('gregorian_date')->label('Date')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('note')->label('Note')->limit(40),
            ])
            ->defaultSort('gregorian_date', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHijriObservances::route('/'),
            'create' => Pages\CreateHijriObservance::route('/create'),
            'edit' => Pages\EditHijriObservance::route('/{record}/edit'),
        ];
    }
}
