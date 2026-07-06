<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! (bool) ($user->is_super_admin ?? false)) {
            abort(403, 'この操作は最高管理者のみ実行できます。');
        }

        return $next($request);
    }
}
