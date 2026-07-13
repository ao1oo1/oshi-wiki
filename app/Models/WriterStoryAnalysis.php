<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WriterStoryAnalysis extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'selected_story_ids',
        'analysis_notes',
        'analysis_prompt',
        'analysis_result',
    ];

    protected function casts(): array
    {
        return [
            'selected_story_ids' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 最高管理者を含め、本人のデータだけに限定します。
     */
    public function scopeForUser(
        Builder $query,
        User $user
    ): Builder {
        return $query->where('user_id', $user->id);
    }

    public function resultLength(): int
    {
        return mb_strlen((string) $this->analysis_result);
    }

    public function promptLength(): int
    {
        return mb_strlen((string) $this->analysis_prompt);
    }

    public function storyCount(): int
    {
        return count($this->selected_story_ids ?? []);
    }

    public function hasAnalysisResult(): bool
    {
        return trim((string) $this->analysis_result) !== '';
    }
}
