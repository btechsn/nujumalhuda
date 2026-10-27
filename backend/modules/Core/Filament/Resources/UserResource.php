<?php

declare(strict_types=1);

namespace Modules\Core\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Filament\Resources\UserResource\Pages;
use Modules\Core\Filament\Resources\UserResource\RelationManagers\MembershipsRelationManager;
use Modules\Core\Models\User;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'utilisateurs';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Utilisateurs';

    protected static ?string $modelLabel = 'utilisateur';

    protected static ?string $pluralModelLabel = 'utilisateurs';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['admin']) ?? false;
    }

    public static function canCreate(): bool
    {
        return self::canViewAny();
    }

    public static function canEdit($record): bool
    {
        return self::canViewAny();
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('memberships');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informations du compte')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('first_name')
                        ->label('Prénom')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\TextInput::make('last_name')
                        ->label('Nom')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\TextInput::make('email')
                        ->label('Adresse e-mail')
                        ->email()
                        ->requiredWithout('phone')
                        ->maxLength(255)
                        ->unique(table: 'users', column: 'email', ignoreRecord: true),
                    Forms\Components\TextInput::make('phone')
                        ->label('Téléphone')
                        ->tel()
                        ->requiredWithout('email')
                        ->maxLength(20)
                        ->unique(table: 'users', column: 'phone', ignoreRecord: true),
                    Forms\Components\Select::make('locale')
                        ->label('Langue')
                        ->options(['fr' => 'Français', 'en' => 'English', 'ar' => 'العربية'])
                        ->default('fr')
                        ->required(),
                    Forms\Components\TextInput::make('timezone')
                        ->label('Fuseau horaire')
                        ->default('Africa/Dakar')
                        ->required()
                        ->maxLength(50),
                    Forms\Components\TextInput::make('password')
                        ->label('Mot de passe')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->minLength(8)
                        ->confirmed()
                        ->autocomplete('new-password'),
                    Forms\Components\TextInput::make('password_confirmation')
                        ->label('Confirmer le mot de passe')
                        ->password()
                        ->revealable()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(false)
                        ->autocomplete('new-password'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Nom')
                    ->formatStateUsing(fn (mixed $state, User $record): string => $record->fullName())
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('disabled_at')
                    ->label('État du compte')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => $state ? 'Désactivé' : 'Actif')
                    ->color(fn (mixed $state): string => $state ? 'danger' : 'success'),
                Tables\Columns\TextColumn::make('memberships_count')
                    ->label('Adhésions')
                    ->counts('memberships')
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Dernière connexion')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Jamais'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('disabled_at')
                    ->label('État du compte')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereNotNull('disabled_at'),
                        false: fn (Builder $query): Builder => $query->whereNull('disabled_at'),
                        blank: fn (Builder $query): Builder => $query,
                    ),
                Tables\Filters\Filter::make('has_membership')
                    ->label('Avec adhésion')
                    ->query(fn (Builder $query): Builder => $query->has('memberships')),
                Tables\Filters\Filter::make('without_membership')
                    ->label('Sans adhésion')
                    ->query(fn (Builder $query): Builder => $query->doesntHave('memberships')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Gérer'),
                Tables\Actions\Action::make('toggle_account')
                    ->label(fn (User $record): string => $record->disabled_at ? 'Réactiver' : 'Désactiver')
                    ->icon(fn (User $record): string => $record->disabled_at ? 'heroicon-o-lock-open' : 'heroicon-o-no-symbol')
                    ->color(fn (User $record): string => $record->disabled_at ? 'success' : 'danger')
                    ->visible(fn (User $record): bool => auth()->id() !== $record->getKey())
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record): string => $record->disabled_at ? 'Réactiver ce compte ?' : 'Désactiver ce compte ?')
                    ->modalDescription(fn (User $record): string => $record->disabled_at
                        ? 'L’utilisateur pourra de nouveau se connecter.'
                        : 'L’utilisateur sera déconnecté de ses sessions mobiles et ne pourra plus se connecter.')
                    ->action(function (User $record): void {
                        self::toggleAccount($record);
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Aucun utilisateur')
            ->emptyStateDescription('Les comptes créés sur la plateforme apparaîtront ici.');
    }

    public static function getRelations(): array
    {
        return [MembershipsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    private static function toggleAccount(User $record): void
    {
        if ($record->disabled_at) {
            $record->forceFill(['disabled_at' => null])->save();

            \Filament\Notifications\Notification::make()
                ->title('Compte réactivé')
                ->success()
                ->send();

            return;
        }

        if ($record->memberships()
            ->where('status', 'active')
            ->whereHas('role', fn (Builder $query): Builder => $query->where('name', 'admin'))
            ->exists()) {
            $anotherAdminExists = User::query()
                ->whereNull('disabled_at')
                ->whereKeyNot($record->getKey())
                ->whereHas('memberships', fn (Builder $query): Builder => $query
                    ->where('status', 'active')
                    ->whereHas('role', fn (Builder $roleQuery): Builder => $roleQuery->where('name', 'admin')))
                ->exists();

            if (! $anotherAdminExists) {
                \Filament\Notifications\Notification::make()
                    ->title('Impossible de désactiver le dernier administrateur')
                    ->danger()
                    ->send();

                return;
            }
        }

        $record->forceFill(['disabled_at' => now()])->save();
        $record->tokens()->delete();

        \Filament\Notifications\Notification::make()
            ->title('Compte désactivé')
            ->success()
            ->send();
    }
}
