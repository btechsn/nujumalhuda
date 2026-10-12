<?php

namespace Modules\Academics\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Academics\Filament\Resources\IjazaResource\Pages;
use Modules\Academics\Models\Ijaza;
use Modules\Academics\Models\StudentProgress;
use Modules\Core\Models\AuditLog;
use Modules\Core\Models\User;
use Modules\Education\Models\Enrollment;
use Modules\Education\Models\Teacher;

class IjazaResource extends Resource
{
    public static function canViewAny(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('academics');
    }

    protected static ?string $model = Ijaza::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Suivi pédagogique';

    protected static ?string $navigationLabel = 'Ijazas';

    protected static ?string $modelLabel = 'ijaza';

    protected static ?string $pluralModelLabel = 'ijazas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('notice')
                ->hiddenLabel()
                ->columnSpanFull()
                ->content('Une ijaza est accordée à la main par un enseignant nommé, avec sa chaîne de transmission. Elle n\'est jamais produite par le passage d\'un palier.'),
            Forms\Components\Select::make('teacher_id')
                ->label('Enseignant')
                ->options(fn (): array => self::userOptions(
                    User::query()->whereIn('id', Teacher::query()->select('id')),
                ))
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('student_id')
                ->label('Élève')
                ->options(fn (): array => self::userOptions(
                    User::query()->where(function (Builder $query): void {
                        $query->whereIn('id', Enrollment::query()->select('user_id'))
                            ->orWhereIn('id', StudentProgress::query()->select('student_id'));
                    }),
                ))
                ->searchable()
                ->preload()
                ->required()
                ->live(),
            Forms\Components\Textarea::make('scope_i18n.fr')
                ->label('Matière autorisée')
                ->helperText('Ce que l’élève a le droit de transmettre. Exemple : récitation du Coran selon Hafs, tajwid, ou Juz Amma.')
                ->required()
                ->columnSpanFull(),
            Forms\Components\Textarea::make('scope_i18n.ar')
                ->label('المجال')
                ->columnSpanFull(),
            Forms\Components\Textarea::make('sanad_i18n.fr')
                ->label('Chaîne de transmission')
                ->required()
                ->columnSpanFull(),
            Forms\Components\Textarea::make('sanad_i18n.ar')
                ->label('السند')
                ->required()
                ->columnSpanFull(),
            Forms\Components\DateTimePicker::make('signed_at')
                ->label('Date de signature')
                ->required(),
            Forms\Components\Toggle::make('is_public')
                ->label('Visible sur le site')
                ->default(false)
                ->helperText(fn (Get $get): string => self::consentHelperText($get('student_id'))),
        ])->columns(2);
    }

    /**
     * Verrou technique (cahier des charges §8.3, point 1) : le nom de
     * l'élève figure sur l'ijaza publiée ; sans consentement « nom public »
     * enregistré, la publication reste refusée.
     */
    public static function enforceConsent(array $data): array
    {
        if (!($data['is_public'] ?? false) || blank($data['student_id'] ?? null)) {
            return $data;
        }

        $student = User::find($data['student_id']);

        $allowed = $student && $student->hasConsent('public_name');

        if (!$allowed) {
            $data['is_public'] = false;
            Notification::make()
                ->title('Publication refusée')
                ->body('Le consentement « nom public » n\'est pas enregistré pour cet élève. L\'ijaza reste masquée du site.')
                ->warning()
                ->send();
        }

        AuditLog::log(
            $allowed ? 'ijaza.published' : 'ijaza.publication_refused',
            $student,
            context: ['student_id' => $data['student_id']],
        );

        return $data;
    }

    public static function consentHelperText(?string $studentId): string
    {
        if (blank($studentId)) {
            return 'Choisissez d\'abord un élève : la publication dépend de son consentement enregistré.';
        }

        $student = User::find($studentId);

        return $student && $student->hasConsent('public_name')
            ? 'Consentement enregistré : la publication est possible.'
            : 'Aucun consentement enregistré pour cet élève (Conformité & RGPD → Élèves mineurs). La publication sera automatiquement refusée.';
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('teacher.first_name')
                ->label('Enseignant')
                ->formatStateUsing(fn (mixed $state, Ijaza $record): string => self::personLabel($record->teacher))
                ->searchable(query: fn (Builder $query, string $search): Builder => self::wherePerson($query, 'teacher', $search)),
            Tables\Columns\TextColumn::make('student.first_name')
                ->label('Élève')
                ->formatStateUsing(fn (mixed $state, Ijaza $record): string => self::personLabel($record->student))
                ->searchable(query: fn (Builder $query, string $search): Builder => self::wherePerson($query, 'student', $search)),
            Tables\Columns\TextColumn::make('verification_code')
                ->label('Code')
                ->searchable()
                ->copyable(),
            Tables\Columns\TextColumn::make('signed_at')
                ->label('Signée le')
                ->dateTime('d/m/Y H:i')
                ->sortable(),
            Tables\Columns\IconColumn::make('is_public')
                ->label('Visible sur le site')
                ->boolean(),
        ])
            ->defaultSort('signed_at', 'desc')
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIjazas::route('/'),
            'create' => Pages\CreateIjaza::route('/create'),
            'edit' => Pages\EditIjaza::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function userOptions(Builder $query): array
    {
        return $query
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn (User $user): array => [$user->getKey() => self::personLabel($user)])
            ->all();
    }

    public static function personLabel(?User $user): string
    {
        if (! $user) {
            return '—';
        }

        $name = $user->fullName();

        return $name !== '' ? $name : ($user->email ?: (string) $user->phone);
    }

    public static function wherePerson(Builder $query, string $relation, string $search): Builder
    {
        $like = '%'.$search.'%';

        return $query->whereHas($relation, function (Builder $person) use ($like): void {
            $person->where('first_name', 'ilike', $like)
                ->orWhere('last_name', 'ilike', $like)
                ->orWhereRaw("concat(first_name, ' ', last_name) ilike ?", [$like]);
        });
    }
}
