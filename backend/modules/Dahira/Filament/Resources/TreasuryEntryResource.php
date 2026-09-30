<?php

namespace Modules\Dahira\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Dahira\Filament\Resources\TreasuryEntryResource\Pages;
use Modules\Dahira\Models\TreasuryEntry;

class TreasuryEntryResource extends Resource
{
    protected static ?string $model = TreasuryEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-scale';

    protected static ?string $navigationGroup = 'Dahira';

    protected static ?string $navigationLabel = 'Trésorerie';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('dahira_group_id')->relationship('group', 'id')->required(),
            Forms\Components\Select::make('direction')->options(['in' => 'Entrée', 'out' => 'Sortie'])->required(),
            Forms\Components\Select::make('category')->options([
                'contribution' => 'Cotisation',
                'donation' => 'Don',
                'expense' => 'Dépense',
                'adjustment' => 'Ajustement',
            ])->required(),
            Forms\Components\TextInput::make('amount_minor')->numeric()->required()->label('Montant (XOF)'),
            Forms\Components\TextInput::make('label')->required(),
            Forms\Components\DatePicker::make('occurred_on')->required(),
            Forms\Components\Textarea::make('note'),
            Forms\Components\Hidden::make('recorded_by')->default(fn () => auth()->id()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('occurred_on')->date(),
            Tables\Columns\TextColumn::make('label'),
            Tables\Columns\TextColumn::make('direction')->badge(),
            Tables\Columns\TextColumn::make('category'),
            Tables\Columns\TextColumn::make('amount_minor')->label('XOF'),
        ])->defaultSort('occurred_on', 'desc')->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTreasuryEntries::route('/'),
            'create' => Pages\CreateTreasuryEntry::route('/create'),
            'edit' => Pages\EditTreasuryEntry::route('/{record}/edit'),
        ];
    }
}
