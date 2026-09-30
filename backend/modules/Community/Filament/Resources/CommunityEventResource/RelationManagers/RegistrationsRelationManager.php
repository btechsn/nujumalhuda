<?php

namespace Modules\Community\Filament\Resources\CommunityEventResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RegistrationsRelationManager extends RelationManager
{
    protected static string $relationship = 'registrations';

    protected static ?string $title = 'Présents inscrits';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('full_name')->label('Nom')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Téléphone')->searchable(),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('confirmation_code')->label('Code'),
                Tables\Columns\TextColumn::make('created_at')->label('Inscrit le')->dateTime(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
