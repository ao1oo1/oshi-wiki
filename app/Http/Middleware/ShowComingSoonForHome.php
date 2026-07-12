<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ShowComingSoonForHome
{
    public function handle(Request $request, Closure $next): Response
    {
        if (
            config('app.coming_soon_enabled') === true
            && $request->is('/')
            && $request->isMethod('GET')
        ) {
            return response()
                ->view('public.coming-soon')
                ->header('X-Robots-Tag', 'noindex, nofollow');
        }

        return $next($request);
    }
}
