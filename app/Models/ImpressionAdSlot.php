<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImpressionAdSlot extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'monetization_service_id',
        'name',
        'page_scope',
        'position',
        'device_type',
        'priority',
        'is_active',
        'starts_at',
        'ends_at',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(
            MonetizationService::class,
            'monetization_service_id'
        );
    }

    public function scopeCurrentlyDisplayable(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('is_active', true)
            ->where(function (Builder $query) use ($now): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function (Builder $query) use ($now): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $now);
            });
    }
}
