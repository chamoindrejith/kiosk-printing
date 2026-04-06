<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    protected $fillable = [
        'name',
        'paper_size',
        'color_mode',
        'duplex_mode',
        'price_per_sheet',
        'printer_id',
        'is_active',
    ];

    protected $casts = [
        'price_per_sheet' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }

    public static function findForOptions(?int $printerId, string $paperSize, bool $color, bool $duplex): ?self
    {
        $query = self::where('paper_size', $paperSize)
            ->where('color_mode', $color ? 'color' : 'bw')
            ->where('duplex_mode', $duplex ? 'duplex' : 'simplex')
            ->where('is_active', true);

        $rule = $query->where('printer_id', $printerId)->first();
        
        if (!$rule) {
            $rule = $query->whereNull('printer_id')->first();
        }

        return $rule;
    }
}