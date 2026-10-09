<?php

namespace Modules\Education\Filament\Resources\EnrollmentResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;
use Modules\Core\Models\User;
use Modules\Education\Filament\Resources\EnrollmentResource;

class CreateEnrollment extends CreateRecord
{
    protected static string $resource = EnrollmentResource::class;

    protected static ?string $title = 'Nouvelle inscription';

    /** @var array{method: ?string, reference: ?string, amount: int|string|null, fees_paid: bool} */
    protected array $paymentInput = [
        'method' => null,
        'reference' => null,
        'amount' => null,
        'fees_paid' => false,
    ];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $phone = preg_replace('/\s+/', '', (string) ($data['phone'] ?? '')) ?: null;
        $email = filled($data['email'] ?? null) ? strtolower(trim((string) $data['email'])) : null;

        $user = $phone ? User::query()->where('phone', $phone)->first() : null;

        if ($user) {
            if ($email && User::query()->where('email', $email)->whereKeyNot($user->id)->exists()) {
                $email = $user->email;
            }

            $user->fill([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $email ?: $user->email,
            ])->save();
        } else {
            if ($email && User::query()->where('email', $email)->exists()) {
                $email = null;
            }

            $user = User::query()->create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $phone,
                'email' => $email,
                'password' => Str::password(32),
                'locale' => 'fr',
                'timezone' => 'Africa/Dakar',
            ]);
        }

        $data['user_id'] = $user->id;
        $data['submitted_at'] = now();
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

    protected function afterCreate(): void
    {
        EnrollmentResource::syncFeePayment($this->record, $this->paymentInput);
    }
}
