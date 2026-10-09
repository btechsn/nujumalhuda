<?php

namespace Modules\Education\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Core\Enums\PaymentMethod;
use Modules\Core\Enums\PaymentStatus;
use Modules\Core\Models\Payment;
use Modules\Education\Events\EnrollmentApproved;
use Modules\Education\Events\EnrollmentRejected;
use Modules\Education\Models\Enrollment;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Éducation';
    protected static ?string $navigationLabel = 'Inscriptions';

    protected static ?string $modelLabel = 'inscription';

    protected static ?string $pluralModelLabel = 'inscriptions';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Élève')
                    ->description('Saisissez la personne à inscrire. Il n\'y a pas d\'écran des comptes : l\'élève est créé ici.')
                    ->schema([
                        Forms\Components\TextInput::make('first_name')
                            ->label('Prénom')
                            ->required()
                            ->maxLength(80),
                        Forms\Components\TextInput::make('last_name')
                            ->label('Nom')
                            ->required()
                            ->maxLength(80),
                        Forms\Components\TextInput::make('phone')
                            ->label('Téléphone')
                            ->tel()
                            ->required()
                            ->maxLength(20),
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Promotion')
                    ->schema([
                        Forms\Components\Select::make('promotion_id')
                            ->label('Promotion')
                            ->relationship('promotion', 'code')
                            ->required()
                            ->searchable()
                            ->preload(),
                    ]),
                
                Forms\Components\Section::make('Statut du dossier')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Statut')
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
                    ->description('Cochez les frais payés, puis indiquez le mode, le montant et le numéro de reçu.')
                    ->schema([
                        Forms\Components\Toggle::make('fees_paid')
                            ->label('Frais payés')
                            ->columnSpanFull(),
                        Forms\Components\Select::make('payment_method')
                            ->label('Mode de paiement')
                            ->options(collect(PaymentMethod::cases())->mapWithKeys(
                                fn (PaymentMethod $method): array => [$method->value => $method->label()],
                            )->all())
                            ->native(false),
                        Forms\Components\TextInput::make('payment_amount')
                            ->label('Montant')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('FCFA'),
                        Forms\Components\TextInput::make('payment_reference')
                            ->label('Référence')
                            ->maxLength(120)
                            ->helperText('Numéro Wave, Orange Money ou reçu.')
                            ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('user.first_name')
                    ->label('Élève')
                    ->formatStateUsing(fn (mixed $state, Enrollment $record): string => $record->user?->fullName() ?: '—')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('user', function (Builder $user) use ($search): void {
                            $user->where('first_name', 'ilike', "%{$search}%")
                                ->orWhere('last_name', 'ilike', "%{$search}%");
                        });
                    }),
                
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

    /**
     * @param  array<string, mixed>  $data
     * @return array{method: ?string, reference: ?string, amount: int|string|null, fees_paid: bool}
     */
    public static function paymentInputFrom(array $data): array
    {
        return [
            'method' => filled($data['payment_method'] ?? null) ? (string) $data['payment_method'] : null,
            'reference' => filled($data['payment_reference'] ?? null) ? (string) $data['payment_reference'] : null,
            'amount' => $data['payment_amount'] ?? null,
            'fees_paid' => (bool) ($data['fees_paid'] ?? false),
        ];
    }

    /**
     * @param  array{method?: string|null, reference?: string|null, amount?: int|string|null, fees_paid?: bool}  $input
     */
    public static function syncFeePayment(Enrollment $enrollment, array $input): void
    {
        if (! ($input['fees_paid'] ?? false)) {
            return;
        }

        $method = filled($input['method'] ?? null) ? (string) $input['method'] : PaymentMethod::CASH->value;
        $reference = trim((string) ($input['reference'] ?? ''));
        $amount = max(0, (int) ($input['amount'] ?? 0));

        $payment = $enrollment->payment;
        $attributes = [
            'payable_type' => $enrollment->getMorphClass(),
            'payable_id' => $enrollment->id,
            'user_id' => $enrollment->user_id,
            'method' => $method,
            'status' => PaymentStatus::COMPLETED,
            'currency' => 'XOF',
            'amount_minor' => $amount,
            'gateway_transaction_id' => $reference !== '' ? $reference : null,
            'completed_at' => $payment?->completed_at ?? now(),
        ];

        if ($payment) {
            $payment->update($attributes);
        } else {
            $payment = Payment::query()->create($attributes);
            $enrollment->update(['payment_id' => $payment->id]);
        }
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
