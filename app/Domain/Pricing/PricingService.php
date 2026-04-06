<?php

namespace App\Domain\Pricing;

use App\Models\PricingRule;

class PricingService
{
    private SheetCalculator $calculator;

    public function __construct()
    {
        $this->calculator = new SheetCalculator();
    }

    public function calculate(
        int $pageCount,
        string $paperSize,
        bool $color,
        bool $duplex,
        int $copies,
        ?int $printerId = null
    ): array {
        $effectivePages = $this->calculator->parsePageRange(null, $pageCount);
        $effectivePageCount = count($effectivePages);
        
        $sheetCount = $this->calculator->calculate($effectivePageCount, $duplex, $copies);
        
        $rule = PricingRule::findForOptions($printerId, $paperSize, $color, $duplex);
        
        $unitPrice = $rule ? $rule->price_per_sheet : $this->getDefaultPrice($paperSize, $color, $duplex);
        $totalPrice = $sheetCount * $unitPrice;

        return [
            'effective_pages' => $effectivePageCount,
            'sheet_count' => $sheetCount,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'currency' => 'LKR',
            'rule_id' => $rule?->id,
        ];
    }

    public function calculateWithRange(
        int $pageCount,
        ?string $pageRange,
        string $paperSize,
        bool $color,
        bool $duplex,
        int $copies,
        ?int $printerId = null
    ): array {
        $effectivePages = $this->calculator->parsePageRange($pageRange, $pageCount);
        $effectivePageCount = count($effectivePages);
        
        $sheetCount = $this->calculator->calculate($effectivePageCount, $duplex, $copies);
        
        $rule = PricingRule::findForOptions($printerId, $paperSize, $color, $duplex);
        
        $unitPrice = $rule ? $rule->price_per_sheet : $this->getDefaultPrice($paperSize, $color, $duplex);
        $totalPrice = $sheetCount * $unitPrice;

        return [
            'effective_pages' => $effectivePageCount,
            'effective_page_numbers' => $effectivePages,
            'sheet_count' => $sheetCount,
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'currency' => 'LKR',
            'rule_id' => $rule?->id,
        ];
    }

    private function getDefaultPrice(string $paperSize, bool $color, bool $duplex): float
    {
        $basePrice = $color ? 10.00 : 5.00;
        
        if ($duplex) {
            $basePrice *= 0.9;
        }

        return match ($paperSize) {
            'A5' => $basePrice * 0.7,
            'Letter' => $basePrice * 1.1,
            'Legal' => $basePrice * 1.15,
            default => $basePrice,
        };
    }
}