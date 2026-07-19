<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>{{ $staff->public_username ?: $staff->name }}｜スタッフプロフィール｜Oshi-Wiki</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="page-top"></div>

    @include(
        'partials.page-jump-navigation',
        ['position' => 'top']
    )

    @include('public.partials.header')
    <main class="oshi-container py-10">
        <div class="rounded bg-white p-6 shadow">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                <div>
                    @if ($staff->profile_icon_path)
                        <img
                            src="{{ asset('storage/' . $staff->profile_icon_path) }}"
                            alt="{{ $staff->public_username ?: $staff->name }}"
                            class="h-24 w-24 rounded-full object-cover"
                        >
                    @else
                        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-gray-200 text-2xl font-bold text-gray-500">
                            {{ mb_substr($staff->public_username ?: $staff->name, 0, 1) }}
                        </div>
                    @endif
                </div>

                <div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($characters as $character)
                        <div class="rounded bg-white p-4 shadow">
                            <h3 class="font-semibold">{{ $character->name }}</h3>
                            @if (! empty($character->description))
                                <p class="mt-2 line-clamp-3 text-sm text-gray-600">{{ $character->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </main>

    <div id="page-bottom"></div>

    @include(
        'partials.page-jump-navigation',
        ['position' => 'bottom']
    )
</body>
</html>
