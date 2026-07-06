<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Character extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'contributor_application_id',
        'helpful_count',
        'work_id',
        'name',
        'name_kana',
        'age',
        'affiliation',
        'grade_class',
        'first_person',
        'tone',
        'tone_examples',
        'personality',
        'appearance',
        'background',
        'status',
        'review_status',
        'reviewed_at',
        'reviewed_by',
    ];


    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)
            ->withTimestamps();
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
}
