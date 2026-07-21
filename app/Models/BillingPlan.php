<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'monthly_price',
        'yearly_price',
        'limits',
        'stripe_monthly_price_id',
        'stripe_yearly_price_id',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'integer',
            'yearly_price' => 'integer',
            'limits' => 'array',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function billingProfiles(): HasMany
    {
        return $this->hasMany(UserBillingProfile::class);
    }
}
