<?php

declare(strict_types=1);

namespace Modules\Core\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\Contracts\PaymentGateway;
use Modules\Core\Data\PaymentData;
use Modules\Core\Enums\PaymentStatus;
use Modules\Core\Models\Payment;

class OrangeMoneyPaymentGateway implements PaymentGateway
{
    public function initiate(PaymentData $payment): array
    {
        $merchant = (string) config('services.orange_money.merchant_key');
        $token = $this->token();
        if ($merchant === '' || $token === null || !$payment->id) {
            return ['ok' => false, 'error' => 'orange_money_not_configured'];
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(20)
            ->post(rtrim((string) config('services.orange_money.base_url'), '/') . '/webpayment', [
                'merchant_key' => $merchant,
                'currency' => $payment->currency,
                'order_id' => $payment->id,
                'amount' => $payment->amount_minor,
                'return_url' => config('services.orange_money.return_url'),
                'cancel_url' => config('services.orange_money.cancel_url'),
                'notif_url' => config('services.orange_money.notif_url'),
                'lang' => 'fr',
                'reference' => 'Nujum Al-Huda',
            ]);

        if (!$response->successful()) {
            Log::warning('Orange Money a refusé le paiement', ['status' => $response->status()]);

            return ['ok' => false, 'error' => 'orange_money_http_' . $response->status(), 'raw' => $response->json()];
        }

        $body = $response->json() ?? [];

        return [
            'ok' => true,
            'transaction_id' => $body['pay_token'] ?? null,
            'checkout_url' => $body['payment_url'] ?? null,
            'raw' => $body,
        ];
    }

    public function verify(string $transactionId): array
    {
        $token = $this->token();
        if ($token === null || $transactionId === '') {
            return ['status' => 'unknown'];
        }

        $payment = Payment::query()->where('gateway_transaction_id', $transactionId)->first();

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(20)
            ->post(rtrim((string) config('services.orange_money.base_url'), '/') . '/transactionstatus', [
                'order_id' => $payment?->id ?? $transactionId,
                'amount' => $payment?->amount_minor,
                'pay_token' => $transactionId,
            ]);

        if (!$response->successful()) {
            return ['status' => 'unknown', 'raw' => $response->json()];
        }

        $body = $response->json() ?? [];
        $status = strtoupper((string) ($body['status'] ?? ''));

        return [
            'status' => in_array($status, ['SUCCESS', 'SUCCESSFUL', 'COMPLETED'], true) ? 'completed' : 'pending',
            'transaction_id' => $transactionId,
            'raw' => $body,
        ];
    }

    public function cancel(string $transactionId): bool
    {
        return $this->postAction('/cancel', $transactionId, null);
    }

    public function refund(string $transactionId, ?int $amount = null): bool
    {
        $payment = Payment::query()->where('gateway_transaction_id', $transactionId)->first();
        $ok = $this->postAction('/refund', $transactionId, $amount ?? $payment?->amount_minor);
        if ($ok && $payment) {
            $payment->update([
                'status' => PaymentStatus::REFUNDED,
                'refunded_at' => now(),
            ]);
        }

        return $ok;
    }

    private function postAction(string $path, string $transactionId, ?int $amount): bool
    {
        $token = $this->token();
        $payment = Payment::query()->where('gateway_transaction_id', $transactionId)->first();
        if ($token === null || $transactionId === '' || (string) config('services.orange_money.merchant_key') === '') {
            return false;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(20)
            ->post(rtrim((string) config('services.orange_money.base_url'), '/') . $path, [
                'merchant_key' => config('services.orange_money.merchant_key'),
                'order_id' => $payment?->id ?? $transactionId,
                'pay_token' => $transactionId,
                'amount' => $amount,
            ]);

        if (!$response->successful()) {
            Log::warning('Orange Money a refusé ' . $path, ['status' => $response->status()]);

            return false;
        }

        return true;
    }

    public function getName(): string
    {
        return 'orange_money';
    }

    private function token(): ?string
    {
        $id = (string) config('services.orange_money.client_id');
        $secret = (string) config('services.orange_money.client_secret');
        if ($id === '' || $secret === '') {
            return null;
        }

        $response = Http::asForm()
            ->withBasicAuth($id, $secret)
            ->timeout(15)
            ->post('https://api.orange.com/oauth/v3/token', [
                'grant_type' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            return null;
        }

        return $response->json('access_token');
    }
}
