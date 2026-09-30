<?php

namespace Modules\Community\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Community\Filament\Resources\DonationResource\Pages;
use Modules\Community\Models\Donation;

class DonationResource extends Resource
{
    protected static ?string $model = Donation::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    protected static ?string $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Dons';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('donor_name'),
            Forms\Components\Toggle::make('is_anonymous'),
            Forms\Components\TextInput::make('amount_minor')->numeric()->required()->label('Montant (XOF)'),
            Forms\Components\Select::make('status')->options([
                'pending' => 'En attente',
                'completed' => 'Reçu',
                'failed' => 'Échoué',
            ])->required(),
            Forms\Components\DateTimePicker::make('paid_at'),
            Forms\Components\Textarea::make('message'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('donor_name')->label('Donateur'),
            Tables\Columns\TextColumn::make('amount_minor')->label('XOF'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\IconColumn::make('is_anonymous')->boolean(),
            Tables\Columns\TextColumn::make('paid_at')->dateTime(),
        ])->actions([Tables\Actions\EditAction::make()])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDonations::route('/'),
            'create' => Pages\CreateDonation::route('/create'),
            'edit' => Pages\EditDonation::route('/{record}/edit'),
        ];
    }
}
