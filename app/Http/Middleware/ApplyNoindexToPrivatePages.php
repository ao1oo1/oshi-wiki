<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyNoindexToPrivatePages
{
    private const ROBOTS_CONTENT = 'noindex,follow';

    private const HEADER_CONTENT = 'noindex, follow';

    /**
     * Authentication screens that must not appear in search results.
     *
     * Password reset tokens are also covered by reset-password/*.
     */
    private const AUTH_PATHS = [
        'login',
        'register',
        'forgot-password',
        'reset-password',
        'reset-password/*',
        'verify-email',
        'verify-email/*',
        'confirm-password',
        'email/verification-notification',
    ];

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response = $next($request);

        if (! $this->shouldNoindex($request)) {
            return $response;
        }

        $response->headers->set(
            'X-Robots-Tag',
            self::HEADER_CONTENT
        );

        if (! $this->isHtmlResponse($response)) {
            return $response;
        }

        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return $response;
        }

        $meta = sprintf(
            '<meta name="robots" content="%s">',
            self::ROBOTS_CONTENT
        );

        $updated = preg_replace(
            '/<meta\s+[^>]*name=["\']robots["\'][^>]*>/i',
            $meta,
            $content,
            1,
            $robotsReplacementCount
        );

        if (! is_string($updated)) {
            return $response;
        }

        if ($robotsReplacementCount === 0) {
            $updated = preg_replace(
                '/<\/head>/i',
                "    {$meta}\n</head>",
                $updated,
                1,
                $headReplacementCount
            );

            if (
                ! is_string($updated)
                || $headReplacementCount !== 1
            ) {
                return $response;
            }
        }

        $response->setContent($updated);

        return $response;
    }

    public function shouldNoindex(
        Request $request
    ): bool {
        if (
            $request->is('admin')
            || $request->is('admin/*')
            || $request->is('writer')
            || $request->is('writer/*')
        ) {
            return true;
        }

        foreach (self::AUTH_PATHS as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }

    private function isHtmlResponse(
        Response $response
    ): bool {
        $contentType = strtolower(
            (string) $response->headers->get(
                'Content-Type',
                ''
            )
        );

        return str_contains($contentType, 'text/html');
    }
}
