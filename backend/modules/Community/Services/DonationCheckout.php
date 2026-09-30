<?php

namespace Modules\Community\Services;

use Illuminate\Http\Request;
use Modules\Community\Models\Donation;
use Modules\Core\Data\PaymentData;
use Modules\Core\Enums\PaymentMethod;
use Modules\Core\Enums\PaymentStatus;
use Modules\Core\Models\Payment;
use Modules\Core\Payments\PaymentGatewayManager;

class DonationCheckout
{
    public function __construct(private readonly PaymentGatewayManager $gateways)
    {
    }

    public function start(Request $request): array
    {
        $data = $request->validate([
            'donor_name' => 'nullable|string|max:120',
            'is_anonymous' => 'boolean',
            'amount_minor' => 'required|integer|min:100',
            'message' => 'nullable|string|max:500',
            'method' => 'nullable|in:cash,bank_transfer,wave,orange_money',
        ]);

        $method = PaymentMethod::from($data['method'] ?? 'cash');
        $donation = Donation::create([
            'donor_name' => $data['donor_name'] ?? null,
            'is_anonymous' => $request->boolean('is_anonymous'),
            'amount_minor' => $data['amount_minor'],
            'message' => $data['message'] ?? null,
            'user_id' => $request->user()?->id,
            'currency' => 'XOF',
            'status' => 'pending',
        ]);

        if (!$method->requiresGateway()) {
            return [
                'status' => 201,
                'body' => [
                    'id' => $donation->id,
                    'status' => 'pending',
                    'message' => 'Intention de don enregistrée. Le règlement en espèces ou par virement est confirmé par l\'administration.',
                ],
            ];
        }

        if (!$request->user()) {
            $donation->delete();

            return [
                'status' => 401,
                'body' => ['message' => 'Connectez-vous pour payer par Wave ou Orange Money.'],
            ];
        }

        $payment = Payment::create([
            'payable_type' => Donation::class,
            'payable_id' => $donation->id,
            'user_id' => $request->user()->id,
            'method' => $method,
            'status' => PaymentStatus::PENDING,
            'currency' => 'XOF',
            'amount_minor' => $donation->amount_minor,
            'metadata' => ['source' => 'donation'],
        ]);
        $donation->update(['payment_id' => $payment->id]);

        $result = $this->gateways->for($method)->initiate(PaymentData::fromPayment($payment));
        if (!($result['ok'] ?? false)) {
            $payment->markAsFailed($result['error'] ?? 'gateway');
            $donation->update(['status' => 'failed']);

            return [
                'status' => 502,
                'body' => [
                    'id' => $donation->id,
                    'status' => 'failed',
                    'message' => 'La passerelle n\'a pas ouvert de paiement. Aucun encaissement n\'a été enregistré.',
                    'error' => $result['error'] ?? 'gateway_unavailable',
                ],
            ];
        }

        $payment->update([
            'gateway_transaction_id' => $result['transaction_id'],
            'gateway_response' => $result['raw'] ?? null,
            'metadata' => array_merge($payment->metadata ?? [], ['checkout_url' => $result['checkout_url']]),
        ]);

        return [
            'status' => 201,
            'body' => [
                'id' => $donation->id,
                'status' => 'pending',
                'checkout_url' => $result['checkout_url'],
                'message' => 'Paiement ouvert chez l\'opérateur. Il sera confirmé à réception du webhook.',
            ],
        ];
    }
}
