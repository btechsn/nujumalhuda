<?php

namespace Modules\Dahira\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Dahira\Filament\Resources\MeetingResource\Pages;
use Modules\Dahira\Models\Meeting;
use Modules\Dahira\Services\DahiraNotifier;

class MeetingResource extends Resource
{
    protected static ?string $model = Meeting::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Dahira';

    protected static ?string $navigationLabel = 'Réunions';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('dahira_group_id')->relationship('group', 'id')->required(),
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\DateTimePicker::make('starts_at')->required(),
            Forms\Components\TextInput::make('location'),
            Forms\Components\Textarea::make('agenda'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('title')->searchable(),
            Tables\Columns\TextColumn::make('starts_at')->dateTime(),
            Tables\Columns\TextColumn::make('attendances_count')->counts('attendances')->label('Présences'),
            Tables\Columns\TextColumn::make('convened_at')->dateTime()->label('Convoquée'),
        ])->actions([
            Tables\Actions\Action::make('convene')
                ->label('Convoquer')
                ->requiresConfirmation()
                ->action(function (Meeting $record) {
                    $count = app(DahiraNotifier::class)->convene($record);
                    Notification::make()->title("Convocation envoyée à {$count} membre(s)")->success()->send();
                }),
            Tables\Actions\EditAction::make(),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeetings::route('/'),
            'create' => Pages\CreateMeeting::route('/create'),
            'edit' => Pages\EditMeeting::route('/{record}/edit'),
        ];
    }
}
