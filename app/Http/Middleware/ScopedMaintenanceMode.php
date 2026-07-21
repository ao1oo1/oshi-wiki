<?php

namespace App\Http\Middleware;

use App\Services\ScopedMaintenanceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopedMaintenanceMode
{
    public function __construct(
        private readonly ScopedMaintenanceService $maintenance
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        if ($this->shouldShowMaintenance($request)) {
            return response()
                ->view('errors.503', status: 503)
                ->header(
                    'X-Robots-Tag',
                    'noindex, nofollow'
                )
                ->header('Retry-After', '60');
        }

        return $next($request);
    }

    private function shouldShowMaintenance(
        Request $request
    ): bool {
        if (
            $this->maintenance->isActive('public')
            && $request->is('/')
        ) {
            return true;
        }

        if (
            $this->maintenance->isActive('writer')
            && (
                $request->is('writer')
                || $request->is('writer/*')
                || (
                    $request->is('dashboard')
                    && ! $request->user()?->canAccessAdmin()
                )
            )
        ) {
            return true;
        }

        if (
            $this->maintenance->isActive('contributor')
            && (
                $request->is('admin')
                || $request->is('admin/*')
            )
        ) {
            $user = $request->user();

            if ($user === null) {
                return false;
            }

            return ! $user->isSuperAdmin();
        }

        return false;
    }
}
