<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Oshi-Wiki') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Zen+Maru+Gothic:wght@500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="oshi-admin-body">
        @auth
            @if (request()->is('admin') || request()->is('admin/*'))
                @include('admin.partials.mobile-navigation')
            @endif
        @endauth

    <div class="oshi-admin-layout">
        <aside class="oshi-admin-sidebar">
            <a class="oshi-brand" href="{{ route('public.works.index') }}">
                <img
                    src="{{ asset('images/oshiwiki-logo.svg') }}"
                    alt="Oshi-Wiki"
                    class="oshi-admin-logo-img"
                >
            </a>

            @auth
                <p class="oshi-sidebar-user">
                    {{ Auth::user()->name ?? '管理者' }}<br>
                    <span class="oshi-muted">管理ユーザー</span>
                </p>
            @endauth

            @if (auth()->check() && request()->routeIs('admin.*') && auth()->user()?->canAccessAdmin())
                @include('admin.partials.navigation')
            @elseif (auth()->check() && request()->routeIs('writer.*') && auth()->user()?->isWriter())
                @include('writer.partials.navigation')
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="oshi-btn oshi-btn-sub" style="width:100%;">
                    ログアウト
                </button>
            </form>
        </aside>

        <main
            class="oshi-admin-main"
            id="page-top"
        >
            @if (request()->routeIs('admin.*'))
                <div
                    class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                    data-admin-top-navigation
                >
                    @include('admin.partials.breadcrumbs')

                    <div class="shrink-0">
                        @include(
                            'partials.page-jump-navigation',
                            ['position' => 'top']
                        )
                    </div>
                </div>
            @else
                @include(
                    'partials.page-jump-navigation',
                    ['position' => 'top']
                )
            @endif

            @isset($header)
                <div class="oshi-admin-title">
                    {{ $header }}
                </div>
            @endisset

            {{ $slot }}

            <div id="page-bottom"></div>

            @include(
                'partials.page-jump-navigation',
                ['position' => 'bottom']
            )
        </main>
    </div>
    @include('admin.partials.staff-mobile-ui')
</body>
</html>
