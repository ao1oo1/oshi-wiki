<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkTermUsage extends Model
{
    protected $fillable = [
        'work_id',
        'sort_order',
        'term',
        'meaning',
        'usage_example',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }
}
