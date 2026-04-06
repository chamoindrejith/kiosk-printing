<?php

namespace App\Services\Payments;

use App\Domain\Payments\Payment;
use Illuminate\Support\Str;

class MockPaymentGateway implements PaymentGatewayInterface
{
    private array $payments = [];

    public function createPayment(array $data): array
    {
        $paymentId = Str::uuid()->toString();
        
        $this->payments[$paymentId] = [
            'id' => $paymentId,
            'amount' => $data['amount'],
            'reference' => $data['reference'],
            'status' => 'pending',
            'created_at' => now()->toIso8601String(),
            'expires_at' => now()->addMinutes(15)->toIso8601String(),
            'qr_code' => $this->generateMockQrCode($paymentId),
        ];

        return $this->payments[$paymentId];
    }

    public function verifyPayment(string $paymentId): array
    {
        if (!isset($this->payments[$paymentId])) {
            return ['valid' => false, 'message' => 'Payment not found'];
        }

        $payment = $this->payments[$paymentId];
        
        if ($payment['status'] === 'successful') {
            return ['valid' => true, 'payment' => $payment];
        }

        return ['valid' => false, 'payment' => $payment];
    }

    public function verifyWebhook(array $payload, string $signature): bool
    {
        return true;
    }

    public function getPaymentStatus(string $paymentId): string
    {
        return $this->payments[$paymentId]['status'] ?? 'not_found';
    }

    public function simulateSuccess(string $paymentId): void
    {
        if (isset($this->payments[$paymentId])) {
            $this->payments[$paymentId]['status'] = 'successful';
            $this->payments[$paymentId]['paid_at'] = now()->toIso8601String();
        }
    }

    private function generateMockQrCode(string $paymentId): string
    {
        return "MOCK_QR_CODE_{$paymentId}";
    }
}