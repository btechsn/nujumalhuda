<?php

namespace Modules\Resources\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Resources\Filament\Resources\ZakatRateResource\Pages;
use Modules\Resources\Models\ZakatRate;

class ZakatRateResource extends Resource
{
    public static function canViewAny(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('resources');
    }

    protected static ?string $model = ZakatRate::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Ressources';

    protected static ?string $navigationLabel = 'Zakat — nisab';

    protected static ?string $modelLabel = 'barème';

    protected static ?string $pluralModelLabel = 'Zakat — nisab';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('madhhab')
                ->label('École')
                ->options(['maliki' => 'Malikite'])
                ->default('maliki')
                ->required(),
            Forms\Components\Select::make('nisab_basis')
                ->label('Base du nisab')
                ->options([
                    'silver' => 'Argent (595 g) — retenu par précaution',
                    'gold' => 'Or (85 g)',
                ])
                ->required(),
            Forms\Components\TextInput::make('gold_nisab_grams')
                ->label('Nisab or (grammes)')
                ->numeric()
                ->default(85)
                ->required(),
            Forms\Components\TextInput::make('silver_nisab_grams')
                ->label('Nisab argent (grammes)')
                ->numeric()
                ->default(595)
                ->required(),
            Forms\Components\TextInput::make('gold_price_per_gram_minor')->label('Prix du gramme d\'or (XOF)')->numeric()->required(),
            Forms\Components\TextInput::make('silver_price_per_gram_minor')->label('Prix du gramme d\'argent (XOF)')->numeric()->required(),
            Forms\Components\DatePicker::make('prices_as_of')->label('Prix au')->required(),
            Forms\Components\Toggle::make('prices_are_indicative')->label('Prix indicatifs')->default(true),
            Forms\Components\Textarea::make('source_i18n.fr')->label('Source (français)')->required()->rows(4),
            Forms\Components\Textarea::make('source_i18n.en')->label('Source (anglais)'),
            Forms\Components\Textarea::make('source_i18n.ar')->label('Source (arabe)'),
            Forms\Components\TextInput::make('rate_numerator')
                ->label('Taux — numérateur')
                ->numeric()
                ->default(1)
                ->helperText('2,5 % correspond à 1 / 40.'),
            Forms\Components\TextInput::make('rate_denominator')
                ->label('Taux — dénominateur')
                ->numeric()
                ->default(40),
            Forms\Components\Toggle::make('is_current')->label('Barème en vigueur'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('prices_as_of')->label('Prix au')->date(),
            Tables\Columns\TextColumn::make('nisab_basis')
                ->label('Base du nisab')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => $state === 'gold' ? 'Or' : 'Argent'),
            Tables\Columns\TextColumn::make('gold_price_per_gram_minor')->label('Or / g'),
            Tables\Columns\TextColumn::make('silver_price_per_gram_minor')->label('Argent / g'),
            Tables\Columns\IconColumn::make('prices_are_indicative')->label('Indicatif')->boolean(),
            Tables\Columns\IconColumn::make('is_current')->label('En vigueur')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListZakatRates::route('/'),
            'create' => Pages\CreateZakatRate::route('/create'),
            'edit' => Pages\EditZakatRate::route('/{record}/edit'),
        ];
    }
}
