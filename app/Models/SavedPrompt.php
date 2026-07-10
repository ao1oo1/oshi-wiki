<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SavedPrompt extends Model
{
    use SoftDeletes;

    public const WORK_SOURCE_ORIGINAL = 'original';

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'purpose',

        'work_source',
        'work_id',
        'selected_character_refs',
        'include_relationship_timeline',

        'writing_style',
        'writing_style_other',
        'genre',
        'genre_other',

        'synopsis',
        'plot_opening',
        'plot_development',
        'plot_turn',
        'plot_conclusion',

        'prompt_body',
        'notes',
        'status',
        'used_count',
        'last_used_at',
    ];

    protected function casts(): array
    {
        return [
            'selected_character_refs' => 'array',
            'last_used_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public static function categoryLabels(): array
    {
        return [
            'scene' => 'シーン作成',
            'character' => 'キャラクター',
            'relationship' => '関係性',
            'world' => '世界観',
            'rewrite' => '推敲・調整',
            'other' => 'その他',
        ];
    }

    public static function writingStyleLabels(): array
    {
        return [
            'dream_novel' => '夢小説風',
            'light_novel' => 'ラノベ風',
            'paperback' => '文庫本風',
            'web_novel' => 'Web小説風',
            'scenario' => 'シナリオ風',
            'other' => 'その他',
        ];
    }

    public static function genreLabels(): array
    {
        return [
            'love_comedy' => 'ラブコメ',
            'daily_life' => '日常',
            'relaxed' => 'まったり',
            'horror' => 'ホラー',
            'fantasy' => 'ファンタジー',
            'mystery' => 'ミステリー',
            'action' => 'アクション',
            'other' => 'その他',
        ];
    }

    public function categoryLabel(): string
    {
        return self::categoryLabels()[$this->category] ?? 'その他';
    }

    public function writingStyleLabel(): string
    {
        if ($this->writing_style === 'other' && $this->writing_style_other) {
            return $this->writing_style_other;
        }

        return self::writingStyleLabels()[$this->writing_style] ?? '-';
    }

    public function genreLabel(): string
    {
        if ($this->genre === 'other' && $this->genre_other) {
            return $this->genre_other;
        }

        return self::genreLabels()[$this->genre] ?? '-';
    }

    public function workLabel(): string
    {
        return 'オリジナル';
    }

    public function lastUsedLabel(): string
    {
        return $this->last_used_at
            ? $this->last_used_at->format('Y/m/d H:i')
            : '未使用';
    }
}
