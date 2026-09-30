<?php

namespace Modules\Dahira\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Dahira\Filament\Resources\ContributionScheduleResource\Pages;
use Modules\Dahira\Models\ContributionSchedule;
use Modules\Dahira\Services\ContributionRecorder;

class ContributionScheduleResource extends Resource
{
    protected static ?string $model = ContributionSchedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Dahira';

    protected static ?string $navigationLabel = 'Échéances';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')->options([
                'due' => 'Due',
                'overdue' => 'En retard',
                'processing' => 'Paiement en cours',
                'paid' => 'Réglée',
                'waived' => 'Dispensée',
            ])->required(),
            Forms\Components\DatePicker::make('due_on')->required(),
            Forms\Components\TextInput::make('amount_minor')->numeric()->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('plan.name_i18n.fr')->label('Plan'),
            Tables\Columns\TextColumn::make('membership.user.email')->label('Membre'),
            Tables\Columns\TextColumn::make('due_on')->date(),
            Tables\Columns\TextColumn::make('amount_minor')->label('XOF'),
            Tables\Columns\TextColumn::make('status')->badge(),
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
