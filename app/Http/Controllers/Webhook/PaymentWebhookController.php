<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PrintJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        
        Log::info('Payment webhook received', $payload);

        $gatewayPaymentId = $payload['payment_id'] ?? $payload['transaction_id'] ?? null;
        
        if (!$gatewayPaymentId) {
            Log::warning('Payment webhook missing payment_id');
            return response()->json(['error' => 'Missing payment_id'], 400);
        }

        $payment = Payment::where('gateway_payment_id', $gatewayPaymentId)->first();

        if (!$payment) {
            Log::warning('Payment not found for webhook', ['payment_id' => $gatewayPaymentId]);
            return response()->json(['error' => 'Payment not found'], 404);
        }

        if ($payment->status === 'successful') {
            Log::info('Payment already processed', ['payment_id' => $gatewayPaymentId]);
            return response()->json(['status' => 'already_processed']);
        }

        $isValid = $this->verifyWebhook($payload, $request->header('X-Signature'));
        
        if (!$isValid) {
            Log::warning('Invalid payment webhook signature', ['payment_id' => $gatewayPaymentId]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $paymentStatus = $payload['status'] ?? 'pending';
        
        if ($paymentStatus === 'success' || $paymentStatus === 'successful') {
            $payment->markAsSuccessful();
            
            $printJob = $payment->printJob;
            if ($printJob) {
                $printJob->update(['status' => 'payment_success']);
            }
            
            Log::info('Payment successful', ['payment_id' => $payment->id]);
        } elseif (in_array($paymentStatus, ['failed', 'expired'])) {
            $payment->markAsFailed($paymentStatus);
            
            $printJob = $payment->printJob;
            if ($printJob) {
                $printJob->update(['status' => 'failed']);
            }
            
            Log::info('Payment failed/expired', ['payment_id' => $payment->id, 'status' => $paymentStatus]);
        }

        $payment->recordEvent('webhook_received', $payload);

        return response()->json(['status' => 'ok']);
    }

    private function verifyWebhook(array $payload, ?string $signature): bool
    {
        if (config('app.env') === 'local' || config('app.env') === 'testing') {
            return true;
        }

        if (!$signature) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', json_encode($payload), config('services.lankaqr.webhook_secret', ''));
        
        return hash_equals($expectedSignature, $signature);
    }
}