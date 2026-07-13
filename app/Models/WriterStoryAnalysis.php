<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WriterStoryAnalysis extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'selected_story_ids',
        'story_snapshot',
        'analysis_notes',
        'analysis_prompt',
        'analysis_result',
    ];

    protected function casts(): array
    {
        return [
            'selected_story_ids' => 'array',
            'story_snapshot' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(
        Builder $query,
        User $user
    ): Builder {
        return $query->where('user_id', $user->id);
    }

    public function storyCount(): int
    {
        return count($this->selected_story_ids ?? []);
    }

    public function resultLength(): int
    {
        return mb_strlen((string) $this->analysis_result);
    }
}
