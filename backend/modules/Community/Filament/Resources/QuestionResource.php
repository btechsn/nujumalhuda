<?php

namespace Modules\Community\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Community\Filament\Resources\QuestionResource\Pages;
use Modules\Community\Models\Question;
use Modules\Core\Models\User;
use Modules\Education\Models\Teacher;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'FAQ';

    protected static ?string $modelLabel = 'question';

    protected static ?string $pluralModelLabel = 'FAQ';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('question_i18n.fr')
                ->label('Question (français)')
                ->required()
                ->rows(3),
            Forms\Components\Textarea::make('question_i18n.en')
                ->label('Question (anglais)')
                ->rows(3),
            Forms\Components\Textarea::make('question_i18n.ar')
                ->label('Question (arabe)')
                ->rows(3),
            Forms\Components\Select::make('topics')
                ->label('Sujets')
                ->multiple()
                ->options([
                    'ramadan' => 'Ramadan',
                    'priere' => 'Prière',
                    'famille' => 'Famille',
                    'zakat' => 'Zakat',
                    'fiqh' => 'Fiqh',
                    'inscription' => 'Inscription',
                    'programmes' => 'Programmes',
                    'dahira' => 'Dahira',
                    'communaute' => 'Communauté',
                ]),
            Forms\Components\Select::make('teacher_id')
                ->label('Enseignant qui répond')
                ->options(fn (): array => User::query()
                    ->whereIn('id', Teacher::query()->select('id'))
                    ->orderBy('last_name')
                    ->orderBy('first_name')
                    ->get()
                    ->mapWithKeys(fn (User $user): array => [$user->getKey() => self::personLabel($user)])
                    ->all())
                ->searchable()
                ->preload()
                ->helperText('Une réponse visible sur le site doit porter le nom de cet enseignant.'),
            Forms\Components\Textarea::make('answer_i18n.fr')->label('Réponse (français)')->rows(4),
            Forms\Components\Textarea::make('answer_i18n.en')->label('Réponse (anglais)')->rows(4),
            Forms\Components\Textarea::make('answer_i18n.ar')->label('Réponse (arabe)')->rows(4),
            Forms\Components\Select::make('status')
                ->label('Statut')
                ->options([
                    'pending' => 'En attente',
                    'answered' => 'Répondue',
                    'published' => 'Publiée',
                ])
                ->default('published')
                ->required()
                ->helperText('Seules les questions publiées et visibles apparaissent sur la page Communauté.'),
            Forms\Components\Toggle::make('is_public')
                ->label('Visible sur le site')
                ->default(true)
                ->helperText('Sans enseignant nommé, une réponse ne peut pas être publiée.'),
            Forms\Components\DateTimePicker::make('answered_at')->label('Répondu le'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('question_i18n.fr')->label('Question')->limit(60)->searchable(),
            Tables\Columns\TextColumn::make('teacher_name')
                ->label('Enseignant')
                ->getStateUsing(fn (Question $record): string => $record->teacherName() ?? '—'),
            Tables\Columns\TextColumn::make('status')
                ->label('Statut')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'answered' => 'Répondue',
                    'published' => 'Publiée',
                    default => 'En attente',
                }),
            Tables\Columns\IconColumn::make('is_public')->label('Visible')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    /**
     * Sans enseignant nommé, la réponse reste hors du site.
     */
    public static function enforceAttribution(array $data): array
    {
        $answer = trim((string) ($data['answer_i18n']['fr'] ?? ''));
        if ($answer !== '' && blank($data['answered_at'] ?? null)) {
            $data['answered_at'] = now();
        }

        $wantsPublic = (bool) ($data['is_public'] ?? false) && ($data['status'] ?? '') === 'published';
        if ($answer !== '' && $wantsPublic && blank($data['teacher_id'] ?? null)) {
            $data['is_public'] = false;
            $data['status'] = 'answered';
            Notification::make()
                ->title('Publication refusée')
                ->body('Une réponse publique doit être attribuée à un enseignant nommé.')
                ->warning()
                ->send();
        }

        return $data;
    }

    public static function personLabel(User $user): string
    {
        $name = $user->fullName();

        return $name !== '' ? $name : ($user->email ?: (string) $user->phone);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
