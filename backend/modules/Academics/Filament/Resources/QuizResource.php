<?php

namespace Modules\Academics\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Academics\Filament\Resources\QuizResource\Pages;
use Modules\Academics\Models\Quiz;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'Suivi pédagogique';

    protected static ?string $navigationLabel = 'Quiz';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\Select::make('topic')->options([
                'tajwid' => 'Tajwid',
                'vocabulary' => 'Vocabulaire',
            ])->required(),
            Forms\Components\TextInput::make('title_i18n.fr')->label('Titre (français)')->required(),
            Forms\Components\TextInput::make('title_i18n.en')->label('Title'),
            Forms\Components\TextInput::make('title_i18n.ar')->label('العنوان'),
            Forms\Components\Textarea::make('questions')
                ->required()
                ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : $state)
                ->dehydrateStateUsing(fn ($state) => json_decode($state, true))
                ->helperText('JSON : [{ "prompt_i18n": {"fr": "..."}, "choices": ["a","b"], "correct_index": 0 }]'),
            Forms\Components\Toggle::make('is_published')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title_i18n.fr')->label('Titre'),
            Tables\Columns\TextColumn::make('topic')->badge(),
            Tables\Columns\IconColumn::make('is_published')->boolean(),
            Tables\Columns\TextColumn::make('attempts_count')->counts('attempts')->label('Tentatives'),
        ])->actions([Tables\Actions\EditAction::make()]);
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
