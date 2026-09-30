<?php

namespace Modules\Education\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\Education\Events\EnrollmentApproved;
use Modules\Education\Events\EnrollmentRejected;
use Modules\Education\Models\Enrollment;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Éducation';
    protected static ?string $navigationLabel = 'Inscriptions';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Élève et promotion')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Élève')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('promotion_id')
                            ->label('Promotion')
                            ->relationship('promotion', 'code')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Statut du dossier')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'En attente',
                                'approved' => 'Approuvé',
                                'rejected' => 'Refusé',
                                'active' => 'Actif',
                                'completed' => 'Terminé',
                                'withdrawn' => 'Retiré',
                            ])
                            ->required()
                            ->default('pending'),
                        
                        Forms\Components\Textarea::make('review_notes')
                            ->label('Notes de révision')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                
                Forms\Components\Section::make('Contact d\'urgence')
                    ->schema([
                        Forms\Components\TextInput::make('emergency_contact_name')
                            ->label('Nom du contact')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('emergency_contact_phone')
                            ->label('Téléphone')
                            ->tel()
                            ->maxLength(20),
                        
                        Forms\Components\TextInput::make('emergency_contact_relation')
                            ->label('Lien de parenté')
                            ->maxLength(100),
                    ])->columns(3),
                
                Forms\Components\Section::make('Paiement')
                    ->schema([
                        Forms\Components\Toggle::make('fees_paid')
                            ->label('Frais payés'),
                        
                        Forms\Components\Select::make('payment_id')
                            ->label('Référence paiement')
                            ->relationship('payment', 'id')
                            ->searchable(),
                    ])->columns(2),
                
                Forms\Components\Section::make('Progression')
                    ->schema([
                        Forms\Components\TextInput::make('attendance_rate')
                            ->label('Taux de présence (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->disabled(),
                        
                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date de début effective'),
                        
                        Forms\Components\DatePicker::make('completion_date')
                            ->label('Date de fin'),
                        
                        Forms\Components\Textarea::make('completion_notes')
                            ->label('Notes de fin de formation')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Élève')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('promotion.code')
                    ->label('Promotion')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Statut')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                        'primary' => 'active',
                        'secondary' => 'completed',
                        'gray' => 'withdrawn',
                    ]),
                
                Tables\Columns\IconColumn::make('fees_paid')
                    ->label('Payé')
                    ->boolean(),
                
                Tables\Columns\TextColumn::make('attendance_rate')
                    ->label('Présence')
                    ->suffix('%')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Soumis le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status'),
                Tables\Filters\SelectFilter::make('promotion_id')
                    ->relationship('promotion', 'code')
                    ->label('Promotion'),
                Tables\Filters\TernaryFilter::make('fees_paid')
                    ->label('Frais payés'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approuver')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->approve(auth()->user());
                        
                        // Déclencher l'événement pour envoyer l'email
                        event(new EnrollmentApproved($record));
                        
                        Notification::make()
                            ->success()
                            ->title('Inscription approuvée')
                            ->body('Un email de confirmation a été envoyé à l\'étudiant.')
                            ->send();
                    }),
                
                Tables\Actions\Action::make('reject')
                    ->label('Refuser')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Raison du refus')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        $record->reject(auth()->user(), $data['reason']);
                        
                        // Déclencher l'événement pour envoyer l'email
                        event(new EnrollmentRejected($record));
                        
                        Notification::make()
                            ->warning()
                            ->title('Inscription refusée')
                            ->body('Un email a été envoyé à l\'étudiant.')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('submitted_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => \Modules\Education\Filament\Resources\EnrollmentResource\Pages\ListEnrollments::route('/'),
            'create' => \Modules\Education\Filament\Resources\EnrollmentResource\Pages\CreateEnrollment::route('/create'),
            'edit' => \Modules\Education\Filament\Resources\EnrollmentResource\Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }
}
