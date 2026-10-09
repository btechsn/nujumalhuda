<?php

namespace Modules\Academics\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Modules\Academics\Filament\Resources\QuizResource\Pages;
use Modules\Academics\Filament\Resources\QuizResource\RelationManagers;
use Modules\Academics\Models\Quiz;
use Modules\Academics\Models\QuizTopic;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Suivi pédagogique';

    protected static ?string $navigationLabel = 'Quizz';

    protected static ?string $modelLabel = 'quizz';

    protected static ?string $pluralModelLabel = 'quizz';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Quizz')
                ->schema([
                    Forms\Components\TextInput::make('title_i18n.fr')
                        ->label('Titre (français)')
                        ->required()
                        ->maxLength(180)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, callable $set, string $operation): void {
                            if ($operation !== 'create') {
                                return;
                            }

                            $set('slug', Str::slug((string) $state));
                        }),
                    Forms\Components\TextInput::make('title_i18n.en')->label('Titre (anglais)')->maxLength(180),
                    Forms\Components\TextInput::make('title_i18n.ar')->label('العنوان')->maxLength(180),
                    Forms\Components\TextInput::make('slug')
                        ->label('Identifiant')
                        ->required()
                        ->maxLength(80)
                        ->unique(ignoreRecord: true)
                        ->helperText('Se remplit à partir du titre français.'),
                    Forms\Components\Select::make('topic')
                        ->label('Thème')
                        ->options(fn (): array => self::topicOptions())
                        ->searchable()
                        ->required()
                        ->helperText('Ajoutez un thème dans Suivi pédagogique → Thèmes.'),
                    Forms\Components\Toggle::make('is_published')
                        ->label('Publié sur le site')
                        ->default(false),
                ])
                ->columns(2),
            Forms\Components\Repeater::make('questions')
                ->label('Questions')
                ->schema([
                    Forms\Components\TextInput::make('prompt_fr')
                        ->label('Question')
                        ->required()
                        ->maxLength(500)
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('prompt_en')
                        ->label('Question (anglais)')
                        ->maxLength(500),
                    Forms\Components\TextInput::make('prompt_ar')
                        ->label('السؤال')
                        ->maxLength(500),
                    Forms\Components\TextInput::make('choice_a')->label('Réponse A')->required()->maxLength(200),
                    Forms\Components\TextInput::make('choice_b')->label('Réponse B')->required()->maxLength(200),
                    Forms\Components\TextInput::make('choice_c')->label('Réponse C')->maxLength(200),
                    Forms\Components\TextInput::make('choice_d')->label('Réponse D')->maxLength(200),
                    Forms\Components\Select::make('correct')
                        ->label('Bonne réponse')
                        ->options([
                            'a' => 'A',
                            'b' => 'B',
                            'c' => 'C',
                            'd' => 'D',
                        ])
                        ->required()
                        ->default('a'),
                ])
                ->columns(2)
                ->minItems(1)
                ->defaultItems(1)
                ->collapsible()
                ->cloneable()
                ->addActionLabel('Ajouter une question')
                ->itemLabel(fn (array $state): ?string => filled($state['prompt_fr'] ?? null) ? (string) $state['prompt_fr'] : 'Nouvelle question')
                ->formatStateUsing(fn (mixed $state): array => self::questionsForForm($state))
                ->mutateDehydratedStateUsing(fn (?array $state): array => self::questionsForStorage(array_values($state ?? [])))
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title_i18n.fr')->label('Titre'),
            Tables\Columns\TextColumn::make('topic')
                ->label('Thème')
                ->badge()
                ->formatStateUsing(fn (?string $state): string => self::topicOptions()[$state] ?? (string) $state),
            Tables\Columns\IconColumn::make('is_published')->label('Publié')->boolean(),
            Tables\Columns\TextColumn::make('attempts_count')->counts('attempts')->label('Tentatives'),
            Tables\Columns\TextColumn::make('passed_attempts_count')
                ->counts([
                    'attempts as passed_attempts_count' => fn (Builder $query) => $query->where('passed', true),
                ])
                ->label('Réussites'),
        ])
            ->defaultSort('created_at', 'desc')
            ->actions([Tables\Actions\EditAction::make()]);
    }

    /**
     * @return array<string, string>
     */
    public static function topicOptions(): array
    {
        return QuizTopic::query()
            ->orderBy('sort')
            ->orderBy('name_i18n->fr')
            ->get()
            ->mapWithKeys(fn (QuizTopic $topic): array => [$topic->slug => $topic->label()])
            ->all();
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function questionsForForm(mixed $state): array
    {
        if (! is_array($state)) {
            return [];
        }

        return array_map(function (mixed $question): array {
            $question = is_array($question) ? $question : [];
            if (array_key_exists('prompt_fr', $question) || array_key_exists('choice_a', $question)) {
                return $question;
            }

            $choices = array_values(is_array($question['choices'] ?? null) ? $question['choices'] : []);
            $letters = ['a', 'b', 'c', 'd'];
            $index = (int) ($question['correct_index'] ?? 0);

            return [
                'prompt_fr' => $question['prompt_i18n']['fr'] ?? '',
                'prompt_en' => $question['prompt_i18n']['en'] ?? '',
                'prompt_ar' => $question['prompt_i18n']['ar'] ?? '',
                'choice_a' => $choices[0] ?? '',
                'choice_b' => $choices[1] ?? '',
                'choice_c' => $choices[2] ?? '',
                'choice_d' => $choices[3] ?? '',
                'correct' => $letters[$index] ?? 'a',
            ];
        }, array_values($state));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function questionsForStorage(mixed $state): array
    {
        if (! is_array($state)) {
            return [];
        }

        $questions = [];

        foreach ($state as $item) {
            if (! is_array($item)) {
                continue;
            }

            $slots = [
                'a' => trim((string) ($item['choice_a'] ?? '')),
                'b' => trim((string) ($item['choice_b'] ?? '')),
                'c' => trim((string) ($item['choice_c'] ?? '')),
                'd' => trim((string) ($item['choice_d'] ?? '')),
            ];
            $choices = [];
            $indexes = [];

            foreach ($slots as $letter => $choice) {
                if ($choice === '') {
                    continue;
                }

                $indexes[$letter] = count($choices);
                $choices[] = $choice;
            }

            $prompt = ['fr' => trim((string) ($item['prompt_fr'] ?? ''))];
            if (filled($item['prompt_en'] ?? null)) {
                $prompt['en'] = trim((string) $item['prompt_en']);
            }
            if (filled($item['prompt_ar'] ?? null)) {
                $prompt['ar'] = trim((string) $item['prompt_ar']);
            }

            if ($prompt['fr'] === '' || count($choices) < 2) {
                continue;
            }

            $selected = is_string($item['correct'] ?? null) ? $item['correct'] : 'a';

            $questions[] = [
                'prompt_i18n' => $prompt,
                'choices' => $choices,
                'correct_index' => $indexes[$selected] ?? 0,
            ];
        }

        return $questions;
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttemptsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }
}
