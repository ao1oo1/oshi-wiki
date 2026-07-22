<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWriterCsvPlusAccess
{
    public function handle(
        Request $request,
        Closure $next
    ): Response|RedirectResponse {
        $user = $request->user()?->loadMissing('billingProfile');

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $status = (string) ($user->billingProfile?->status ?? '');

        if (in_array($status, ['active', 'trialing', 'past_due_grace'], true)) {
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
