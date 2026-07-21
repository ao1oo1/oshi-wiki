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
            && $this->isPublicArea($request)
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

    private function isPublicArea(Request $request): bool
    {
        $excludedPatterns = [
            'admin',
            'admin/*',
            'writer',
            'writer/*',
            'dashboard',
            'login',
            'logout',
            'register',
            'forgot-password',
            'reset-password/*',
            'verify-email',
            'verify-email/*',
            'email/verification-notification',
            'confirm-password',
            'password',
            'profile',
            'profile/*',
            'stripe/webhook',
            'up',
        ];

        foreach ($excludedPatterns as $pattern) {
            if ($request->is($pattern)) {
                return false;
            }
        }

        return true;
    }
}
