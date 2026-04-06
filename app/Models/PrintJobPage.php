<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrintJobPage extends Model
{
    protected $fillable = [
        'print_job_id',
        'page_number',
        'copy_number',
        'sequence_order',
        'status',
        'external_page_id',
        'sent_at',
        'confirmed_at',
        'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    public const STATUSES = ['pending', 'sent', 'confirmed', 'failed', 'skipped'];

    public function printJob(): BelongsTo
    {
        return $this->belongsTo(PrintJob::class);
    }
}