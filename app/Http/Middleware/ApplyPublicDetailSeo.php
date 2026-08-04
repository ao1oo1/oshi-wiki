<?php

namespace App\Http\Middleware;

use App\Models\Character;
use App\Models\Work;
use App\Services\PublicSeoContentBuilder;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyPublicDetailSeo
{
    public function __construct(
        private readonly PublicSeoContentBuilder $builder
    ) {
    }

    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $response = $next($request);

        if (
            ! $request->isMethod('GET')
            || $response->getStatusCode() !== 200
            || ! $this->isHtml($response)
        ) {
            return $response;
        }

        $model = $this->resolveModel($request);

        if ($model instanceof Work) {
            return $this->replace(
                $response,
                $this->builder->workTitle($model),
                $this->builder->workDescription($model)
            );
        }

        if ($model instanceof Character) {
            return $this->replace(
                $response,
                $this->builder->characterTitle($model),
                $this->builder->characterDescription($model)
            );
        }

        return $response;
    }

    private function resolveModel(
        Request $request
    ): Work|Character|null {
        $routeName = $request->route()?->getName();

        if (! in_array(
            $routeName,
            [
                'public.works.show',
                'public.characters.show',
            ],
            true
        )) {
            return null;
        }

        foreach ($request->route()?->parameters() ?? [] as $value) {
            if ($value instanceof Work || $value instanceof Character) {
                return $value;
            }
        }

        return null;
    }

    private function replace(
        Response $response,
        string $title,
        string $description
    ): Response {
        $html = $response->getContent();

        if (! is_string($html) || $html === '') {
            return $response;
        }

        $escapedTitle = htmlspecialchars(
            $title,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        $escapedDescription = htmlspecialchars(
            $description,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        $html = preg_replace(
            '/<title>.*?<\/title>/is',
            "<title>{$escapedTitle}</title>",
            $html,
            1
        ) ?? $html;

        $descriptionMeta = sprintf(
            '<meta name="description" content="%s">',
            $escapedDescription
        );

        $html = preg_replace(
            '/<meta\s+[^>]*name=["\']description["\'][^>]*>/i',
            $descriptionMeta,
            $html,
            1,
            $count
        ) ?? $html;

        if ($count === 0) {
            $html = preg_replace(
                '/<\/head>/i',
                "    {$descriptionMeta}\n</head>",
                $html,
                1
            ) ?? $html;
        }

        $response->setContent($html);

        return $response;
    }

    private function isHtml(Response $response): bool
    {
        return str_contains(
            strtolower(
                (string) $response->headers->get(
                    'Content-Type',
                    ''
                )
            ),
            'text/html'
        );
    }
}
