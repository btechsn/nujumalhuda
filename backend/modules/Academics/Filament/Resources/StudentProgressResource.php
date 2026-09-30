<?php

namespace Modules\Academics\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Academics\Filament\Resources\StudentProgressResource\Pages;
use Modules\Academics\Models\StudentProgress;

class StudentProgressResource extends Resource
{
    protected static ?string $model = StudentProgress::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Suivi pédagogique';

    protected static ?string $navigationLabel = 'Progression';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('student_id')->relationship('student', 'email')->searchable()->required(),
            Forms\Components\Select::make('milestone_id')->relationship('milestone', 'slug')->required(),
            Forms\Components\Select::make('status')->options([
                'not_started' => 'Non commencé',
                'in_progress' => 'En cours',
                'completed' => 'Atteint',
            ])->required()->helperText('Passer à « Atteint » délivre une attestation de niveau. Cela ne crée pas d\'ijaza.'),
            Forms\Components\Textarea::make('teacher_note'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('student.email')->label('Élève')->searchable(),
            Tables\Columns\TextColumn::make('milestone.slug')->label('Palier'),
            Tables\Columns\TextColumn::make('status')->badge(),
            Tables\Columns\TextColumn::make('certificate.verification_code')->label('Attestation'),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStudentProgress::route('/'),
            'create' => Pages\CreateStudentProgress::route('/create'),
            'edit' => Pages\EditStudentProgress::route('/{record}/edit'),
        ];
    }
}
