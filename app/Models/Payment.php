<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $fillable = [
        'print_job_id',
        'gateway',
        'gateway_payment_id',
        'reference',
        'amount',
        'status',
        'qr_code',
        'paid_at',
        'expires_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public const STATUSES = ['initiated', 'pending', 'successful', 'failed', 'expired', 'refunded'];

    public function printJob(): BelongsTo
    {
        return $this->belongsTo(PrintJob::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }

    public function recordEvent(string $type, array $payload = []): self
    {
        $this->events()->create([
            'event_type' => $type,
            'payload' => $payload,
        ]);
        return $this;
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function markAsSuccessful(): void
    {
        $this->status = 'successful';
        $this->paid_at = now();
        $this->save();
        $this->recordEvent('payment_successful');
    }

    public function markAsFailed(string $reason = ''): void
    {
        $this->status = 'failed';
        $this->save();
        $this->recordEvent('payment_failed', ['reason' => $reason]);
    }
}