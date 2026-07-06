<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $work->title }} | Oshi-Wiki</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <header class="oshi-header">
        <div class="oshi-container oshi-header-inner">
            <a href="{{ route('public.home') }}" class="flex items-center">
                <img
                    src="{{ asset('images/oshiwiki-logo.png') }}"
                    alt="Oshi-Wiki"
                    class="h-12 w-auto"
                >
            </a>

            <a
                href="{{ route('login') }}"
                style="display:inline-block;background:#FED7E2;color:#2D3748;padding:8px 14px;border-radius:8px;font-weight:bold;text-decoration:none;"
            >
                管理者ログイン
            </a>
        </div>
    </header>

    <main class="oshi-container space-y-8">
        <div class="mb-6">
            <a
                href="{{ route('public.works.index') }}"
                style="display:inline-block;background:#A0AEC0;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
            >
                作品一覧へ戻る
            </a>
        </div>

        <section class="oshi-card">
            <p class="mb-2 text-sm text-gray-500">
                {{ $work->genre ?: 'ジャンル未設定' }}
                @if ($work->original_media)
                    / {{ $work->original_media }}
                @endif
            </p>

            <h1 class="mb-2 text-3xl font-bold">
                {{ $work->title }}
            </h1>

            @if ($work->title_kana)
                <p class="mb-4 text-gray-500">
                    {{ $work->title_kana }}
                </p>
            @endif

            @if ($work->tags->count())
                <div class="mb-5 flex flex-wrap gap-2">
                    @foreach ($work->tags as $tag)
                        <span
                            class="oshi-chip"
                            style="background:#FED7E2;color:#2D3748;"
                        >
                            {{ $tag->name }}
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="text-gray-700 leading-relaxed">
                {!! nl2br(e(trim($work->description ?: '説明はまだ登録されていません。'))) !!}
            </div>
        </section>

        <section class="oshi-card">
            <h2 class="mb-4 text-2xl font-bold">
                キャラクター
            </h2>

            @if ($work->characters->count())
                <div class="oshi-card-grid">
                    @foreach ($work->characters as $character)
                        <article class="oshi-card">
                            <p class="mb-1 text-sm text-gray-500">
                                {{ $character->affiliation ?: '所属未設定' }}
                            </p>

                            <h3 class="mb-2 text-xl font-bold">
                                {{ $character->name }}
                            </h3>

                            @if ($character->name_kana)
                                <p class="mb-2 text-sm text-gray-500">
                                    {{ $character->name_kana }}
                                </p>
                            @endif

                            <div class="mb-3 text-sm text-gray-700">
                                @if ($character->age)
                                    <span>年齢：{{ $character->age }}</span>
                                @endif

                                @if ($character->first_person)
                                    <span>一人称：{{ $character->first_person }}</span>
                                @endif
                            </div>

                            @if ($character->tags->count())
                                <div class="mb-3 flex flex-wrap gap-2">
                                    @foreach ($character->tags as $tag)
                                        <span class="oshi-chip">
                                            {{ $tag->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <p class="mb-4 line-clamp-3 text-gray-700">
                                {{ $character->personality ?: $character->background ?: '説明はまだ登録されていません。' }}
                            </p>

                            <a
                                href="{{ route('public.characters.show', $character) }}"
                                style="display:inline-block;background:#2D3748;color:#ffffff;padding:8px 14px;border-radius:8px;font-weight:bold;text-decoration:none;"
                            >
                                キャラクター詳細
                            </a>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600">
                    公開中のキャラクターはまだ登録されていません。
                </p>
            @endif
        </section>

        <section class="oshi-card">
            <h2 class="mb-4 text-2xl font-bold">
                キャラクター関係性
            </h2>

            @if ($work->characterRelationships->count())
                <div class="oshi-table-wrap">
                    <table class="oshi-table">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="px-4 py-2 text-left">キャラクター</th>
                                <th class="px-4 py-2 text-left">相手</th>
                                <th class="px-4 py-2 text-left">呼ばれ方</th>
                                <th class="px-4 py-2 text-left">関係性</th>
                                <th class="px-4 py-2 text-left">印象・気持ち等</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($work->characterRelationships as $relation)
                                <tr class="border-b">
                                    <td class="px-4 py-2">
                                        {{ $relation->fromCharacter?->name ?: '未設定' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $relation->toCharacter?->name ?: '未設定' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $relation->called_name ?: '未設定' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $relation->relationship ?: '未設定' }}
                                    </td>
                                    <td class="px-4 py-2">
                                        {{ $relation->impression ?: '未設定' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600">
                    公開中の関係性はまだ登録されていません。
                </p>
            @endif
        </section>

        @include('public.partials.helpful-button', [
            'targetType' => 'work',
            'targetId' => $work->id,
            'helpfulCount' => $work->helpful_count ?? 0,
        ])
    </main>
</body>
</html>
