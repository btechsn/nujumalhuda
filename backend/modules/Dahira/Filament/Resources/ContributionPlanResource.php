<?php

namespace Modules\Dahira\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Dahira\Filament\Resources\ContributionPlanResource\Pages;
use Modules\Dahira\Models\ContributionPlan;

class ContributionPlanResource extends Resource
{
    protected static ?string $model = ContributionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Dahira';

    protected static ?string $navigationLabel = 'Plans de cotisation';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('dahira_group_id')->relationship('group', 'id')->required(),
            Forms\Components\TextInput::make('name_i18n.fr')->label('Nom')->required(),
            Forms\Components\TextInput::make('amount_minor')->numeric()->required()->label('Montant (XOF)'),
            Forms\Components\Select::make('frequency')->options([
                'monthly' => 'Mensuelle',
                'yearly' => 'Annuelle',
                'once' => 'Unique',
            ])->required(),
            Forms\Components\TextInput::make('due_day')->numeric()->minValue(1)->maxValue(28)->default(5),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name_i18n.fr')->label('Plan'),
            Tables\Columns\TextColumn::make('amount_minor')->label('XOF'),
            Tables\Columns\TextColumn::make('frequency'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContributionPlans::route('/'),
            'create' => Pages\CreateContributionPlan::route('/create'),
            'edit' => Pages\EditContributionPlan::route('/{record}/edit'),
        ];
    }
}
