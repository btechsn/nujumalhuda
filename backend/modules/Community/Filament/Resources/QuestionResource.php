<?php

namespace Modules\Community\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Community\Filament\Resources\QuestionResource\Pages;
use Modules\Community\Models\Question;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Questions aux enseignants';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('question_i18n.fr')->label('Question')->disabled(),
            Forms\Components\Select::make('teacher_id')->relationship('teacher', 'email')->searchable(),
            Forms\Components\Textarea::make('answer_i18n.fr')->label('Réponse (français)'),
            Forms\Components\Textarea::make('answer_i18n.ar')->label('الإجابة'),
            Forms\Components\Select::make('status')->options([
                'pending' => 'En attente',
                'answered' => 'Répondue',
                'published' => 'Publiée',
            ])->required(),
            Forms\Components\Toggle::make('is_public')->label('Visible dans la FAQ'),
            Forms\Components\DateTimePicker::make('answered_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('question_i18n.fr')->label('Question')->limit(60),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\IconColumn::make('is_public')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuestions::route('/'),
            'edit' => Pages\EditQuestion::route('/{record}/edit'),
        ];
    }
}
