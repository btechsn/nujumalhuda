<?php

declare(strict_types=1);

namespace Modules\Core\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Models\Payment;
use Modules\Core\Payments\PaymentGatewayManager;
use Modules\Core\Services\PaymentSettlement;
use Modules\Core\Support\WaveSignature;
use Modules\Core\Enums\PaymentMethod;

class PaymentWebhookController extends Controller
{
    public function wave(Request $request, PaymentSettlement $settlement): JsonResponse
    {
        $secret = (string) config('services.wave.webhook_secret');
        if ($secret === '') {
            return response()->json(['message' => 'Webhook Wave non configuré'], 503);
        }

        $valid = WaveSignature::matches(
            (string) $request->header('Wave-Signature', ''),
            $request->getContent(),
            $secret,
        );
        if (!$valid) {
            return response()->json(['message' => 'Signature invalide'], 401);
        }

        $payload = $request->json()->all();
        $reference = $payload['data']['client_reference'] ?? null;
        $payment = $reference ? Payment::query()->find($reference) : null;
        if (!$payment) {
            return response()->json(['message' => 'Paiement introuvable'], 404);
        }

        $status = ($payload['data']['payment_status'] ?? '') === 'succeeded' ? 'completed' : 'pending';
        if ($status === 'completed') {
            $settlement->complete($payment, [
                'status' => 'completed',
                'transaction_id' => $payload['data']['id'] ?? $payment->gateway_transaction_id,
                'raw' => $payload,
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function orangeMoney(Request $request, PaymentGatewayManager $gateways, PaymentSettlement $settlement): JsonResponse
    {
        $paymentId = (string) ($request->input('order_id') ?: $request->input('reference'));
        $payment = Payment::query()->find($paymentId);
        if (!$payment || !$payment->gateway_transaction_id) {
            return response()->json(['message' => 'Paiement introuvable'], 404);
        }

        $result = $gateways->for(PaymentMethod::ORANGE_MONEY)->verify($payment->gateway_transaction_id);
        if (($result['status'] ?? '') === 'completed') {
            $settlement->complete($payment, $result);
        }

        return response()->json(['ok' => true]);
    }
}
