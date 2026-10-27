<?php

declare(strict_types=1);

namespace Modules\Core\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Core\Enums\MembershipStatus;
use Modules\Core\Models\Membership;
use Modules\Core\Models\Organization;
use Modules\Core\Models\Role;

class MembershipsRelationManager extends RelationManager
{
    protected static string $relationship = 'memberships';

    protected static ?string $title = 'Adhésions et rôles';

    protected static ?string $modelLabel = 'adhésion';

    protected static ?string $pluralModelLabel = 'adhésions';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('organization_id')
                ->label('Organisation')
                ->options(fn (): array => Organization::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->required(),
            Forms\Components\Select::make('role_id')
                ->label('Rôle')
                ->options(fn (): array => Role::query()
                    ->orderBy('display_name')
                    ->get()
                    ->mapWithKeys(fn (Role $role): array => [$role->id => $role->display_name ?: $role->name])
                    ->all())
                ->searchable()
                ->required(),
            Forms\Components\Select::make('status')
                ->label('Statut')
                ->options(collect(MembershipStatus::cases())->mapWithKeys(
                    fn (MembershipStatus $status): array => [$status->value => $status->label()],
                ))
                ->default(MembershipStatus::ACTIVE->value)
                ->required(),
            Forms\Components\DateTimePicker::make('joined_at')
                ->label('Date d’adhésion')
                ->default(now())
                ->required(),
            Forms\Components\DateTimePicker::make('expires_at')
                ->label('Expiration')
                ->nullable(),
            Forms\Components\Textarea::make('notes')
                ->label('Notes')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('organization.name')->label('Organisation')->searchable(),
                Tables\Columns\TextColumn::make('role.display_name')
                    ->label('Rôle')
                    ->formatStateUsing(fn (mixed $state, Membership $record): string => $state ?: ($record->role?->name ?? '—')),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state instanceof MembershipStatus ? $state->label() : (MembershipStatus::tryFrom((string) $state)?->label() ?? (string) $state))
                    ->color(fn (mixed $state): string => $state === MembershipStatus::ACTIVE || $state === MembershipStatus::ACTIVE->value ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('joined_at')->label('Depuis le')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('expires_at')->label('Expire le')->date('d/m/Y')->placeholder('—'),
            ])
            ->headerActions([Tables\Actions\CreateAction::make()->label('Ajouter une adhésion')])
            ->actions([
                Tables\Actions\EditAction::make()->label('Modifier'),
                Tables\Actions\DeleteAction::make()->label('Retirer l’adhésion'),
            ]);
    }
}
