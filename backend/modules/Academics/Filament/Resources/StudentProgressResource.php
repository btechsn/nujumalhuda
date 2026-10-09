<?php

namespace Modules\Academics\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Modules\Academics\Filament\Resources\StudentProgressResource\Pages;
use Modules\Academics\Models\HifzMilestone;
use Modules\Academics\Models\StudentProgress;
use Modules\Core\Models\User;

class StudentProgressResource extends Resource
{
    protected static ?string $model = StudentProgress::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Suivi pédagogique';

    protected static ?string $navigationLabel = 'Progression des élèves';

    protected static ?string $modelLabel = 'progression';

    protected static ?string $pluralModelLabel = 'progressions';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')
                ->label('Élève')
                ->relationship(
                    'student',
                    modifyQueryUsing: fn (Builder $query) => $query->orderBy('last_name')->orderBy('first_name'),
                )
                ->getOptionLabelFromRecordUsing(fn (User $record): string => self::personLabel($record))
                ->searchable(['first_name', 'last_name'])
                ->preload()
                ->required()
                ->createOptionModalHeading('Nouvel élève')
                ->helperText('Choisissez un élève déjà inscrit, ou créez-le avec le bouton +.')
                ->createOptionForm([
                    Forms\Components\TextInput::make('first_name')
                        ->label('Prénom')
                        ->required()
                        ->maxLength(80),
                    Forms\Components\TextInput::make('last_name')
                        ->label('Nom')
                        ->required()
                        ->maxLength(80),
                    Forms\Components\TextInput::make('phone')
                        ->label('Téléphone')
                        ->tel()
                        ->required()
                        ->maxLength(20)
                        ->unique('users', 'phone'),
                    Forms\Components\TextInput::make('email')
                        ->label('E-mail')
                        ->email()
                        ->maxLength(255)
                        ->unique('users', 'email'),
                ])
                ->createOptionUsing(function (array $data): string {
                    $user = User::query()->create([
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'phone' => $data['phone'],
                        'email' => filled($data['email'] ?? null) ? $data['email'] : null,
                        'password' => Str::password(32),
                        'locale' => 'fr',
                        'timezone' => 'Africa/Dakar',
                    ]);

                    return $user->getKey();
                }),
            Forms\Components\Select::make('milestone_id')
                ->label('Palier de mémorisation')
                ->placeholder('Pas encore de palier')
                ->options(fn (): array => HifzMilestone::query()
                    ->orderBy('display_order')
                    ->get()
                    ->mapWithKeys(fn (HifzMilestone $milestone): array => [
                        $milestone->id => self::milestoneLabel($milestone),
                    ])
                    ->all())
                ->searchable()
                ->required()
                ->helperText('Portion du Coran validée, par exemple Juz Amma ou Cinq juz. Les paliers se gèrent dans Suivi pédagogique → Paliers de hifz.'),
            Forms\Components\Select::make('status')
                ->label('Statut')
                ->options(self::statusOptions())
                ->required()
                ->default('in_progress')
                ->helperText('« Atteint » délivre l’attestation affichée sur le site. Cela ne crée pas d’ijaza.'),
            Forms\Components\Textarea::make('teacher_note')
                ->label('Note de l’enseignant')
                ->rows(4),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.first_name')
                    ->label('Élève')
                    ->formatStateUsing(fn (mixed $state, StudentProgress $record): string => $record->student ? self::personLabel($record->student) : '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $like = '%'.$search.'%';

                        return $query->whereHas('student', function (Builder $student) use ($like): void {
                            $student->where('first_name', 'ilike', $like)
                                ->orWhere('last_name', 'ilike', $like)
                                ->orWhereRaw("concat(first_name, ' ', last_name) ilike ?", [$like]);
                        });
                    }),
                Tables\Columns\TextColumn::make('milestone.title_i18n')
                    ->label('Palier')
                    ->formatStateUsing(fn (mixed $state, StudentProgress $record): string => $record->milestone ? self::milestoneLabel($record->milestone) : '—'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn (mixed $state): string => self::statusOptions()[(string) $state] ?? (string) $state),
                Tables\Columns\TextColumn::make('certificate.verification_code')
                    ->label('Attestation')
                    ->placeholder('–'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentProgress::route('/'),
            'create' => Pages\CreateStudentProgress::route('/create'),
            'edit' => Pages\EditStudentProgress::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statusOptions(): array
    {
        return [
            'not_started' => 'Non commencé',
            'in_progress' => 'En cours',
            'completed' => 'Atteint',
        ];
    }

    public static function personLabel(User $user): string
    {
        $name = $user->fullName();

        return $name !== '' ? $name : ($user->email ?: (string) $user->phone);
    }

    public static function milestoneLabel(HifzMilestone $milestone): string
    {
        $title = $milestone->title_i18n['fr'] ?? $milestone->slug;
        $from = $milestone->from_juz;
        $to = $milestone->to_juz;
        if ($from && $to) {
            return $title.' (juz '.$from.' à '.$to.')';
        }

        return $title;
    }
}
