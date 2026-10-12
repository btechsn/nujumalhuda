<?php

namespace Modules\Dahira\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Dahira\Filament\Concerns\ScopesToDahira;
use Modules\Dahira\Filament\Resources\ContributionPlanResource\Pages;
use Modules\Dahira\Models\ContributionPlan;
use Modules\Dahira\Services\DahiraAccess;

class ContributionPlanResource extends Resource
{
    use ScopesToDahira;

    public static function canViewAny(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('dahira_money');
    }

    protected static function officerRoles(): array
    {
        return DahiraAccess::TREASURER_ROLES;
    }

    protected static ?string $model = ContributionPlan::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Dahira';

    protected static ?string $navigationLabel = 'Plans de cotisation';

    protected static ?string $modelLabel = 'plan de cotisation';

    protected static ?string $pluralModelLabel = 'Plans de cotisation';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('dahira_group_id')
                ->label('Dahira')
                ->relationship('group', 'id')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name_i18n['fr'] ?? $record->id)
                ->searchable()
                ->required(),
            Forms\Components\TextInput::make('name_i18n.fr')->label('Nom')->required(),
            Forms\Components\TextInput::make('amount_minor')->numeric()->required()->label('Montant (XOF)'),
            Forms\Components\Select::make('frequency')
                ->label('Fréquence')
                ->options([
                    'monthly' => 'Mensuelle',
                    'yearly' => 'Annuelle',
                    'once' => 'Unique',
                ])
                ->required(),
            Forms\Components\TextInput::make('due_day')
                ->label('Jour d’échéance')
                ->numeric()
                ->minValue(1)
                ->maxValue(28)
                ->default(5)
                ->helperText('Jour du mois, de 1 à 28.'),
            Forms\Components\Toggle::make('is_active')->label('Actif')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name_i18n.fr')->label('Plan'),
            Tables\Columns\TextColumn::make('amount_minor')->label('Montant (XOF)'),
            Tables\Columns\TextColumn::make('frequency')
                ->label('Fréquence')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'yearly' => 'Annuelle',
                    'once' => 'Unique',
                    default => 'Mensuelle',
                }),
            Tables\Columns\IconColumn::make('is_active')->label('Actif')->boolean(),
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
