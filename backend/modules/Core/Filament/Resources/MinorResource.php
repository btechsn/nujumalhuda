<?php

declare(strict_types=1);

namespace Modules\Core\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Actions\AnonymizeMinorAction;
use Modules\Core\Filament\Resources\MinorResource\Pages;
use Modules\Core\Models\MinorConsent;
use Modules\Core\Models\User;

/**
 * Cahier des charges, §8.3 : consentements parentaux par usage, et droit à
 * l'effacement. Liste uniquement les utilisateurs pour lesquels un tuteur
 * est déclaré (donc présumés mineurs sur la plateforme) ; ce n'est pas un
 * écran de création de compte.
 */
class MinorResource extends Resource
{
    public static function canViewAny(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('compliance');
    }

    protected static ?string $model = User::class;

    protected static ?string $slug = 'eleves-mineurs';

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Conformité & RGPD';

    protected static ?string $navigationLabel = 'Élèves mineurs';

    protected static ?string $modelLabel = 'élève mineur';

    protected static ?string $pluralModelLabel = 'Élèves mineurs';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('guardians')
            ->with(['minorConsent', 'guardians.guardian']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('identity')
                ->label('Élève')
                ->content(fn (?User $record): string => $record ? self::personLabel($record) : '—'),
            Forms\Components\Placeholder::make('guardians_list')
                ->label('Tuteur(s)')
                ->content(fn (?User $record): string => $record ? self::guardianNames($record) : '—'),

            Forms\Components\Section::make('Consentement parental, par usage')
                ->description(
                    'Cahier des charges §8.3 : sans case cochée ici, la diffusion '
                    .'correspondante reste techniquement bloquée, quelle que soit '
                    .'l\'action tentée ailleurs dans l\'administration.'
                )
                ->schema([
                    Forms\Components\Toggle::make('gallery_image_consent')
                        ->label('Photo de l\'élève en galerie / médiathèque')
                        ->default(false),
                    Forms\Components\Toggle::make('recitation_audio_consent')
                        ->label('Voix de l\'élève en récitation diffusée publiquement')
                        ->default(false),
                    Forms\Components\Toggle::make('public_name_consent')
                        ->label('Nom de l\'élève sur une attestation ou un ijaza publié')
                        ->default(false),
                    Forms\Components\Textarea::make('consent_notes')
                        ->label('Référence du consentement écrit')
                        ->helperText('Exemple : « Formulaire papier signé le 03/10/2026 par Mme Diop, tutrice. »')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            Forms\Components\Placeholder::make('consent_meta')
                ->label('Dernier enregistrement')
                ->content(fn (?User $record): string => self::consentMeta($record)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Élève')
                    ->formatStateUsing(fn (mixed $state, User $record): string => self::personLabel($record))
                    ->searchable(['first_name', 'last_name']),
                Tables\Columns\TextColumn::make('guardians_list')
                    ->label('Tuteur(s)')
                    ->state(fn (User $record): string => self::guardianNames($record)),
                Tables\Columns\IconColumn::make('minorConsent.gallery_image_consent')
                    ->label('Galerie')
                    ->boolean(),
                Tables\Columns\IconColumn::make('minorConsent.recitation_audio_consent')
                    ->label('Récitation')
                    ->boolean(),
                Tables\Columns\IconColumn::make('minorConsent.public_name_consent')
                    ->label('Nom public')
                    ->boolean(),
                Tables\Columns\TextColumn::make('anonymized_at')
                    ->label('Anonymisé le')
                    ->dateTime('d/m/Y')
                    ->placeholder('—'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('anonymize')
                    ->label('Anonymiser')
                    ->icon('heroicon-o-eye-slash')
                    ->color('danger')
                    ->visible(fn (User $record): bool => $record->anonymized_at === null)
                    ->requiresConfirmation()
                    ->modalHeading('Anonymiser cet élève')
                    ->modalDescription(
                        'Droit à l\'effacement (§8.3) : le nom, l\'e-mail, le téléphone et '
                        .'tous les consentements sont effacés définitivement. Les '
                        .'inscriptions, attestations et progressions restent, mais ne '
                        .'désignent plus personne d\'identifiable. Cette action est irréversible.'
                    )
                    ->modalSubmitActionLabel('Anonymiser définitivement')
                    ->action(function (User $record): void {
                        app(AnonymizeMinorAction::class)->execute($record);
                        Notification::make()->title('Élève anonymisé')->success()->send();
                    }),
            ])
            ->emptyStateHeading('Aucun élève mineur déclaré')
            ->emptyStateDescription('Un élève apparaît ici dès qu\'un tuteur lui est rattaché (module Tutelles).');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMinors::route('/'),
            'edit' => Pages\EditMinor::route('/{record}/edit'),
        ];
    }

    public static function personLabel(User $user): string
    {
        $name = $user->fullName();

        return $name !== '' ? $name : ($user->email ?: (string) $user->phone);
    }

    public static function guardianNames(User $user): string
    {
        $names = $user->guardians
            ->map(fn ($guardianship) => $guardianship->guardian ? self::personLabel($guardianship->guardian) : null)
            ->filter()
            ->unique()
            ->implode(', ');

        return $names !== '' ? $names : '—';
    }

    public static function consentMeta(?User $record): string
    {
        /** @var MinorConsent|null $consent */
        $consent = $record?->minorConsent;
        if (!$consent || !$consent->consented_at) {
            return 'Aucun consentement enregistré pour l\'instant.';
        }

        $by = $consent->consentedBy ? self::personLabel($consent->consentedBy) : 'un membre du secrétariat';

        return 'Enregistré le '.$consent->consented_at->format('d/m/Y à H:i').' par '.$by.'.';
    }
}
