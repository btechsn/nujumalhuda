<?php

namespace Modules\Academics\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Academics\Filament\Resources\RecitationEvaluationResource\Pages;
use Modules\Academics\Models\RecitationEvaluation;

class RecitationEvaluationResource extends Resource
{
    protected static ?string $model = RecitationEvaluation::class;

    protected static ?string $navigationIcon = 'heroicon-o-microphone';

    protected static ?string $navigationGroup = 'Suivi pédagogique';

    protected static ?string $navigationLabel = 'Évaluations';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')->relationship('student', 'email')->searchable()->required(),
            Forms\Components\Select::make('teacher_id')->relationship('teacher', 'email')->searchable()->required(),
            Forms\Components\Select::make('milestone_id')->relationship('milestone', 'slug'),
            Forms\Components\TextInput::make('live_stream_id')->label('Séance diffusée (identifiant)')->helperText('Lien optionnel vers une session du module Live.'),
            Forms\Components\TextInput::make('memorization')->numeric()->minValue(1)->maxValue(5)->required(),
            Forms\Components\TextInput::make('tajwid')->numeric()->minValue(1)->maxValue(5)->required(),
            Forms\Components\TextInput::make('fluency')->numeric()->minValue(1)->maxValue(5)->required(),
            Forms\Components\Textarea::make('comments'),
            Forms\Components\DateTimePicker::make('evaluated_at')->default(now())->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('student.email')->label('Élève'),
            Tables\Columns\TextColumn::make('teacher.email')->label('Enseignant'),
            Tables\Columns\TextColumn::make('memorization'),
            Tables\Columns\TextColumn::make('tajwid'),
            Tables\Columns\TextColumn::make('fluency'),
            Tables\Columns\TextColumn::make('evaluated_at')->dateTime(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRecitationEvaluations::route('/'),
            'create' => Pages\CreateRecitationEvaluation::route('/create'),
            'edit' => Pages\EditRecitationEvaluation::route('/{record}/edit'),
        ];
    }
}
