<!DOCTYPE html>
<html lang="ja">
<head>
    @include('partials.favicon')
    @include('partials.google-analytics')
    @include('partials.seo-meta')


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | Oshi-Wiki</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Google AdSense site verification --}}
    <script
        async
        src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3916030283806562"
        crossorigin="anonymous"
    ></script>
</head>
<body class="bg-[#FFF9FB] text-[#2D3748]">
    @include(
        'public.partials.impression-ads',
        ['position' => 'page_top']
    )

@include('public.partials.header')
<main class="mx-auto max-w-4xl px-5 py-10">
    <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-10">
        <p class="text-sm font-bold text-[#A0AEC0]">Oshi-Wiki</p>
        <h1 class="mt-2 text-3xl font-bold">{{ $title }}</h1>
        <p class="mt-3 text-sm text-[#718096]">制定日：2026年7月21日</p>
        <div class="mt-8 space-y-8 leading-8 text-[#4A5568]">
