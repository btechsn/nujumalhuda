<?php

declare(strict_types=1);

namespace Modules\Core\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\Contracts\PaymentGateway;
use Modules\Core\Data\PaymentData;
use Modules\Core\Enums\PaymentStatus;
use Modules\Core\Models\Payment;

class WavePaymentGateway implements PaymentGateway
{
    public function initiate(PaymentData $payment): array
    {
        $key = (string) config('services.wave.key');
        if ($key === '' || !$payment->id) {
            return ['ok' => false, 'error' => 'wave_not_configured'];
        }

        $response = Http::withToken($key)
            ->acceptJson()
            ->timeout(20)
            ->post(rtrim((string) config('services.wave.base_url'), '/') . '/v1/checkout/sessions', [
                'amount' => (string) $payment->amount_minor,
                'currency' => $payment->currency,
                'client_reference' => $payment->id,
                'success_url' => config('services.wave.success_url'),
                'error_url' => config('services.wave.error_url'),
            ]);

        if (!$response->successful()) {
            Log::warning('Wave a refusé la session', ['status' => $response->status()]);

            return ['ok' => false, 'error' => 'wave_http_' . $response->status(), 'raw' => $response->json()];
        }

        $body = $response->json() ?? [];

        return [
            'ok' => true,
            'transaction_id' => $body['id'] ?? null,
            'checkout_url' => $body['wave_launch_url'] ?? null,
            'raw' => $body,
        ];
    }

    public function verify(string $transactionId): array
    {
        $key = (string) config('services.wave.key');
        if ($key === '' || $transactionId === '') {
            return ['status' => 'unknown'];
        }

        $response = Http::withToken($key)
            ->acceptJson()
            ->timeout(20)
            ->get(rtrim((string) config('services.wave.base_url'), '/') . '/v1/checkout/sessions/' . $transactionId);

        if (!$response->successful()) {
            return ['status' => 'unknown', 'raw' => $response->json()];
        }

        $body = $response->json() ?? [];
        $paid = ($body['payment_status'] ?? '') === 'succeeded' || ($body['checkout_status'] ?? '') === 'complete';

        return [
            'status' => $paid ? 'completed' : 'pending',
            'transaction_id' => $body['id'] ?? $transactionId,
            'raw' => $body,
        ];
    }

    public function cancel(string $transactionId): bool
    {
        $key = (string) config('services.wave.key');
        if ($key === '' || $transactionId === '') {
            return false;
        }

        $response = Http::withToken($key)
            ->acceptJson()
            ->timeout(20)
            ->post(rtrim((string) config('services.wave.base_url'), '/') . '/v1/checkout/sessions/' . $transactionId . '/expire');

        return $response->successful();
    }

    public function refund(string $transactionId, ?int $amount = null): bool
    {
        $key = (string) config('services.wave.key');
        $payment = Payment::query()->where('gateway_transaction_id', $transactionId)->first();
        $amount ??= $payment?->amount_minor;
        if ($key === '' || $transactionId === '' || !$amount) {
            return false;
        }

        $response = Http::withToken($key)
            ->acceptJson()
            ->timeout(20)
            ->post(rtrim((string) config('services.wave.base_url'), '/') . '/v1/refunds', [
                'payment_id' => $transactionId,
                'amount' => (string) $amount,
                'currency' => $payment?->currency ?? 'XOF',
            ]);

        if (!$response->successful()) {
            Log::warning('Remboursement Wave refusé', ['status' => $response->status()]);

            return false;
        }

        $payment?->update([
            'status' => PaymentStatus::REFUNDED,
            'refunded_at' => now(),
            'gateway_response' => $response->json(),
        ]);

        return true;
    }

    public function getName(): string
    {
        return 'wave';
    }
}
