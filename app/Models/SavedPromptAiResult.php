<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavedPromptAiResult extends Model
{
    use SoftDeletes;

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
            'deleted_at' => 'datetime',
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
