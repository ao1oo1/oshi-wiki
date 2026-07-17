<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkStorySectionEvent extends Model
{
    protected $fillable = [
        'work_story_section_id',
        'event_number',
        'title',
        'timing',
        'summary',
        'location',
        'outcome',
        'spoiler_level',
        'notes',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'event_number' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(
            WorkStorySection::class,
            'work_story_section_id'
        );
    }
}
