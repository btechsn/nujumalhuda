<?php

namespace Modules\Academics\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Modules\Academics\Filament\Resources\QuizTopicResource\Pages;
use Modules\Academics\Models\Quiz;
use Modules\Academics\Models\QuizTopic;

class QuizTopicResource extends Resource
{
    public static function canViewAny(): bool
    {
        return \Modules\Core\Support\PanelAccess::allows('academics');
    }

    protected static ?string $model = QuizTopic::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Suivi pédagogique';

    protected static ?string $navigationLabel = 'Thèmes';

    protected static ?string $modelLabel = 'thème';

    protected static ?string $pluralModelLabel = 'thèmes';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Nom')
                ->schema([
                    Forms\Components\TextInput::make('name_i18n.fr')
                        ->label('Nom (français)')
                        ->required()
                        ->maxLength(120)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, callable $set, string $operation): void {
                            if ($operation !== 'create') {
                                return;
                            }

                            $set('slug', Str::slug((string) $state));
                        }),
                    Forms\Components\TextInput::make('name_i18n.en')
                        ->label('Nom (anglais)')
                        ->maxLength(120),
                    Forms\Components\TextInput::make('name_i18n.ar')
                        ->label('Nom (arabe)')
                        ->maxLength(120),
                    Forms\Components\TextInput::make('slug')
                        ->label('Identifiant')
                        ->required()
                        ->maxLength(80)
                        ->unique(ignoreRecord: true)
                        ->disabled(fn (string $operation): bool => $operation === 'edit')
                        ->dehydrated()
                        ->helperText('Utilisé par les quizz. Il ne change plus après la création.'),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_i18n.fr')
                    ->label('Nom')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->label('Identifiant'),
                Tables\Columns\TextColumn::make('quizzes_count')
                    ->counts('quizzes')
                    ->label('Quizz'),
            ])
            ->defaultSort('sort')
            ->reorderable('sort')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (QuizTopic $record, Tables\Actions\DeleteAction $action): void {
                        if (Quiz::query()->where('topic', $record->slug)->exists()) {
                            Notification::make()
                                ->title('Ce thème est utilisé par des quizz')
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizTopics::route('/'),
            'create' => Pages\CreateQuizTopic::route('/create'),
            'edit' => Pages\EditQuizTopic::route('/{record}/edit'),
        ];
    }
}
