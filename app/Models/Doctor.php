<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Doctor extends Model
{
    protected $fillable = [
        'full_name',
        'title',
        'specialization',
        'education',
        'experience',
        'photo_path',
        'schedule',
    ];

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    public function queues(): HasMany
    {
        return $this->hasMany(Queue::class);
    }
}
