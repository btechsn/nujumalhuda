<?php

namespace Modules\Academics\Filament\Resources\QuizResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Academics\Models\QuizAttempt;

class AttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'attempts';

    protected static ?string $title = 'Tentatives';

    protected static ?string $modelLabel = 'tentative';

    protected static ?string $pluralModelLabel = 'tentatives';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('score')->disabled(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('completed_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('–'),
                Tables\Columns\TextColumn::make('participant_name')
                    ->label('Participant')
                    ->searchable()
                    ->formatStateUsing(fn (mixed $state, QuizAttempt $record): string => $record->user?->fullName() ?: (filled($state) ? (string) $state : '—')),
                Tables\Columns\TextColumn::make('participant_phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->placeholder('–'),
                Tables\Columns\TextColumn::make('score')
                    ->label('Score')
                    ->formatStateUsing(fn (mixed $state, QuizAttempt $record): string => $record->score.' / '.$record->total),
                Tables\Columns\IconColumn::make('passed')
                    ->label('Réussi')
                    ->boolean(),
            ])
            ->defaultSort('completed_at', 'desc')
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
