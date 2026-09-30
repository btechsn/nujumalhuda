<?php

namespace Modules\Academics\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Academics\Filament\Resources\IjazaResource\Pages;
use Modules\Academics\Models\Ijaza;

class IjazaResource extends Resource
{
    protected static ?string $model = Ijaza::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'Suivi pédagogique';

    protected static ?string $navigationLabel = 'Ijazas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Placeholder::make('warning')
                ->content('Une ijaza est accordée à la main par un enseignant nommé, avec sa chaîne de transmission. Elle n\'est jamais produite par le passage d\'un palier.'),
            Forms\Components\Select::make('teacher_id')->relationship('teacher', 'email')->searchable()->required(),
            Forms\Components\Select::make('student_id')->relationship('student', 'email')->searchable()->required(),
            Forms\Components\Textarea::make('scope_i18n.fr')->label('Portée (français)')->required(),
            Forms\Components\Textarea::make('scope_i18n.ar')->label('المجال'),
            Forms\Components\Textarea::make('sanad_i18n.fr')->label('Chaîne de transmission')->required(),
            Forms\Components\Textarea::make('sanad_i18n.ar')->label('السند')->required(),
            Forms\Components\DateTimePicker::make('signed_at')->required(),
            Forms\Components\Toggle::make('is_public')->default(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('teacher.email')->label('Enseignant'),
            Tables\Columns\TextColumn::make('student.email')->label('Élève'),
            Tables\Columns\TextColumn::make('signed_at')->dateTime(),
            Tables\Columns\IconColumn::make('is_public')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIjazas::route('/'),
            'create' => Pages\CreateIjaza::route('/create'),
            'edit' => Pages\EditIjaza::route('/{record}/edit'),
        ];
    }
}
