<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OriginalCharacterRelationship extends Model
{
    use SoftDeletes;

    public const SOURCE_ORIGINAL = 'original';

    protected $fillable = [
        'user_id',

        'from_character_source',
        'to_character_source',

        'from_original_character_id',
        'to_original_character_id',

        'from_character_id',
        'to_character_id',

        'called_name',
        'relationship_type',
        'impression',
        'notes',
        'timeline_items',
        'status',
    ];

    protected $casts = [
        'timeline_items' => 'array',
        'deleted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromCharacter(): BelongsTo
    {
        return $this->belongsTo(OriginalCharacter::class, 'from_original_character_id');
    }

    public function toCharacter(): BelongsTo
    {
        return $this->belongsTo(OriginalCharacter::class, 'to_original_character_id');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function fromDisplayName(): string
    {
        return $this->fromCharacter?->name ?? '-';
    }

    public function toDisplayName(): string
    {
        return $this->toCharacter?->name ?? '-';
    }

    public function fromSourceLabel(): string
    {
        return 'オリジナル';
    }

    public function toSourceLabel(): string
    {
        return 'オリジナル';
    }
}
