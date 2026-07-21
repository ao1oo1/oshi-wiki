<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWriterDataWritable
{
    private const ALLOWED_ROUTE_NAMES = [
        'writer.billing.index',
        'writer.billing.checkout',
        'writer.billing.portal',
        'writer.csv.export',
        'writer.csv.sample',
    ];

    public function handle(
        Request $request,
        Closure $next
    ): Response|RedirectResponse {
        if (
            $request->isMethodSafe()
            || in_array(
                $request->route()?->getName(),
                self::ALLOWED_ROUTE_NAMES,
                true
            )
        ) {
            return $next($request);
        }

        $user = $request->user()?->loadMissing('billingProfile');

        if (
            ! $user
            || $user->isSuperAdmin()
            || ! $user->billingProfile?->isInRetentionPeriod()
        ) {
            return $next($request);
        }

        return redirect()
            ->route('writer.billing.index')
            ->with(
                'status',
                'Plusの利用期間終了後はデータ保管期間のため、'
                .'閲覧とCSVエクスポートのみ利用できます。'
                .'再加入すると編集機能を再開できます。'
            );
    }
}
