<?php

declare(strict_types=1);

namespace Modules\Live\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\User;
use Modules\Live\Filament\Resources\RecitationSessionResource\Pages;
use Modules\Live\Models\RecitationSession;

/**
 * Cahier des charges, §8.3, point 2 : diffusion par défaut restreinte, le
 * passage en public exige un consentement enregistré sur la fiche de
 * l'élève (voir Core → Conformité & RGPD → Élèves mineurs).
 */
class RecitationSessionResource extends Resource
{
    protected static ?string $model = RecitationSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-musical-note';

    protected static ?string $navigationGroup = 'Direct';

    protected static ?string $navigationLabel = 'Séances de récitation';

    protected static ?string $modelLabel = 'séance de récitation';

    protected static ?string $pluralModelLabel = 'Séances de récitation';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->label('Élève')
                ->options(fn (): array => self::userOptions())
                ->searchable()
                ->preload()
                ->live()
                ->required(),
            Forms\Components\Select::make('teacher_id')
                ->label('Enseignant')
                ->options(fn (): array => self::userOptions())
                ->searchable()
                ->preload(),
            Forms\Components\TextInput::make('title')
                ->label('Titre')
                ->required()
                ->maxLength(180),
            Forms\Components\DateTimePicker::make('starts_at')
                ->label('Démarre le'),
            Forms\Components\Select::make('status')
                ->label('Statut')
                ->options([
                    'scheduled' => 'Programmée',
                    'live' => 'En direct',
                    'ended' => 'Terminée',
                ])
                ->default('scheduled')
                ->required(),
            Forms\Components\Select::make('visibility')
                ->label('Diffusion')
                ->options([
                    'restricted' => 'Restreinte (enseignants et tuteurs)',
                    'public' => 'Publique',
                ])
                ->default('restricted')
                ->required()
                ->helperText(fn (Get $get): string => self::consentHelperText($get('student_id')))
                ->live(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Titre')->searchable(),
                Tables\Columns\TextColumn::make('student.first_name')
                    ->label('Élève')
                    ->formatStateUsing(fn (mixed $state, RecitationSession $record): string => self::personLabel($record->student)),
                Tables\Columns\TextColumn::make('teacher.first_name')
                    ->label('Enseignant')
                    ->formatStateUsing(fn (mixed $state, RecitationSession $record): string => self::personLabel($record->teacher)),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'live' => 'En direct',
                        'ended' => 'Terminée',
                        default => 'Programmée',
                    }),
                Tables\Columns\TextColumn::make('visibility')
                    ->label('Diffusion')
                    ->badge()
                    ->color(fn (?string $state): string => $state === 'public' ? 'success' : 'gray')
                    ->formatStateUsing(fn (?string $state): string => $state === 'public' ? 'Publique' : 'Restreinte'),
                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Démarre le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('starts_at', 'desc')
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecitationSessions::route('/'),
            'create' => Pages\CreateRecitationSession::route('/create'),
            'edit' => Pages\EditRecitationSession::route('/{record}/edit'),
        ];
    }

    /**
     * Verrou technique, pas seulement une case à cocher dans le formulaire :
     * si l'élève n'a pas de consentement « voix en récitation diffusée »
     * enregistré, la visibilité est ramenée à « restricted » quoi qu'il
     * arrive, et l'administrateur en est averti.
     */
    public static function enforceConsent(array $data): array
    {
        if (($data['visibility'] ?? 'restricted') !== 'public') {
            return $data;
        }

        $student = filled($data['student_id'] ?? null) ? User::find($data['student_id']) : null;

        $allowed = $student && $student->hasConsent('recitation_audio');

        if (!$allowed) {
            $data['visibility'] = 'restricted';
            Notification::make()
                ->title('Diffusion publique refusée')
                ->body('Le consentement « voix en récitation diffusée » n\'est pas enregistré pour cet élève. La séance reste restreinte aux enseignants et tuteurs.')
                ->warning()
                ->send();
        }

        AuditLog::log(
            $allowed ? 'recitation.published' : 'recitation.publication_refused',
            $student,
            context: ['student_id' => $data['student_id'] ?? null],
        );

        return $data;
    }

    public static function consentHelperText(?string $studentId): string
    {
        if (blank($studentId)) {
            return 'Choisissez d\'abord un élève : la diffusion publique dépend de son consentement enregistré.';
        }

        $student = User::find($studentId);
        if (!$student) {
            return '';
        }

        return $student->hasConsent('recitation_audio')
            ? 'Consentement enregistré : la diffusion publique est possible.'
            : 'Aucun consentement enregistré pour cet élève (Conformité & RGPD → Élèves mineurs). La diffusion publique sera automatiquement refusée.';
    }

    public static function userOptions(): array
    {
        return User::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit(200)
            ->get()
            ->mapWithKeys(fn (User $user): array => [$user->getKey() => self::personLabel($user)])
            ->all();
    }

    public static function personLabel(?User $user): string
    {
        if (!$user) {
            return '—';
        }

        $name = $user->fullName();

        return $name !== '' ? $name : ($user->email ?: (string) $user->phone);
    }
}
