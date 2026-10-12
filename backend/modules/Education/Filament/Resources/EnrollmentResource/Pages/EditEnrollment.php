<?php

namespace Modules\Education\Filament\Resources\EnrollmentResource\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;
use Modules\Core\Models\User;
use Modules\Education\Filament\Resources\EnrollmentResource;
use Modules\Education\Models\EnrollmentDocument;

class EditEnrollment extends EditRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected static ?string $title = 'Modifier l\'inscription';

    /** @var array{method: ?string, reference: ?string, amount: int|string|null, fees_paid: bool} */
    protected array $paymentInput = [
        'method' => null,
        'reference' => null,
        'amount' => null,
        'fees_paid' => false,
    ];

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $user = $this->getRecord()->user;
        $payment = $this->getRecord()->payment;
        $data['first_name'] = $user?->first_name;
        $data['last_name'] = $user?->last_name;
        $data['email'] = $user?->email;
        $data['phone'] = $user?->phone;
        $data['payment_method'] = $payment?->method?->value;
        $data['payment_reference'] = $payment?->gateway_transaction_id;
        $data['payment_amount'] = $payment?->amount_minor;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = $this->getRecord()->user;
        $phone = preg_replace('/\s+/', '', (string) ($data['phone'] ?? '')) ?: null;
        $email = filled($data['email'] ?? null) ? strtolower(trim((string) $data['email'])) : null;

        if ($user && $phone && User::query()->where('phone', $phone)->whereKeyNot($user->id)->exists()) {
            throw ValidationException::withMessages([
                'data.phone' => 'Ce téléphone est déjà utilisé par une autre personne.',
            ]);
        }

        if ($user && $email && User::query()->where('email', $email)->whereKeyNot($user->id)->exists()) {
            throw ValidationException::withMessages([
                'data.email' => 'Cet e-mail est déjà utilisé par une autre personne.',
            ]);
        }

        $user?->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $phone,
            'email' => $email,
        ]);

        $this->paymentInput = EnrollmentResource::paymentInputFrom($data);
        unset(
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['payment_method'],
            $data['payment_reference'],
            $data['payment_amount'],
        );

        return $data;
    }

    protected function afterSave(): void
    {
        EnrollmentResource::syncFeePayment($this->record, $this->paymentInput);
    }

    public function verifyDocument(string $documentId): void
    {
        $this->ownedDocument($documentId)->verify();
        $this->record->unsetRelation('uploadedDocuments');

        Notification::make()
            ->success()
            ->title('Pièce vérifiée')
            ->send();
    }

    public function rejectDocument(string $documentId, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '') {
            Notification::make()
                ->warning()
                ->title('Indiquez un motif de refus')
                ->send();

            return;
        }

        $this->ownedDocument($documentId)->reject($reason);
        $this->record->unsetRelation('uploadedDocuments');

        Notification::make()
            ->warning()
            ->title('Pièce refusée')
            ->send();
    }

    private function ownedDocument(string $documentId): EnrollmentDocument
    {
        return $this->getRecord()->uploadedDocuments()->whereKey($documentId)->firstOrFail();
    }
}
