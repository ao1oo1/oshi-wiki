<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContributorApplication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'username',
        'email',
        'discord_id',
        'applied_at',
        'started_at',
        'registered_works_count',
        'registered_characters_count',
        'status',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'started_at' => 'datetime',
        'registered_works_count' => 'integer',
        'registered_characters_count' => 'integer',
    ];

    public function statusLabel(): string
    {
        return match ($this->status) {
            'active' => '登用中',
            'rejected' => '見送り',
            default => '申請中',
        };
    }
}
