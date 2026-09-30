<?php

namespace Modules\Community\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Community\Filament\Resources\DiscussionMessageResource\Pages;
use Modules\Community\Models\DiscussionMessage;

class DiscussionMessageResource extends Resource
{
    protected static ?string $model = DiscussionMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationGroup = 'Communauté';

    protected static ?string $navigationLabel = 'Messages de discussion';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('body')->disabled(),
            Forms\Components\Select::make('status')->options([
                'visible' => 'Visible',
                'hidden' => 'Masqué',
            ])->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('discussion.title')->label('Espace'),
            Tables\Columns\TextColumn::make('user.email')->label('Auteur'),
            Tables\Columns\TextColumn::make('body')->limit(50),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiscussionMessages::route('/'),
            'edit' => Pages\EditDiscussionMessage::route('/{record}/edit'),
        ];
    }
}
