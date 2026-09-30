<?php

namespace Modules\Community\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Community\Filament\Resources\SponsorshipResource\Pages;
use Modules\Community\Models\Sponsorship;

class SponsorshipResource extends Resource
{
    protected static ?string $model = Sponsorship::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Parrainages';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('sponsor_id')->relationship('sponsor', 'email')->searchable()->required(),
            Forms\Components\Select::make('student_id')->relationship('student', 'email')->searchable()->required(),
            Forms\Components\TextInput::make('amount_minor')->numeric()->required()->label('Montant (XOF)'),
            Forms\Components\Select::make('frequency')->options([
                'once' => 'Une fois',
                'monthly' => 'Mensuel',
                'yearly' => 'Annuel',
            ])->required(),
            Forms\Components\Select::make('status')->options([
                'active' => 'Actif',
                'paused' => 'En pause',
                'ended' => 'Terminé',
            ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('sponsor.email')->label('Parrain'),
            Tables\Columns\TextColumn::make('student.email')->label('Élève'),
            Tables\Columns\TextColumn::make('amount_minor')->label('XOF'),
            Tables\Columns\TextColumn::make('frequency'),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSponsorships::route('/'),
            'create' => Pages\CreateSponsorship::route('/create'),
            'edit' => Pages\EditSponsorship::route('/{record}/edit'),
        ];
    }
}
