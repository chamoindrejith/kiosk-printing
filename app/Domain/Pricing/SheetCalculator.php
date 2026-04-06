<?php

namespace App\Domain\Pricing;

class SheetCalculator
{
    public function calculate(int $pageCount, bool $duplex, int $copies = 1): int
    {
        if ($pageCount === 0) {
            return 0;
        }

        $pagesPerSheet = $duplex ? 2 : 1;
        return (int) ceil($pageCount / $pagesPerSheet) * $copies;
    }

    public function parsePageRange(?string $pageRange, int $totalPages): array
    {
        if (empty($pageRange)) {
            return range(1, $totalPages);
        }

        $pages = [];
        $parts = explode(',', $pageRange);

        foreach ($parts as $part) {
            $part = trim($part);
            
            if (strpos($part, '-') !== false) {
                $rangeParts = explode('-', $part);
                $start = (int) trim($rangeParts[0]);
                $end = (int) trim($rangeParts[1] ?? $rangeParts[0]);
                
                if ($start > 0 && $start <= $totalPages && $end > 0 && $end <= $totalPages && $start <= $end) {
                    $pages = array_merge($pages, range($start, $end));
                }
            } else {
                $page = (int) $part;
                if ($page > 0 && $page <= $totalPages) {
                    $pages[] = $page;
                }
            }
        }

        if (empty($pages)) {
            return range(1, $totalPages);
        }

        $pages = array_unique($pages);
        sort($pages);
        return $pages;
    }
}