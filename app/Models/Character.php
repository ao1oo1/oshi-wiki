<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Character extends Model
{
    use HasFactory, SoftDeletes;

    public const SOURCE_TYPES = [
        'official' => '公式',
        'semi_official' => '準公式',
        'wikipedia' => 'Wikipedia',
        'encyclopedia' => '百科事典',
        'personal_site' => '個人サイト',
    ];

    public const SOURCE_RELIABILITIES = [
        'high' => '高',
        'medium' => '中',
        'low' => '低',
    ];

    public const SPOILER_LEVELS = [
        'none' => 'なし',
        'minor' => '軽度',
        'major' => '重大',
        'latest_chapter' => '本誌情報あり',
        'anime_spoiler' => 'アニメネタバレあり',
    ];

    protected $fillable = [
        'contributor_application_id',
        'helpful_count',
        'work_id',
        'name',
        'name_kana',
        'real_name',
        'aliases',
        'name_english',
        'gender',
        'age',
        'birthday',
        'height',
        'weight',
        'blood_type',
        'birthplace',
        'species',
        'affiliation',
        'school_grade_class',
        'occupation_position',
        'family_structure',
        'appearance',
        'personality',
        'first_person',
        'second_person',
        'basic_tone',
        'catchphrases',
        'distinctive_speech',
        'tone_by_relationship',
        'short_quote_examples',
        'abilities',
        'background',
        'story_activities',
        'source_title',
        'source_url',
        'source_type',
        'source_reliability',
        'source_checked_at',
        'spoiler_level',

        // 旧フォーム・旧CSVとの互換用
        'grade_class',
        'tone',
        'tone_examples',

        'status',
        'review_status',
        'reviewed_at',
        'reviewed_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'source_checked_at' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function outgoingRelationships(): HasMany
    {
        return $this->hasMany(CharacterRelationship::class, 'from_character_id');
    }

    public function incomingRelationships(): HasMany
    {
        return $this->hasMany(CharacterRelationship::class, 'to_character_id');
    }

    protected static function booted(): void
    {
        static::creating(function (Character $character): void {
            if (auth()->check() && empty($character->created_by)) {
                $character->created_by = auth()->id();
            }
        });
    }
}
