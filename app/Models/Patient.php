<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Patient extends Model
{
    protected $fillable = [
        'name',
        'age',
        'address',
        'symptom',
        'session_token',
        'unique_code',
    ];

    protected $casts = [
        'age' => 'integer',
    ];

    public function queue(): HasOne
    {
        return $this->hasOne(Queue::class);
    }
}
