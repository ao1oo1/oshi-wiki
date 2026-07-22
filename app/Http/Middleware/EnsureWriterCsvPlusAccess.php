<?php

namespace App\Http\Middleware;

use App\Services\BillingEntitlementService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWriterCsvPlusAccess
{
    public function __construct(
        private readonly BillingEntitlementService $entitlements
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response|RedirectResponse {
        $user = $request->user()?->loadMissing(
            'billingProfile.plan'
        );

        if (! $user) {
            return redirect()->route('login');
        }

        if ($this->entitlements->hasPlusAccess($user)) {
            return $next($request);
        }

        return redirect()
            ->route('writer.billing.index')
            ->with(
                'status',
                'CSVインポート/エクスポートはPlus限定です。'
            );
    }
}
