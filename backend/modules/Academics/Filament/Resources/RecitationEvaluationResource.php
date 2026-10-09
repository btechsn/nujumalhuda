<?php

namespace Modules\Academics\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Academics\Filament\Resources\RecitationEvaluationResource\Pages;
use Modules\Academics\Models\HifzMilestone;
use Modules\Academics\Models\RecitationEvaluation;
use Modules\Academics\Models\StudentProgress;
use Modules\Core\Models\User;
use Modules\Education\Models\Enrollment;
use Modules\Education\Models\Teacher;
use Modules\Live\Models\LiveStream;

class RecitationEvaluationResource extends Resource
{
    protected static ?string $model = RecitationEvaluation::class;

    protected static ?string $navigationIcon = 'heroicon-o-microphone';

    protected static ?string $navigationGroup = 'Suivi pédagogique';

    protected static ?string $navigationLabel = 'Évaluations';

    protected static ?string $modelLabel = 'évaluation de récitation';

    protected static ?string $pluralModelLabel = 'évaluations de récitation';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('notice')
                ->hiddenLabel()
                ->columnSpanFull()
                ->content('Note d’un enseignant après avoir écouté un élève réciter. Chaque note va de 1 (à revoir) à 5 (excellent). Ce n’est ni une attestation ni une ijaza.'),
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
                ->required(),
            Forms\Components\Select::make('teacher_id')
                ->label('Enseignant')
                ->options(fn (): array => self::userOptions(
                    User::query()->whereIn('id', Teacher::query()->select('id')),
                ))
                ->searchable()
                ->preload()
                ->required(),
            Forms\Components\Select::make('milestone_id')
                ->label('Palier récité')
                ->options(fn (): array => HifzMilestone::query()
                    ->orderBy('display_order')
                    ->get()
                    ->mapWithKeys(fn (HifzMilestone $milestone): array => [
                        $milestone->id => StudentProgressResource::milestoneLabel($milestone),
                    ])
                    ->all())
                ->searchable()
                ->placeholder('Aucun palier')
                ->helperText('La portion récitée, par exemple Juz Amma. Laissez vide si la récitation ne correspond pas à un palier.'),
            Forms\Components\Select::make('live_stream_id')
                ->label('Séance en direct')
                ->options(fn (): array => self::streamOptions())
                ->searchable()
                ->placeholder('Aucune')
                ->helperText('Seulement si la récitation a eu lieu pendant une émission en direct.'),
            Forms\Components\Select::make('memorization')
                ->label('Mémorisation')
                ->options(self::scoreOptions())
                ->required()
                ->helperText('L’élève se souvient-il du passage ?'),
            Forms\Components\Select::make('tajwid')
                ->label('Tajwid')
                ->options(self::scoreOptions())
                ->required()
                ->helperText('Les règles de lecture sont-elles respectées ?'),
            Forms\Components\Select::make('fluency')
                ->label('Fluidité')
                ->options(self::scoreOptions())
                ->required()
                ->helperText('La récitation est-elle posée, sans hésitation ?'),
            Forms\Components\Textarea::make('comments')
                ->label('Commentaire')
                ->rows(4)
                ->columnSpanFull(),
            Forms\Components\DateTimePicker::make('evaluated_at')
                ->label('Date de l’écoute')
                ->default(now())
                ->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('student.first_name')
                    ->label('Élève')
                    ->formatStateUsing(fn (mixed $state, RecitationEvaluation $record): string => StudentProgressResource::personLabel($record->student)),
                Tables\Columns\TextColumn::make('teacher.first_name')
                    ->label('Enseignant')
                    ->formatStateUsing(fn (mixed $state, RecitationEvaluation $record): string => StudentProgressResource::personLabel($record->teacher)),
                Tables\Columns\TextColumn::make('memorization')->label('Mémorisation'),
                Tables\Columns\TextColumn::make('tajwid')->label('Tajwid'),
                Tables\Columns\TextColumn::make('fluency')->label('Fluidité'),
                Tables\Columns\TextColumn::make('evaluated_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('evaluated_at', 'desc')
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecitationEvaluations::route('/'),
            'create' => Pages\CreateRecitationEvaluation::route('/create'),
            'edit' => Pages\EditRecitationEvaluation::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function scoreOptions(): array
    {
        return [
            1 => '1 — à revoir',
            2 => '2 — fragile',
            3 => '3 — correct',
            4 => '4 — bien',
            5 => '5 — excellent',
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
            ->mapWithKeys(fn (User $user): array => [$user->getKey() => StudentProgressResource::personLabel($user)])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public static function streamOptions(): array
    {
        return LiveStream::query()
            ->orderByDesc('scheduled_at')
            ->limit(50)
            ->get()
            ->mapWithKeys(function (LiveStream $stream): array {
                $title = is_array($stream->title) ? ($stream->title['fr'] ?? $stream->title['en'] ?? 'Séance') : 'Séance';
                $when = $stream->scheduled_at?->timezone('Africa/Dakar')->format('d/m/Y H:i');

                return [$stream->getKey() => $when ? $title.' — '.$when : $title];
            })
            ->all();
    }
}
