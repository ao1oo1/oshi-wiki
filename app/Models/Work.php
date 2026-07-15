<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Work extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contributor_application_id',
        'helpful_count',
        'title',
        'title_kana',
        'slug',
        'genre',
        'original_media',
        'official_url',
        'guideline_url',
        'description',
        'timeline_setting',
        'building_layout',
        'character_room_seat',
        'hangout_places',
        'restricted_secret_places',
        'cafeteria_store_menu',
        'daily_schedule',
        'school_dorm_rules',
        'uniform_details',
        'casual_holiday_rules',
        'duty_system',
        'class_grade_system',
        'organizations_memberships',
        'ranking_system',
        'adult_roles',
        'annual_events',
        'event_flow',
        'story_season',
        'school_location',
        'commute_environment',
        'nearby_shops',
        'climate_nature',
        'sounds',
        'symbolic_motifs',
        'required_belongings',
        'status',
        'review_status',
        'created_by',
        'updated_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    public function characterRelationships(): HasMany
    {
        return $this->hasMany(CharacterRelationship::class);
    }

    public function canonEvents(): HasMany
    {
        return $this->hasMany(WorkCanonEvent::class)->orderBy('sort_order')->orderBy('id');
    }

    public function termUsages(): HasMany
    {
        return $this->hasMany(WorkTermUsage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->deleted_at === null;
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });
    }
}
