<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WriterStory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'episode_number',
        'body',
        'memo',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'episode_number' => 'integer',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 最高管理者を含め、必ず本人のデータだけに限定する。
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => '下書き',
            default => '有効',
        };
    }

    public function bodyLength(): int
    {
        return mb_strlen($this->body ?? '');
    }
}
