<?php

namespace Modules\News\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\News\Models\ArticleComment;

class CommentResource extends Resource
{
    protected static ?string $model = ArticleComment::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationGroup = 'Actualités';
    protected static ?string $navigationLabel = 'Commentaires';
    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() > 0 ? 'warning' : null;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Commentaire')
                    ->schema([
                        Forms\Components\Select::make('article_id')
                            ->label('Article')
                            ->relationship('article', 'title_i18n->fr')
                            ->required()
                            ->disabled(),
                        
                        Forms\Components\Select::make('user_id')
                            ->label('Utilisateur')
                            ->relationship('user', 'name')
                            ->required()
                            ->disabled(),
                        
                        Forms\Components\Textarea::make('content')
                            ->label('Contenu')
                            ->required()
                            ->rows(4)
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Modération')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'En attente',
                                'approved' => 'Approuvé',
                                'rejected' => 'Refusé',
                                'flagged' => 'Signalé',
                                'spam' => 'Spam',
                            ])
                            ->required()
                            ->default('pending'),
                        
                        Forms\Components\Textarea::make('moderation_reason')
                            ->label('Raison de modération')
                            ->rows(3)
                            ->helperText('Expliquez pourquoi le commentaire est refusé ou signalé')
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('reports_count')
                            ->label('Nombre de signalements')
                            ->numeric()
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('article.title_i18n.fr')
                    ->label('Article')
                    ->searchable()
                    ->limit(30)
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('content')
                    ->label('Contenu')
                    ->limit(50)
                    ->wrap(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => ['rejected', 'spam'],
                        'gray' => 'flagged',
                    ]),
                
                Tables\Columns\TextColumn::make('spam_score')
                    ->label('Score spam')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('reports_count')
                    ->label('Signalements')
                    ->sortable()
                    ->badge()
                    ->color(fn($record) => $record->reports_count > 0 ? 'danger' : 'secondary'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'En attente',
                        'approved' => 'Approuvé',
                        'rejected' => 'Refusé',
                        'flagged' => 'Signalé',
                        'spam' => 'Spam',
                    ])
                    ->default('pending'),
                Tables\Filters\SelectFilter::make('article_id')
                    ->relationship('article', 'title_i18n->fr')
                    ->label('Article'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'approved')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->approve(auth()->user());
                        
                        Notification::make()
                            ->success()
                            ->title('Commentaire approuvé')
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject')
                    ->label('Refuser')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status !== 'rejected')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Raison du refus')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->reject(auth()->user(), $data['reason']);
                        
                        Notification::make()
                            ->warning()
                            ->title('Commentaire refusé')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('approve_all')
                        ->label('Approuver sélectionnés')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->approve(auth()->user());
                            }
                            
                            Notification::make()
                                ->success()
                                ->title('Commentaires approuvés')
                                ->send();
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\News\Filament\Resources\CommentResource\Pages\ListComments::route('/'),
            'edit' => \Modules\News\Filament\Resources\CommentResource\Pages\EditComment::route('/{record}/edit'),
        ];
    }
}
