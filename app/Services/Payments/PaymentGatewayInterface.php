<?php

namespace App\Services\Payments;

interface PaymentGatewayInterface
{
    public function createPayment(array $data): array;
    public function verifyPayment(string $paymentId): array;
    public function verifyWebhook(array $payload, string $signature): bool;
    public function getPaymentStatus(string $paymentId): string;
}