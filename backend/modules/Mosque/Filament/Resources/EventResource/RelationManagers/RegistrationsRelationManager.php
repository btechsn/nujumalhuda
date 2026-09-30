<?php

namespace Modules\Mosque\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Mosque\Models\EventRegistration;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Participants';

    protected static ?string $modelLabel = 'participant';

    protected static ?string $pluralModelLabel = 'participants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('participant_name')
                    ->label('Nom complet')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('participant_phone')
                    ->label('Téléphone')
                    ->tel()
                    ->required()
                    ->maxLength(20),
                Forms\Components\Select::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'confirmed' => 'Confirmé',
                        'cancelled' => 'Annulé',
                        'attended' => 'Présent',
                    ])
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('participant_name')
            ->columns([
                Tables\Columns\TextColumn::make('metadata.first_name')
                    ->label('Prénom')
                    ->placeholder('-')
                    ->searchable(query: function ($query, string $search) {
                        $query->where('metadata->first_name', 'ilike', "%{$search}%")
                            ->orWhere('participant_name', 'ilike', "%{$search}%");
                    }),
                Tables\Columns\TextColumn::make('metadata.last_name')
                    ->label('Nom')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('participant_name')
                    ->label('Nom complet')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('participant_phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'danger' => 'cancelled',
                        'info' => 'attended',
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'confirmed' => 'Confirmé',
                        'cancelled' => 'Annulé',
                        'attended' => 'Présent',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('confirmation_code')
                    ->label('Code')
                    ->copyable(),
                Tables\Columns\TextColumn::make('registered_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Statut')
                    ->options([
                        'pending' => 'En attente',
                        'confirmed' => 'Confirmé',
                        'cancelled' => 'Annulé',
                        'attended' => 'Présent',
                    ]),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Confirmer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (EventRegistration $record) => $record->isPending())
                    ->requiresConfirmation()
                    ->action(fn (EventRegistration $record) => $record->confirm()),
                Tables\Actions\Action::make('cancel')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (EventRegistration $record) => ! $record->isCancelled())
                    ->requiresConfirmation()
                    ->action(fn (EventRegistration $record) => $record->cancel()),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('registered_at', 'desc')
            ->emptyStateHeading('Aucun participant')
            ->emptyStateDescription('Les inscriptions faites sur le site apparaîtront ici.');
    }
}
