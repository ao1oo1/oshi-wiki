<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Response;

class BlockSuspiciousIp
{
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $ip = (string) $request->ip();
        $blocked = config('security.blocked_ips', []);

        if (
            $ip !== ''
            && is_array($blocked)
            && IpUtils::checkIp($ip, $blocked)
        ) {
            abort(
                403,
                'このネットワークからのアクセスは制限されています。'
            );
        }

        return $next($request);
    }
}
