<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Work extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'parent_work_id',
        'child_sort_order',
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
            'parent_work_id' => 'integer',
            'child_sort_order' => 'integer',
        ];
    }

    public function parentWork(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_work_id'
        );
    }

    public function childWorks(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_work_id'
        )
            ->orderBy('child_sort_order')
            ->orderBy('id');
    }

    public function publishedChildWorks(): HasMany
    {
        return $this->childWorks()
            ->where('status', 'published');
    }

    public function isChildWork(): bool
    {
        return $this->parent_work_id !== null;
    }

    public function isRootWork(): bool
    {
        return $this->parent_work_id === null;
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    public function linkedCharacters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class, 'character_work')
            ->using(CharacterWork::class)
            ->withPivot(['id','is_primary','appearance_type','sort_order','notes'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
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
