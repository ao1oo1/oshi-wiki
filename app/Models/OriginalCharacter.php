<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OriginalCharacter extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'name_kana',
        'age',
        'gender',
        'affiliation',
        'school_grade',
        'first_person',
        'speech_style',
        'speech_examples',
        'personality',
        'appearance',
        'background',
        'image_path',
        'image_original_name',
        'is_main_character',
        'important_points',
        'ng_points',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_main_character' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public function hasImage(): bool
    {
        return filled($this->image_path);
    }
}
