<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavedPrompt extends Model
{
    public const WORK_SOURCE_ORIGINAL = 'original';
    public const WORK_SOURCE_V1 = 'v1';

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'purpose',

        'work_source',
        'work_id',
        'selected_character_refs',
        'include_relationship_timeline',
        'include_work_worldbuilding',
        'selected_work_worldbuilding_categories',

        'writing_style',
        'writing_style_other',
        'genre',
        'genre_other',

        'synopsis',
        'plot_opening',
        'plot_development',
        'plot_turn',
        'plot_conclusion',

        'use_story_length_options',
        'story_length_type',
        'output_plot_first',
        'output_in_parts',
        'selected_story_analysis_ids',

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
            'include_relationship_timeline' => 'boolean',
            'include_work_worldbuilding' => 'boolean',
            'selected_work_worldbuilding_categories' => 'array',
            'use_story_length_options' => 'boolean',
            'output_plot_first' => 'boolean',
            'output_in_parts' => 'boolean',
            'selected_story_analysis_ids' => 'array',
            'last_used_at' => 'datetime',
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

    public static function workWorldbuildingCategoryLabels(): array
    {
        return [
            'story_design' => '物語の設計',
            'buildings' => '建物・空間',
            'life_rules' => '生活・ルール',
            'organizations' => '組織・制度',
            'events_time' => '行事・時間の流れ',
            'geography' => '地理・周辺環境',
            'sensory' => '小物・感覚的な情報',
            'canon_events' => '原作の重要イベント年表',
            'term_usages' => '用語の使用例',
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

    public function storyLengthLabel(): string
    {
        if (! $this->use_story_length_options) {
            return '指定なし';
        }

        return $this->story_length_type === 'long'
            ? '長編・全10話'
            : '短編・1話完結';
    }

    public function aiResults(): HasMany
    {
        return $this->hasMany(
            SavedPromptAiResult::class
        )->latest();
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function workLabel(): string
    {
        if ($this->work_source === self::WORK_SOURCE_V1) {
            return $this->work?->title
                ?? '参照できない作品';
        }

        return 'オリジナル';
    }

    public function lastUsedLabel(): string
    {
        return $this->last_used_at
            ? $this->last_used_at->format('Y/m/d H:i')
            : '未使用';
    }
}
