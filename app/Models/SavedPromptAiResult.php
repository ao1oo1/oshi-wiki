<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedPromptAiResult extends Model
{
    protected $fillable = [
        'user_id',
        'saved_prompt_id',
        'title',
        'prompt_snapshot',
        'result_body',
    ];

    protected function casts(): array
    {
        return [
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savedPrompt(): BelongsTo
    {
        return $this->belongsTo(SavedPrompt::class);
    }

    public function scopeForUser(
        Builder $query,
        User $user
    ): Builder {
        return $query->where('user_id', $user->id);
    }

    public function resultLength(): int
    {
        return mb_strlen((string) $this->result_body);
    }
}
