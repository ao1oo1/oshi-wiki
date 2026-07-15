<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkCanonEvent extends Model
{
    protected $fillable = [
        'work_id',
        'sort_order',
        'timing',
        'event_name',
        'event_status',
        'notes',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }
}
