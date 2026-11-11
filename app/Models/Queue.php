<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Queue extends Model
{
    public const STATUS_WAITING = 'waiting';
    public const STATUS_CALLED = 'called';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'queue_number',
        'position',
        'queue_date',
        'status',
        'called_at',
        'completed_at',
    ];

    protected $casts = [
        'queue_number' => 'integer',
        'position' => 'integer',
        'called_at' => 'datetime',
        'completed_at' => 'datetime',
        'queue_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(QueueLog::class);
    }
}
