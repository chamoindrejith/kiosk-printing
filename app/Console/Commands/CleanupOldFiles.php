<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class CleanupOldFiles extends Command
{
    protected $signature = 'print:cleanup {--days=3 : Number of days to retain files}';
    protected $description = 'Clean up old uploaded PDF files';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $this->info("Cleaning up files older than {$days} days...");

        $storagePath = storage_path('app/uploads');
        
        if (!File::exists($storagePath)) {
            $this->info('No uploads directory found.');
            return Command::SUCCESS;
        }

        $deletedCount = 0;
        $deletedSize = 0;

        foreach (File::directories($storagePath) as $printerDir) {
            foreach (File::directories($printerDir) as $dateDir) {
                $dirDate = basename($dateDir);
                
                if ($this->shouldDelete($dirDate, $cutoff)) {
                    $size = $this->getDirectorySize($dateDir);
                    File::deleteDirectory($dateDir);
                    $deletedCount++;
                    $deletedSize += $size;
                    $this->line("Deleted: {$dateDir}");
                }
            }
        }

        Log::info('File cleanup completed', [
            'directories_deleted' => $deletedCount,
            'size_freed_bytes' => $deletedSize,
        ]);

        $this->info("Cleaned up {$deletedCount} directories (" . number_format($deletedSize / 1024, 2) . " KB)");

        return Command::SUCCESS;
    }

    private function shouldDelete(string $dirDate, \Carbon\Carbon $cutoff): bool
    {
        try {
            $date = \Carbon\Carbon::createFromFormat('Y/m/d', $dirDate);
            return $date->lt($cutoff);
        } catch (\Exception $e) {
            return false;
        }
    }

    private function getDirectorySize(string $directory): int
    {
        $size = 0;
        foreach (File::allFiles($directory) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }
}