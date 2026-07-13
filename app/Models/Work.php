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
        return $this->belongsToMany(Tag::class)
            ->withTimestamps();
    }

    public function characters(): HasMany
    {
        return $this->hasMany(Character::class);
    }

    public function characterRelationships(): HasMany
    {
        return $this->hasMany(CharacterRelationship::class);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' && $this->deleted_at === null;
    }
    protected static function booted(): void
    {
        // OWNER_CREATED_BY_AUTO_SET
        static::creating(function ($model): void {
            if (auth()->check() && empty($model->created_by)) {
                $model->created_by = auth()->id();
            }
        });
        // /OWNER_CREATED_BY_AUTO_SET
    }

}
