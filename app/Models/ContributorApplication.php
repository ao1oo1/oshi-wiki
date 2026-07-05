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
        'admin_notes',
        'paused_at',
        'registered_works_count',
        'registered_characters_count',
        'status',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'started_at' => 'datetime',
        'paused_at' => 'datetime',
        'registered_works_count' => 'integer',
        'registered_characters_count' => 'integer',
    ];

    public function statusLabel(): string
    {
        if ($this->trashed()) {
            return '削除フラグ';
        }

        return match ($this->status) {
            'active' => '登用中',
            'paused' => '一時停止',
            'rejected' => '見送り',
            default => '申請中',
        };
    }
}
