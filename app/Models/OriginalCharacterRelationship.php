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
    public const SOURCE_V1 = 'v1';

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

    /**
     * 既存コードとの互換用。
     * オリジナルキャラクターのFrom側。
     */
    public function fromCharacter(): BelongsTo
    {
        return $this->belongsTo(
            OriginalCharacter::class,
            'from_original_character_id'
        );
    }

    /**
     * 既存コードとの互換用。
     * オリジナルキャラクターのTo側。
     */
    public function toCharacter(): BelongsTo
    {
        return $this->belongsTo(
            OriginalCharacter::class,
            'to_original_character_id'
        );
    }

    public function fromV1Character(): BelongsTo
    {
        return $this->belongsTo(
            Character::class,
            'from_character_id'
        );
    }

    public function toV1Character(): BelongsTo
    {
        return $this->belongsTo(
            Character::class,
            'to_character_id'
        );
    }

    public function scopeForUser(
        Builder $query,
        User $user
    ): Builder {
        return $query->where('user_id', $user->id);
    }

    public function fromDisplayName(): string
    {
        if ($this->from_character_source === self::SOURCE_V1) {
            return $this->fromV1Character?->name
                ?? '参照できないキャラクター';
        }

        return $this->fromCharacter?->name
            ?? '参照できないキャラクター';
    }

    public function toDisplayName(): string
    {
        if ($this->to_character_source === self::SOURCE_V1) {
            return $this->toV1Character?->name
                ?? '参照できないキャラクター';
        }

        return $this->toCharacter?->name
            ?? '参照できないキャラクター';
    }

    public function fromSourceLabel(): string
    {
        return $this->from_character_source === self::SOURCE_V1
            ? '登録済みキャラクター'
            : 'オリジナル';
    }

    public function toSourceLabel(): string
    {
        return $this->to_character_source === self::SOURCE_V1
            ? '登録済みキャラクター'
            : 'オリジナル';
    }

    public function fromReference(): ?string
    {
        if ($this->from_character_source === self::SOURCE_V1) {
            return $this->from_character_id
                ? 'v1:' . $this->from_character_id
                : null;
        }

        return $this->from_original_character_id
            ? 'original:' . $this->from_original_character_id
            : null;
    }

    public function toReference(): ?string
    {
        if ($this->to_character_source === self::SOURCE_V1) {
            return $this->to_character_id
                ? 'v1:' . $this->to_character_id
                : null;
        }

        return $this->to_original_character_id
            ? 'original:' . $this->to_original_character_id
            : null;
    }
}
