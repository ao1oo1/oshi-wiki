<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $character->name }} | {{ $character->work?->title }} | <span class="oshi-brand-mark">✦</span> Oshi-Wiki</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body >
    <header class="oshi-header">
        <div class="oshi-container oshi-header-inner">
            <a href="{{ route('public.home') }}" class="oshi-brand" style="color:#2D3748;text-decoration:none;">
                Oshi-Wiki
            </a>

            <a
                href="{{ route('login') }}"
                style="display:inline-block;background:#FED7E2;color:#2D3748;padding:8px 14px;border-radius:8px;font-weight:bold;text-decoration:none;"
            >
                管理者ログイン
            </a>
        </div>
    </header>

    <main class="oshi-container">
        <div >
            <div class="mb-6 flex flex-wrap gap-3">
                <a
                    href="{{ route('public.works.index') }}"
                    style="display:inline-block;background:#A0AEC0;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                >
                    作品一覧へ戻る
                </a>

                @if ($character->work)
                    <a
                        href="{{ route('public.works.show', $character->work) }}"
                        style="display:inline-block;background:#2D3748;color:#ffffff;padding:10px 18px;border-radius:8px;font-weight:bold;text-decoration:none;"
                    >
                        作品詳細へ戻る
                    </a>
                @endif
            </div>

            <section class="oshi-card">
                <p class="mb-2 text-sm text-gray-500">
                    {{ $character->work?->title }}
                </p>

                <h1 class="mb-2 text-3xl font-bold">
                    {{ $character->name }}
                </h1>

                @if ($character->name_kana)
                    <p class="mb-4 text-gray-500">
                        {{ $character->name_kana }}
                    </p>
                @endif

                @if ($character->tags->count())
                    <div class="mb-5 flex flex-wrap gap-2">
                        @foreach ($character->tags as $tag)
                            <span
                                class="oshi-chip"
                                style="background:#FED7E2;color:#2D3748;"
                            >
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <h2 class="mb-1 font-semibold">年齢</h2>
                        <p class="oshi-card">
                            {{ $character->age ?: '未設定' }}
                        </p>
                    </div>

                    <div>
                        <h2 class="mb-1 font-semibold">一人称</h2>
                        <p class="oshi-card">
                            {{ $character->first_person ?: '未設定' }}
                        </p>
                    </div>

                    <div>
                        <h2 class="mb-1 font-semibold">所属</h2>
                        <p class="oshi-card">
                            {{ $character->affiliation ?: '未設定' }}
                        </p>
                    </div>

                    <div>
                        <h2 class="mb-1 font-semibold">学年クラス</h2>
                        <p class="oshi-card">
                            {{ $character->grade_class ?: '未設定' }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 space-y-5">
                    <div>
                        <h2 class="mb-1 font-semibold">口調</h2>
                        <div class="whitespace-pre-wrap rounded bg-gray-50 p-4">{{ $character->tone ?: '未設定' }}</div>
                    </div>

                    <div>
                        <h2 class="mb-1 font-semibold">口調の例</h2>
                        <div class="whitespace-pre-wrap rounded bg-gray-50 p-4">{{ $character->tone_examples ?: '未設定' }}</div>
                    </div>

                    <div>
                        <h2 class="mb-1 font-semibold">性格</h2>
                        <div class="whitespace-pre-wrap rounded bg-gray-50 p-4">{{ $character->personality ?: '未設定' }}</div>
                    </div>

                    <div>
                        <h2 class="mb-1 font-semibold">外見の特徴</h2>
                        <div class="whitespace-pre-wrap rounded bg-gray-50 p-4">{{ $character->appearance ?: '未設定' }}</div>
                    </div>

                    <div>
                        <h2 class="mb-1 font-semibold">背景・経歴</h2>
                        <div class="whitespace-pre-wrap rounded bg-gray-50 p-4">{{ $character->background ?: '未設定' }}</div>
                    </div>
                </div>
            </section>

            <section class="oshi-card">
                <h2 class="mb-4 text-2xl font-bold">
                    このキャラクターから見た関係性
                </h2>

                @if ($character->outgoingRelationships->count())
                    <div class="oshi-table-wrap">
                        <table class="oshi-table">
                            <thead>
                                <tr class="border-b bg-gray-50">
                                    <th class="px-4 py-2 text-left">相手</th>
                                    <th class="px-4 py-2 text-left">呼ばれ方</th>
                                    <th class="px-4 py-2 text-left">関係性</th>
                                    <th class="px-4 py-2 text-left">印象・気持ち等</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($character->outgoingRelationships as $relation)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">
                                            {{ $relation->toCharacter?->name }}
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

            <section class="oshi-card">
                <h2 class="mb-4 text-2xl font-bold">
                    他キャラクターから見たこのキャラクター
                </h2>

                @if ($character->incomingRelationships->count())
                    <div class="oshi-table-wrap">
                        <table class="oshi-table">
                            <thead>
                                <tr class="border-b bg-gray-50">
                                    <th class="px-4 py-2 text-left">相手</th>
                                    <th class="px-4 py-2 text-left">このキャラクターの呼ばれ方</th>
                                    <th class="px-4 py-2 text-left">関係性</th>
                                    <th class="px-4 py-2 text-left">印象・気持ち等</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($character->incomingRelationships as $relation)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">
                                            {{ $relation->fromCharacter?->name }}
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
        </div>
    </main>
</body>
</html>
