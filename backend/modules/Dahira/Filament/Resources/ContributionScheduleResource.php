<?php

namespace Modules\Dahira\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Dahira\Filament\Concerns\ScopesToDahira;
use Modules\Dahira\Filament\Resources\ContributionScheduleResource\Pages;
use Modules\Dahira\Models\ContributionSchedule;
use Modules\Dahira\Services\ContributionRecorder;
use Modules\Dahira\Services\DahiraAccess;

class ContributionScheduleResource extends Resource
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

    protected static function dahiraScope(): string
    {
        return 'plan';
    }

    protected static ?string $model = ContributionSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Dahira';

    protected static ?string $navigationLabel = 'Échéances';

    protected static ?string $modelLabel = 'échéance';

    protected static ?string $pluralModelLabel = 'Échéances';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->label('Statut')
                ->options([
                    'due' => 'À régler',
                    'overdue' => 'En retard',
                    'processing' => 'Paiement en cours',
                    'paid' => 'Réglée',
                    'waived' => 'Dispensée',
                ])
                ->required(),
            Forms\Components\DatePicker::make('due_on')->label('Date d’échéance')->required(),
            Forms\Components\TextInput::make('amount_minor')->label('Montant (XOF)')->numeric()->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('plan.name_i18n.fr')->label('Plan'),
            Tables\Columns\TextColumn::make('membership.user.email')->label('Membre'),
            Tables\Columns\TextColumn::make('due_on')->label('Date d’échéance')->date(),
            Tables\Columns\TextColumn::make('amount_minor')->label('Montant (XOF)'),
            Tables\Columns\TextColumn::make('status')
                ->label('Statut')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'overdue' => 'En retard',
                    'processing' => 'Paiement en cours',
                    'paid' => 'Réglée',
                    'waived' => 'Dispensée',
                    default => 'À régler',
                }),
        ])->actions([
            Tables\Actions\Action::make('cash')
                ->label('Encaisser')
                ->visible(fn (ContributionSchedule $record) => $record->status !== 'paid')
                ->requiresConfirmation()
                ->action(function (ContributionSchedule $record) {
                    app(ContributionRecorder::class)->record($record, auth()->user(), 'cash');
                    Notification::make()->title('Cotisation encaissée')->success()->send();
                }),
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContributionSchedules::route('/'),
            'edit' => Pages\EditContributionSchedule::route('/{record}/edit'),
        ];
    }
}
