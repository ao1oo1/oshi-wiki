<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkStorySection extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'arc' => '編',
        'part' => '部',
        'chapter' => '章',
        'episode' => '話',
        'act' => '幕',
        'prologue' => '序章',
        'epilogue' => '終章',
        'other' => 'その他',
    ];

    public const SPOILER_LEVELS = [
        'none' => 'なし',
        'minor' => '軽度',
        'major' => '重大',
    ];

    protected $fillable = [
        'work_id',
        'parent_section_id',
        'section_type',
        'section_number',
        'title',
        'title_kana',
        'short_label',
        'synopsis',
        'cumulative_settings',
        'notes',
        'spoiler_level',
        'sort_order',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'work_id' => 'integer',
            'parent_section_id' => 'integer',
            'section_number' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function parentSection(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_section_id');
    }

    public function childSections(): HasMany
    {
        return $this->hasMany(self::class, 'parent_section_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(WorkStorySectionEvent::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(
            Character::class,
            'character_work_story_section'
        )
            ->withPivot([
                'id',
                'appearance_type',
                'age_at_section',
                'school_grade_at_section',
                'class_at_section',
                'affiliation_at_section',
                'position_at_section',
                'character_state',
                'first_appearance',
                'notes',
                'sort_order',
            ])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->section_type]
            ?? $this->section_type;
    }

    protected static function booted(): void
    {
        static::creating(function (self $section): void {
            if (auth()->check() && ! $section->created_by) {
                $section->created_by = auth()->id();
            }
        });

        static::saving(function (self $section): void {
            if (auth()->check()) {
                $section->updated_by = auth()->id();
            }
        });
    }
}
