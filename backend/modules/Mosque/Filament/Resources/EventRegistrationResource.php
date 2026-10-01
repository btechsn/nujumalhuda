<?php

namespace Modules\Mosque\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Mosque\Filament\Resources\EventRegistrationResource\Pages;
use Modules\Mosque\Models\EventRegistration;

class EventRegistrationResource extends Resource
{
    protected static ?string $model = EventRegistration::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationLabel = 'Inscriptions événements';

    protected static ?string $navigationGroup = 'Zawiya';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Événement')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label('Événement')
                            ->relationship('event', 'id')
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->title_i18n['fr'] ?? 'Sans titre')
                            ->required()
                            ->searchable(),
                    ]),

                Forms\Components\Section::make('Participant')
                    ->schema([
                        Forms\Components\TextInput::make('participant_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('participant_email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('participant_phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(20),

                        Forms\Components\TextInput::make('number_of_attendees')
                            ->label('Nombre de participants')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->maxValue(10),
                    ])->columns(2),

                Forms\Components\Section::make('Détails')
                    ->schema([
                        Forms\Components\Textarea::make('message')
                            ->label('Message')
                            ->maxLength(1000)
                            ->rows(3),

                        Forms\Components\Select::make('status')
                            ->label('Statut')
                            ->options([
                                'pending' => 'En attente',
                                'confirmed' => 'Confirmé',
                                'cancelled' => 'Annulé',
                                'attended' => 'Présent',
                            ])
                            ->default('pending')
                            ->required(),

                        Forms\Components\TextInput::make('confirmation_code')
                            ->label('Code de confirmation')
                            ->maxLength(32)
                            ->disabled()
                            ->dehydrated(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('event.title_i18n.fr')
                    ->label('Événement')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('participant_name')
                    ->label('Nom complet')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('metadata.first_name')
                    ->label('Prénom')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('metadata.last_name')
                    ->label('Nom')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('participant_phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('participant_email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('number_of_attendees')
                    ->label('Participants')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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

                Tables\Columns\TextColumn::make('registered_at')
                    ->label('Date inscription')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('confirmation_code')
                    ->label('Code')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
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

                Tables\Filters\SelectFilter::make('event_id')
                    ->label('Événement')
                    ->relationship('event', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->title_i18n['fr'] ?? 'Sans titre'),
            ])
            ->actions([
                Tables\Actions\Action::make('confirm')
                    ->label('Confirmer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (EventRegistration $record) => $record->isPending())
                    ->requiresConfirmation()
                    ->action(fn (EventRegistration $record) => $record->confirm())
                    ->successNotificationTitle('Inscription confirmée'),

                Tables\Actions\Action::make('cancel')
                    ->label('Annuler')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (EventRegistration $record) => !$record->isCancelled())
                    ->requiresConfirmation()
                    ->action(fn (EventRegistration $record) => $record->cancel())
                    ->successNotificationTitle('Inscription annulée'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('confirm')
                    ->label('Confirmer')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each->confirm())
                    ->deselectRecordsAfterCompletion(),

                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('registered_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEventRegistrations::route('/'),
            'create' => Pages\CreateEventRegistration::route('/create'),
            'edit' => Pages\EditEventRegistration::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
