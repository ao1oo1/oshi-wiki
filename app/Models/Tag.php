<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'status',
        'review_status',
        'reviewed_at',
        'reviewed_by',
        'created_by'
    ];

    public function works(): BelongsToMany
    {
        return $this->belongsToMany(Work::class)
            ->withTimestamps();
    }

    public function characters(): BelongsToMany
    {
        return $this->belongsToMany(Character::class)
            ->withTimestamps();
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
