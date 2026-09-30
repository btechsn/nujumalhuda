<?php

namespace Modules\Live\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Live\Filament\Resources\LiveChatMessageResource\Pages;
use Modules\Live\Models\LiveChatMessage;
use Modules\Live\Services\LiveChatService;

class LiveChatMessageResource extends Resource
{
    protected static ?string $model = LiveChatMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Live Streaming';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Chat Moderation';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Message Details')
                    ->schema([
                        Forms\Components\Select::make('stream_id')
                            ->relationship('stream', 'id')
                            ->disabled(),

                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->disabled(),

                        Forms\Components\Textarea::make('message')
                            ->label('Message Content')
                            ->disabled()
                            ->rows(3),

                        Forms\Components\Select::make('status')
                            ->options([
                                'visible' => 'Visible',
                                'hidden' => 'Hidden',
                                'deleted' => 'Deleted',
                                'flagged' => 'Flagged',
                            ])
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Spam Analysis')
                    ->schema([
                        Forms\Components\TextInput::make('spam_score')
                            ->label('Spam Score')
                            ->disabled()
                            ->numeric()
                            ->suffix('/ 100'),

                        Forms\Components\TagsInput::make('spam_flags')
                            ->label('Spam Flags')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Moderation')
                    ->schema([
                        Forms\Components\Select::make('moderated_by')
                            ->relationship('moderator', 'name')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('moderated_at')
                            ->label('Moderated At')
                            ->disabled(),

                        Forms\Components\Textarea::make('moderation_reason')
                            ->label('Moderation Reason')
                            ->rows(2),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('stream.title.fr')
                    ->label('Stream')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->default('Guest'),

                Tables\Columns\TextColumn::make('message')
                    ->label('Message')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'visible',
                        'warning' => 'flagged',
                        'danger' => 'hidden',
                        'secondary' => 'deleted',
                    ]),

                Tables\Columns\TextColumn::make('spam_score')
                    ->label('Spam')
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state >= 70 => 'danger',
                        $state >= 50 => 'warning',
                        default => 'success',
                    })
                    ->formatStateUsing(fn ($state) => $state . '%'),

                Tables\Columns\TextColumn::make('type')
                    ->badge(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('moderator.name')
                    ->label('Moderated By')
                    ->default('-'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'visible' => 'Visible',
                        'hidden' => 'Hidden',
                        'deleted' => 'Deleted',
                        'flagged' => 'Flagged',
                    ])
                    ->default('flagged'),

                Tables\Filters\Filter::make('high_spam')
                    ->label('High Spam Score')
                    ->query(fn ($query) => $query->where('spam_score', '>=', 70)),

                Tables\Filters\SelectFilter::make('stream_id')
                    ->relationship('stream', 'id')
                    ->label('Stream'),

                Tables\Filters\Filter::make('unmoderated')
                    ->label('Unmoderated')
                    ->query(fn ($query) => $query->whereNull('moderated_by')),
            ])
            ->actions([
                Tables\Actions\Action::make('hide')
                    ->label('Hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $service = app(LiveChatService::class);
                        $service->moderateMessage($record, 'hide', auth()->user(), $data['reason']);

                        Notification::make()
                            ->title('Message hidden successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'flagged' || $record->status === 'visible'),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function ($record) {
                        $service = app(LiveChatService::class);
                        $service->moderateMessage($record, 'approve', auth()->user());

                        Notification::make()
                            ->title('Message approved successfully')
                            ->success()
                            ->send();
                    })
                    ->visible(fn ($record) => $record->status === 'flagged'),

                Tables\Actions\Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $service = app(LiveChatService::class);
                        $service->moderateMessage($record, 'delete', auth()->user(), $data['reason']);

                        Notification::make()
                            ->title('Message deleted successfully')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('hide_all')
                        ->label('Hide Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $service = app(LiveChatService::class);
                            foreach ($records as $record) {
                                $service->moderateMessage($record, 'hide', auth()->user(), 'Bulk moderation');
                            }

                            Notification::make()
                                ->title('Messages hidden successfully')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLiveChatMessages::route('/'),
            'edit' => Pages\EditLiveChatMessage::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'flagged')->count() ?: null;
    }
}
