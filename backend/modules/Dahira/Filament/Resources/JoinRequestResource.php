<?php

namespace Modules\Dahira\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Dahira\Filament\Resources\JoinRequestResource\Pages;
use Modules\Dahira\Models\DahiraJoinRequest;
use Modules\Dahira\Services\JoinRequestService;

class JoinRequestResource extends Resource
{
    protected static ?string $model = DahiraJoinRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationGroup = 'Dahira';

    protected static ?string $navigationLabel = 'Demandes d\'adhésion';

    protected static ?string $modelLabel = 'Demande d\'adhésion';

    protected static ?string $pluralModelLabel = 'Demandes d\'adhésion';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('dahira_group_id')
                ->label('Dahira')
                ->relationship('group', 'id')
                ->getOptionLabelFromRecordUsing(fn ($record) => $record->name_i18n['fr'] ?? $record->id)
                ->disabled(),
            Forms\Components\TextInput::make('first_name')->label('Prénom')->disabled(),
            Forms\Components\TextInput::make('last_name')->label('Nom')->disabled(),
            Forms\Components\TextInput::make('phone')->label('Téléphone')->disabled(),
            Forms\Components\Textarea::make('message')->label('Message')->disabled(),
            Forms\Components\TextInput::make('status')->label('Statut')->disabled(),
            Forms\Components\Textarea::make('review_note')->label('Note de validation'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group.name_i18n.fr')->label('Dahira')->searchable(),
                Tables\Columns\TextColumn::make('last_name')->label('Nom')->searchable(),
                Tables\Columns\TextColumn::make('first_name')->label('Prénom')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Téléphone')->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'En attente',
                        'approved' => 'Acceptée',
                        'rejected' => 'Refusée',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Reçue le')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('reviewed_at')->label('Traitée le')->dateTime()->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'En attente',
                    'approved' => 'Acceptée',
                    'rejected' => 'Refusée',
                ]),
            ])
            ->actions([
                Tables\Actions\Action::make('approve')
                    ->label('Accepter')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (DahiraJoinRequest $record) => $record->status === 'pending')
                    ->form([
                        Forms\Components\Textarea::make('review_note')->label('Note (optionnel)'),
                    ])
                    ->action(function (DahiraJoinRequest $record, array $data, JoinRequestService $service) {
                        $service->approve($record, auth()->user(), $data['review_note'] ?? null);
                        Notification::make()->title('Demande acceptée')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Refuser')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (DahiraJoinRequest $record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('review_note')->label('Motif (optionnel)'),
                    ])
                    ->action(function (DahiraJoinRequest $record, array $data, JoinRequestService $service) {
                        $service->reject($record, auth()->user(), $data['review_note'] ?? null);
                        Notification::make()->title('Demande refusée')->warning()->send();
                    }),
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJoinRequests::route('/'),
            'view' => Pages\ViewJoinRequest::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
