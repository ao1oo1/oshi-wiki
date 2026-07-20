<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBillingProfile extends Model
{
    public const ACTIVE_STATUSES = [
        'active',
        'trialing',
        'past_due_grace',
        'canceling',
    ];

    protected $fillable = [
        'user_id',
        'billing_plan_id',
        'stripe_customer_id',
        'stripe_subscription_id',
        'billing_cycle',
        'status',
        'current_period_start',
        'current_period_end',
        'cancel_at',
        'canceled_at',
        'grace_period_ends_at',
        'last_payment_succeeded_at',
        'last_payment_failed_at',
        'last_payment_failure_code',
    ];

    protected function casts(): array
    {
        return [
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'cancel_at' => 'datetime',
            'canceled_at' => 'datetime',
            'grace_period_ends_at' => 'datetime',
            'last_payment_succeeded_at' => 'datetime',
            'last_payment_failed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(BillingPlan::class, 'billing_plan_id');
    }

    public function hasPaidAccess(): bool
    {
        if (! in_array($this->status, self::ACTIVE_STATUSES, true)) {
            return false;
        }

        if (
            $this->status === 'past_due_grace'
            && $this->grace_period_ends_at?->isPast()
        ) {
            return false;
        }

        if (
            $this->current_period_end
            && $this->current_period_end->isPast()
            && ! $this->grace_period_ends_at?->isFuture()
        ) {
            return false;
        }

        return $this->plan?->slug === 'plus';
    }
}
