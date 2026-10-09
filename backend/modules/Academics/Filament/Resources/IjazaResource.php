<?php

namespace Modules\Academics\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Academics\Filament\Resources\IjazaResource\Pages;
use Modules\Academics\Models\Ijaza;
use Modules\Academics\Models\StudentProgress;
use Modules\Core\Models\User;
use Modules\Education\Models\Enrollment;
use Modules\Education\Models\Teacher;

class IjazaResource extends Resource
{
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
                ->required(),
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
                ->default(false),
        ])->columns(2);
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
