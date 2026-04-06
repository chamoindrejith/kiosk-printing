<?php

namespace App\Services\Printers;

use App\Models\Printer;

class PrinterCupsQueueManager
{
    public function syncAll(iterable $printers): array
    {
        $results = [];

        foreach ($printers as $printer) {
            try {
                $results[] = [
                    'printer_id' => $printer->id,
                    'printer_code' => $printer->code,
                    'queue' => $this->sync($printer),
                    'status' => 'synced',
                ];
            } catch (\Throwable $e) {
                $results[] = [
                    'printer_id' => $printer->id,
                    'printer_code' => $printer->code,
                    'queue' => null,
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function sync(Printer $printer): string
    {
        $existingQueue = $this->discoverExistingQueue($printer);
        if (!empty($existingQueue)) {
            $capabilities = is_array($printer->capabilities) ? $printer->capabilities : [];
            $capabilities['cups_queue'] = $existingQueue;
            $printer->forceFill(['capabilities' => $capabilities])->save();

            return $existingQueue;
        }

        if (!$printer->ip_address) {
            throw new \RuntimeException('Printer IP address is required to create a new CUPS queue.');
        }

        $queueName = $this->resolveQueueName($printer);
        $queueUri = sprintf('ipp://%s:%d/ipp/print', $printer->ip_address, $printer->port ?? 631);

        $command = sprintf(
            'lpadmin -p %s -E -v %s -m everywhere 2>&1',
            escapeshellarg($queueName),
            escapeshellarg($queueUri)
        );

        $output = [];
        $exitCode = 0;
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \RuntimeException(trim(implode("\n", $output)) ?: 'Failed to create CUPS queue.');
        }

        $capabilities = is_array($printer->capabilities) ? $printer->capabilities : [];
        $capabilities['cups_queue'] = $queueName;
        $printer->forceFill(['capabilities' => $capabilities])->save();

        return $queueName;
    }

    public function discoverExistingQueue(Printer $printer): ?string
    {
        $availableQueues = $this->listAvailableQueues();

        $candidates = array_filter([
            is_array($printer->capabilities) ? ($printer->capabilities['cups_queue'] ?? null) : null,
            $this->normalizeQueueName($printer->code),
            $this->normalizeQueueName($printer->name),
            $this->normalizeQueueName($printer->code . '_' . $printer->name),
        ]);

        foreach ($candidates as $candidate) {
            foreach ($availableQueues as $queue) {
                if ($this->normalizeQueueName($queue) === $this->normalizeQueueName($candidate)) {
                    return $queue;
                }
            }
        }

        return null;
    }

    private function resolveQueueName(Printer $printer): string
    {
        $baseName = preg_replace('/[^A-Za-z0-9]+/', '_', strtoupper($printer->code));
        $baseName = trim((string) $baseName, '_');

        return sprintf('PRN_%d_%s', $printer->id, $baseName ?: 'PRINTER');
    }

    private function listAvailableQueues(): array
    {
        $output = [];
        $exitCode = 0;

        exec('lpstat -e 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            return [];
        }

        return array_values(array_filter(array_map('trim', $output)));
    }

    private function normalizeQueueName(string $value): string
    {
        $normalized = preg_replace('/[^A-Za-z0-9]+/', '_', strtoupper($value));

        return trim((string) $normalized, '_');
    }
}