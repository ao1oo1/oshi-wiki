@php
    echo '<?xml version="1.0" encoding="UTF-8"?>';
@endphp
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($staticUrls as $url)
    <url>
        <loc>{{ $url }}</loc>
    </url>
@endforeach
@foreach ($works as $work)
    <url>
        <loc>{{ route('public.works.show', $work) }}</loc>
        @if ($work->updated_at)
            <lastmod>{{ $work->updated_at->toAtomString() }}</lastmod>
        @endif
    </url>
@endforeach
@foreach ($characters as $character)
    <url>
        <loc>{{ route('public.characters.show', $character) }}</loc>
        @if ($character->updated_at)
            <lastmod>{{ $character->updated_at->toAtomString() }}</lastmod>
        @endif
    </url>
@endforeach
</urlset>
