<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AffiliateProgram extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_id', 'name', 'provider_name', 'url_template',
        'affiliate_identifier', 'additional_parameters', 'allowed_hosts',
        'code_validation_pattern', 'code_example', 'priority',
        'is_default', 'is_affiliate', 'is_active', 'starts_at', 'ends_at',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'additional_parameters' => 'array',
            'allowed_hosts' => 'array',
            'priority' => 'integer',
            'is_default' => 'boolean',
            'is_affiliate' => 'boolean',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(MonetizationService::class, 'service_id');
    }

    public function workLinks(): HasMany
    {
        return $this->hasMany(WorkMonetizationLink::class, 'affiliate_program_id');
    }
}
