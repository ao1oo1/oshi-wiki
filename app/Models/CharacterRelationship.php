<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CharacterRelationship extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'work_id',
        'from_character_id',
        'to_character_id',
        'called_name',
        'relationship',
        'impression',
        'notes',
        'status',
        'review_status',
        'reviewed_at',
        'reviewed_by',
    ];

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function fromCharacter(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'from_character_id');
    }

    public function toCharacter(): BelongsTo
    {
        return $this->belongsTo(Character::class, 'to_character_id');
    }
}
