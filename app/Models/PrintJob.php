<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintJob extends Model
{
    protected $fillable = [
        'printer_id',
        'payment_id',
        'status',
        'original_filename',
        'file_path',
        'original_page_count',
        'effective_page_count',
        'sheet_count',
        'copies',
        'color',
        'duplex',
        'paper_size',
        'page_range',
        'total_price',
        'last_confirmed_page',
        'external_job_id',
        'error_message',
        'printed_at',
    ];

    protected $casts = [
        'color' => 'boolean',
        'duplex' => 'boolean',
        'printed_at' => 'datetime',
    ];

    public const STATUSES = [
        'draft',
        'configured',
        'awaiting_payment',
        'payment_pending',
        'payment_success',
        'awaiting_confirmation',
        'queued',
        'dispatching',
        'printing',
        'paused',
        'completed',
        'failed',
        'cancelled',
        'expired',
    ];

    public function printer(): BelongsTo
    {
        return $this->belongsTo(Printer::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(PrintJobPage::class)->orderBy('sequence_order');
    }

    public function events(): HasMany
    {
        return $this->hasMany(PrintJobEvent::class);
    }

    public function recordEvent(string $type, array $payload = []): self
    {
        $this->events()->create([
            'event_type' => $type,
            'payload' => $payload,
        ]);
        return $this;
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $transitions = [
            'draft' => ['configured', 'cancelled'],
            'configured' => ['awaiting_payment', 'cancelled'],
            'awaiting_payment' => ['payment_pending', 'expired', 'cancelled'],
            'payment_pending' => ['payment_success', 'failed', 'expired'],
            'payment_success' => ['awaiting_confirmation', 'failed'],
            'awaiting_confirmation' => ['queued', 'cancelled'],
            'queued' => ['dispatching', 'cancelled'],
            'dispatching' => ['printing', 'failed'],
            'printing' => ['completed', 'paused', 'failed'],
            'paused' => ['printing', 'failed', 'cancelled'],
        ];

        return in_array($newStatus, $transitions[$this->status] ?? []);
    }

    public function transitionTo(string $status): bool
    {
        if (!$this->canTransitionTo($status)) {
            return false;
        }

        $this->status = $status;
        $this->save();
        $this->recordEvent('status_changed', ['from' => $this->getOriginal('status'), 'to' => $status]);
        return true;
    }
}