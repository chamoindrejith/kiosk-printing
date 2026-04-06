<?php

namespace App\Domain\Pdf;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PdfService
{
    private int $maxFileSize;
    private int $maxPages;

    public function __construct()
    {
        $this->maxFileSize = config('pdf.max_file_size', 50 * 1024 * 1024);
        $this->maxPages = config('pdf.max_pages', 100);
    }

    public function validate(UploadedFile|array|null $file): array
    {
        if ($file === null) {
            return ['valid' => false, 'errors' => ['No file was uploaded']];
        }

        $errors = [];

        if ($file instanceof UploadedFile) {
            $fileArray = [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'tmp_name' => $file->getPathname(),
                'error' => $file->getError(),
            ];
        } else {
            $fileArray = $file;
        }

        if (!is_array($fileArray)
            || !isset($fileArray['name'], $fileArray['size'], $fileArray['tmp_name'], $fileArray['error'])) {
            return ['valid' => false, 'errors' => ['Invalid upload payload']];
        }

        if ($fileArray['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'errors' => ['Upload failed with error code: ' . $fileArray['error']]];
        }

        $extension = strtolower(pathinfo($fileArray['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            $errors[] = 'Only PDF files are allowed';
        }

        if ($fileArray['size'] > $this->maxFileSize) {
            $errors[] = "File size exceeds maximum of " . ($this->maxFileSize / 1024 / 1024) . "MB";
        }

        $mimeType = $this->detectMimeType($fileArray['tmp_name']);
        if ($mimeType !== 'application/pdf') {
            $errors[] = 'File is not a valid PDF';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }

    public function store($file, string $printerCode): string
    {
        if ($file instanceof UploadedFile) {
            $path = $file->getPathname();
            $originalName = $file->getClientOriginalName();
        } else {
            $path = $file['tmp_name'];
            $originalName = $file['name'];
        }

        $directory = "uploads/{$printerCode}/" . date('Y/m/d');
        $filename = Str::uuid() . '.pdf';
        
        $destination = storage_path("app/{$directory}/{$filename}");
        
        if (!is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }

        if ($file instanceof UploadedFile) {
            $file->move(dirname($destination), $filename);
        } else {
            if (!move_uploaded_file($path, $destination)) {
                throw new \Exception("Failed to store uploaded file");
            }
        }

        return "{$directory}/{$filename}";
    }

    public function getPageCount(string $filePath): int
    {
        $fullPath = storage_path("app/{$filePath}");
        
        if (!file_exists($fullPath)) {
            throw new \Exception("File not found: {$filePath}");
        }

        if (class_exists('Imagick')) {
            try {
                $imagick = new \Imagick($fullPath);
                $count = $imagick->getNumberImages();
                $imagick->clear();
                if ($count > 0) {
                    return $count;
                }
            } catch (\Exception $e) {
                Log::debug("Imagick failed: " . $e->getMessage());
            }
        }

        $command = "pdfinfo " . escapeshellarg($fullPath) . " 2>/dev/null | grep '^Pages:' | awk '{print $2}'";
        $output = trim(shell_exec($command));
        
        if (is_numeric($output) && $output > 0) {
            return (int) $output;
        }

        $command = "pdftotext " . escapeshellarg($fullPath) . " 2>/dev/null | wc -l";
        $output = trim(shell_exec($command));
        
        if (is_numeric($output) && $output > 0) {
            return max(1, (int) ceil((int) $output / 50));
        }

        $fileSize = filesize($fullPath);
        if ($fileSize > 50000) {
            return (int) ceil($fileSize / 10000);
        }

        Log::warning("Could not determine PDF page count, defaulting to 1");
        return 1;
    }

    public function getMetadata(string $filePath): array
    {
        $fullPath = storage_path("app/{$filePath}");
        
        return [
            'filename' => basename($filePath),
            'size' => file_exists($fullPath) ? filesize($fullPath) : 0,
            'exists' => file_exists($fullPath),
        ];
    }

    private function detectMimeType(string $filePath): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mimeType;
        }

        return 'application/pdf';
    }
}