<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QueueLog extends Model
{
    protected $fillable = [
        'queue_id',
        'action',
        'from_position',
        'to_position',
        'by_user_id',
        'occurred_at',
    ];

    protected $casts = [
        'from_position' => 'integer',
        'to_position' => 'integer',
        'occurred_at' => 'datetime',
    ];

    public function queue(): BelongsTo
    {
        return $this->belongsTo(Queue::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'by_user_id');
    }
}
