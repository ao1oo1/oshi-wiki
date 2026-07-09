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
    public const SOURCE_V1_CHARACTER = 'v1_character';

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
    ];


    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

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

    public function fromOfficialCharacter(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'from_character_id');
    }

    public function toOfficialCharacter(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'to_character_id');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public function fromDisplayName(): string
    {
        if ($this->from_character_source === self::SOURCE_V1_CHARACTER) {
            return $this->formatOfficialCharacterName($this->fromOfficialCharacter);
        }

        return $this->fromCharacter?->name ?? '-';
    }

    public function toDisplayName(): string
    {
        if ($this->to_character_source === self::SOURCE_V1_CHARACTER) {
            return $this->formatOfficialCharacterName($this->toOfficialCharacter);
        }

        return $this->toCharacter?->name ?? '-';
    }

    public function fromSourceLabel(): string
    {
        return $this->from_character_source === self::SOURCE_V1_CHARACTER
            ? '作品キャラクター'
            : 'オリジナル';
    }

    public function toSourceLabel(): string
    {
        return $this->to_character_source === self::SOURCE_V1_CHARACTER
            ? '作品キャラクター'
            : 'オリジナル';
    }

    private function formatOfficialCharacterName(?Character $character): string
    {
        if (! $character) {
            return '-';
        }

        $workTitle = $character->work?->title ?? null;

        if ($workTitle) {
            return $workTitle . ' ＞ ' . $character->name;
        }

        return $character->name;
    }
}
