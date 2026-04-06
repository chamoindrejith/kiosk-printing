<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Printer extends Model
{
    protected $fillable = [
        'code',
        'name',
        'location',
        'ip_address',
        'port',
        'protocol',
        'is_active',
        'capabilities',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'capabilities' => 'array',
    ];

    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJob::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    public function isReachable(): bool
    {
        if (!$this->ip_address) {
            return false;
        }
        
        $adapter = app(\App\Services\Printers\PrinterGatewayInterface::class);
        return $adapter->isReachable($this);
    }
}