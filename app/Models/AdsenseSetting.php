<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdsenseSetting extends Model
{
    protected $fillable = [
        'is_enabled',
        'publisher_id',
        'auto_ads_enabled',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'auto_ads_enabled' => 'boolean',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'is_enabled' => true,
                'publisher_id' => 'ca-pub-3916030283806562',
                'auto_ads_enabled' => true,
            ]
        );
    }
}
