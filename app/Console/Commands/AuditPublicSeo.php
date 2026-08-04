<?php

namespace App\Console\Commands;

use DOMDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AuditPublicSeo extends Command
{
    protected $signature = 'seo:audit
        {--limit=50 : Maximum sitemap URLs to inspect}
        {--timeout=15 : Timeout per request in seconds}';

    protected $description =
        '公開ページのtitle・description・canonical・noindex・h1・HTTP状態を検査';

    public function handle(): int
    {
        $sitemap = public_path('sitemap.xml');

        if (! is_file($sitemap)) {
            $this->error(
                'public/sitemap.xml is missing. '
                . 'Run sitemap:generate first.'
            );

            return self::FAILURE;
        }

        $xml = simplexml_load_file($sitemap);

        if ($xml === false) {
            $this->error('sitemap.xml is invalid.');

            return self::FAILURE;
        }

        $urls = collect($xml->url)
            ->map(fn ($entry) => (string) $entry->loc)
            ->filter()
            ->take((int) $this->option('limit'));

        $failures = [];

        foreach ($urls as $url) {
            try {
                $response = Http::timeout(
                    (int) $this->option('timeout')
                )
                    ->withUserAgent(
                        'Oshi-Wiki-SEO-Audit/1.0'
                    )
                    ->get($url);
            } catch (\Throwable $exception) {
                $failures[] = "{$url}: request failed";
                continue;
            }

            if ($response->status() !== 200) {
                $failures[] =
                    "{$url}: HTTP {$response->status()}";
                continue;
            }

            $html = $response->body();
            $dom = new DOMDocument();

            libxml_use_internal_errors(true);
            $loaded = $dom->loadHTML(
                $html,
                LIBXML_NOWARNING | LIBXML_NOERROR
            );
            libxml_clear_errors();

            if (! $loaded) {
                $failures[] = "{$url}: invalid HTML";
                continue;
            }

            $titles = $dom->getElementsByTagName('title');
            $h1s = $dom->getElementsByTagName('h1');

            if (
                $titles->length !== 1
                || trim($titles->item(0)?->textContent ?? '') === ''
            ) {
                $failures[] = "{$url}: title missing/duplicate";
            }

            if ($h1s->length !== 1) {
                $failures[] =
                    "{$url}: h1 count={$h1s->length}";
            }

            $description = null;
            $canonical = null;
            $robots = null;

            foreach ($dom->getElementsByTagName('meta') as $meta) {
                $name = strtolower(
                    $meta->getAttribute('name')
                );

                if ($name === 'description') {
                    $description = trim(
                        $meta->getAttribute('content')
                    );
                }

                if ($name === 'robots') {
                    $robots = strtolower(
                        $meta->getAttribute('content')
                    );
                }
            }

            foreach ($dom->getElementsByTagName('link') as $link) {
                if (
                    strtolower($link->getAttribute('rel'))
                    === 'canonical'
                ) {
                    $canonical = rtrim(
                        $link->getAttribute('href'),
                        '/'
                    );
                }
            }

            if (blank($description)) {
                $failures[] = "{$url}: description missing";
            }

            if (
                $canonical === null
                || $canonical !== rtrim($url, '/')
            ) {
                $failures[] = "{$url}: canonical mismatch";
            }

            if (
                $robots !== null
                && str_contains($robots, 'noindex')
            ) {
                $failures[] = "{$url}: unexpected noindex";
            }
        }

        if ($failures !== []) {
            foreach ($failures as $failure) {
                $this->error($failure);
            }

            $this->error(
                'SEO audit failed: '
                . count($failures)
                . ' issue(s)'
            );

            return self::FAILURE;
        }

        $this->info(
            'SEO audit passed: '
            . $urls->count()
            . ' URL(s)'
        );

        return self::SUCCESS;
    }
}
