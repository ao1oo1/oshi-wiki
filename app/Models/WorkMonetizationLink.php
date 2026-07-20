<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class WorkMonetizationLink extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'public_key', 'work_id', 'service_id', 'affiliate_program_id',
        'product_code', 'product_type', 'title', 'button_label',
        'campaign_code', 'availability_status', 'priority', 'is_active',
        'starts_at', 'ends_at', 'last_verified_at',
        'verification_method', 'verification_note',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'last_verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->public_key ??= (string) Str::ulid();

            if (auth()->check()) {
                $model->created_by ??= auth()->id();
                $model->updated_by ??= auth()->id();
            }
        });
    }

    public function scopeDisplayable(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where('availability_status', 'available')
            ->where(fn (Builder $query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $query) => $query->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(MonetizationService::class, 'service_id');
    }

    public function affiliateProgram(): BelongsTo
    {
        return $this->belongsTo(AffiliateProgram::class, 'affiliate_program_id');
    }
}
