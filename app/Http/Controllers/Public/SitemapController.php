<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\Work;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $works = Work::query()
            ->where('status', 'published')
            ->orderBy('id')
            ->get(['id', 'updated_at']);

        $characters = Character::query()
            ->where('status', 'published')
            ->whereHas(
                'linkedWorks',
                fn ($query) => $query->where(
                    'works.status',
                    'published'
                )
            )
            ->orderBy('id')
            ->get(['id', 'updated_at']);

        $staticUrls = collect([
            rtrim(config('app.url'), '/').'/',
            route('public.works.index'),
            route('public.tags.index'),
            route('public.about.show'),
            route('public.writing-tool.show'),
            route('public.legal'),
            route('public.privacy'),
            route('public.terms'),
            route('public.pricing'),
            route('public.billing-policy'),
        ])->unique()->values();

        return response()
            ->view(
                'public.sitemap',
                compact(
                    'staticUrls',
                    'works',
                    'characters'
                )
            )
            ->header(
                'Content-Type',
                'application/xml; charset=UTF-8'
            )
            ->header(
                'Cache-Control',
                'public, max-age=3600'
            )
            ->header(
                'X-Robots-Tag',
                'noindex, follow'
            );
    }
}
