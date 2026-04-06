<?php

namespace App\Services\Printers;

use App\Models\Printer;

interface PrinterGatewayInterface
{
    public function isReachable(Printer $printer): bool;
    public function getCapabilities(Printer $printer): array;
    public function submitJob(Printer $printer, string $filePath, array $options): string;
    public function getJobStatus(Printer $printer, string $jobId): array;
    public function cancelJob(Printer $printer, string $jobId): bool;
    public function supportsPageConfirmation(): bool;
}