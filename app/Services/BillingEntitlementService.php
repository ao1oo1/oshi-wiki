<?php

namespace App\Services;

use App\Models\User;

class BillingEntitlementService
{
    public function planSlug(User $user): string
    {
        if ($user->isSuperAdmin()) {
            return 'plus';
        }

        $profile = $user->billingProfile;

        return $profile?->hasPaidAccess() ? 'plus' : 'free';
    }

    public function hasPlusAccess(User $user): bool
    {
        return $this->planSlug($user) === 'plus';
    }

    public function limit(User $user, string $resource): ?int
    {
        if ($user->isSuperAdmin()) {
            return null;
        }

        $plan = $this->planSlug($user);

        return config("billing.plans.{$plan}.limits.{$resource}");
    }
}
