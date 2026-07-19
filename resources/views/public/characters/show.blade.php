<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $character->name }} | {{ $character->linkedWorks->first()?->title ?? $character->work?->title ?? '作品未設定' }} | Oshi-Wiki</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('public.partials.header')

    <div id="page-top"></div>

    @include(
        'partials.page-jump-navigation',
        ['position' => 'top']
    )

    @php
        $sourceTypeLabel = \App\Models\Character::SOURCE_TYPES[$character->source_type] ?? null;
        $sourceReliabilityLabel = \App\Models\Character::SOURCE_RELIABILITIES[$character->source_reliability] ?? null;
        $spoilerLabel = \App\Models\Character::SPOILER_LEVELS[$character->spoiler_level] ?? 'なし';

        $spoilerMessages = [
            'minor' => '一部ネタバレを含みます。',
            'major' => '物語の重要な展開に関するネタバレを含みます。',
            'latest_chapter' => '単行本未収録の内容を含む可能性があります。',
            'anime_spoiler' => 'アニメ未視聴者向けのネタバレを含みます。',
        ];

        $sourceUrls = collect(preg_split('/\R/u', (string) $character->source_url))
            ->map(fn ($url) => trim($url))
            ->filter()
            ->values();

        $publishedLinkedWorks = $character->linkedWorks
            ->where('status', 'published')
            ->values();

        $primaryPublishedWork = $publishedLinkedWorks
            ->first(fn ($work) => (bool) ($work->pivot?->is_primary))
            ?? $publishedLinkedWorks->first();

        $additionalPublishedWorks = $publishedLinkedWorks
            ->reject(
                fn ($work) => $primaryPublishedWork
                    && (int) $work->id === (int) $primaryPublishedWork->id
            )
            ->values();

        $pageWorkTitle = $primaryPublishedWork?->title
            ?? $publishedLinkedWorks->pluck('title')->first()
            ?? '作品未設定';
    @endphp

    <main class="oshi-container">
        <div class="mb-6 flex flex-wrap gap-3">
            <a
                href="{{ route('public.works.index') }}"
                class="oshi-btn oshi-btn-sub"
            >
                作品一覧へ戻る
            </a>

            @if ($primaryPublishedWork)
                <a
                    href="{{ route('public.works.show', $primaryPublishedWork) }}"
                    class="oshi-btn oshi-btn-sub"
                >
                    作品詳細へ戻る
                </a>
            @endif
        </div>

        @if (($character->spoiler_level ?? 'none') !== 'none')
            <section class="mb-6 rounded-3xl border border-amber-300 bg-amber-50 p-5 text-amber-900">
                <p class="font-bold">
                    ネタバレ：{{ $spoilerLabel }}
                </p>
                <p class="mt-1 text-sm">
                    {{ $spoilerMessages[$character->spoiler_level] ?? 'ネタバレを含みます。' }}
                </p>
            </section>
        @endif

        <section class="oshi-card">
            <p class="mb-2 text-sm text-gray-500">
                {{ $pageWorkTitle }}
            </p>

            <h1 class="mb-1 text-3xl font-bold">
                {{ $character->name }}
            </h1>

            @if ($character->name_kana)
                <p class="text-gray-500">
                    {{ $character->name_kana }}
                </p>
            @endif

            @if ($character->name_english)
                <p class="mt-1 text-sm text-gray-500">
                    {{ $character->name_english }}
                </p>
            @endif

            @if ($character->tags->count())
                <div class="mt-5 flex flex-wrap gap-2">
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
        </section>

        <section class="oshi-card">
            <h2 class="mb-4 text-2xl font-bold">紐付いている作品</h2>

            <div class="flex flex-wrap gap-2">
                @foreach ($publishedLinkedWorks as $linkedWork)
                    <a
                        href="{{ route('public.works.show', $linkedWork) }}"
                        class="rounded-full border border-[#FED7E2] bg-[#FFF5F7] px-4 py-2 text-sm font-bold text-[#2D3748]"
                    >
                        {{ $linkedWork->title }}

                        @if (
                            $primaryPublishedWork
                            && (int) $linkedWork->id
                                === (int) $primaryPublishedWork->id
                        )
                            <span class="ml-1 text-xs text-[#718096]">
                                主作品
                            </span>
                        @else
                            <span class="ml-1 text-xs text-[#718096]">
                                追加作品
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>

        <section class="oshi-card">
            <h2 class="mb-5 text-2xl font-bold">基本情報</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    '本名' => $character->real_name,
                    '別名・愛称' => $character->aliases,
                    '性別' => $character->gender,
                    '年齢' => $character->age,
                    '生年月日・誕生日' => $character->birthday,
                    '身長' => $character->height,
                    '体重' => $character->weight,
                    '血液型' => $character->blood_type,
                    '出身地' => $character->birthplace,
                    '種族' => $character->species,
                    '所属' => $character->affiliation,
                    '学校・学年・クラス' => $character->school_grade_class,
                    '職業・役職' => $character->occupation_position,
                    '家族構成' => $character->family_structure,
                ] as $label => $value)
                    @if (filled($value))
                        <div>
                            <h3 class="mb-1 font-semibold">{{ $label }}</h3>
                            <div class="whitespace-pre-wrap rounded-2xl bg-gray-50 p-4">{{ $value }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>

        @foreach ([
            '外見' => $character->appearance,
            '性格・特徴' => $character->personality,
        ] as $label => $value)
            @if (filled($value))
                <section class="oshi-card">
                    <h2 class="mb-3 text-2xl font-bold">{{ $label }}</h2>
                    <div class="whitespace-pre-wrap rounded-2xl bg-gray-50 p-4">{{ $value }}</div>
                </section>
            @endif
        @endforeach

        @if (
            filled($character->first_person)
            || filled($character->second_person)
            || filled($character->basic_tone)
            || filled($character->catchphrases)
            || filled($character->distinctive_speech)
            || filled($character->tone_by_relationship)
            || filled($character->short_quote_examples)
        )
            <section class="oshi-card">
                <h2 class="mb-5 text-2xl font-bold">一人称・口調</h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach ([
                        '一人称' => $character->first_person,
                        '二人称' => $character->second_person,
                    ] as $label => $value)
                        @if (filled($value))
                            <div>
                                <h3 class="mb-1 font-semibold">{{ $label }}</h3>
                                <div class="whitespace-pre-wrap rounded-2xl bg-gray-50 p-4">{{ $value }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <div class="mt-5 space-y-5">
                    @foreach ([
                        '基本口調' => $character->basic_tone,
                        '口癖' => $character->catchphrases,
                        '特徴的な言い回し' => $character->distinctive_speech,
                        '相手による口調の違い' => $character->tone_by_relationship,
                        '短いセリフ例' => $character->short_quote_examples,
                    ] as $label => $value)
                        @if (filled($value))
                            <div>
                                <h3 class="mb-1 font-semibold">{{ $label }}</h3>
                                <div class="whitespace-pre-wrap rounded-2xl bg-gray-50 p-4">{{ $value }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        @foreach ([
            '能力・技・戦闘' => $character->abilities,
            '背景・経歴' => $character->background,
            '作品内での活躍' => $character->story_activities,
        ] as $label => $value)
            @if (filled($value))
                <section class="oshi-card">
                    <h2 class="mb-3 text-2xl font-bold">{{ $label }}</h2>
                    <div class="whitespace-pre-wrap rounded-2xl bg-gray-50 p-4">{{ $value }}</div>
                </section>
            @endif
        @endforeach

        @if (
            filled($character->source_title)
            || $sourceUrls->isNotEmpty()
            || filled($sourceTypeLabel)
            || filled($sourceReliabilityLabel)
            || $character->source_checked_at
        )
            <section class="oshi-card">
                <h2 class="mb-5 text-2xl font-bold">出典</h2>

                @if (filled($character->source_title))
                    <div class="mb-5">
                        <h3 class="mb-1 font-semibold">ページ名または資料名</h3>
                        <div class="whitespace-pre-wrap rounded-2xl bg-gray-50 p-4">{{ $character->source_title }}</div>
                    </div>
                @endif

                @if ($sourceUrls->isNotEmpty())
                    <div class="mb-5">
                        <h3 class="mb-1 font-semibold">URL</h3>
                        <div class="space-y-2 rounded-2xl bg-gray-50 p-4">
                            @foreach ($sourceUrls as $url)
                                @if (\Illuminate\Support\Str::startsWith($url, ['https://', 'http://']))
                                    <a
                                        href="{{ $url }}"
                                        target="_blank"
                                        rel="noopener noreferrer nofollow"
                                        class="block break-all text-blue-700 underline"
                                    >
                                        {{ $url }}
                                    </a>
                                @else
                                    <p class="break-all">{{ $url }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    @if (filled($sourceTypeLabel))
                        <div>
                            <h3 class="mb-1 font-semibold">情報源区分</h3>
                            <div class="rounded-2xl bg-gray-50 p-4">{{ $sourceTypeLabel }}</div>
                        </div>
                    @endif

                    @if (filled($sourceReliabilityLabel))
                        <div>
                            <h3 class="mb-1 font-semibold">信頼度</h3>
                            <div class="rounded-2xl bg-gray-50 p-4">{{ $sourceReliabilityLabel }}</div>
                        </div>
                    @endif

                    @if ($character->source_checked_at)
                        <div>
                            <h3 class="mb-1 font-semibold">確認日</h3>
                            <div class="rounded-2xl bg-gray-50 p-4">
                                {{ $character->source_checked_at->format('Y年n月j日') }}
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif

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
                                    <td class="px-4 py-2">{{ $relation->toCharacter?->name }}</td>
                                    <td class="px-4 py-2">{{ $relation->called_name ?: '未設定' }}</td>
                                    <td class="px-4 py-2">{{ $relation->relationship ?: '未設定' }}</td>
                                    <td class="px-4 py-2">{{ $relation->impression ?: '未設定' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600">公開中の関係性はまだ登録されていません。</p>
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
                                    <td class="px-4 py-2">{{ $relation->fromCharacter?->name }}</td>
                                    <td class="px-4 py-2">{{ $relation->called_name ?: '未設定' }}</td>
                                    <td class="px-4 py-2">{{ $relation->relationship ?: '未設定' }}</td>
                                    <td class="px-4 py-2">{{ $relation->impression ?: '未設定' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600">公開中の関係性はまだ登録されていません。</p>
            @endif
        </section>

        @include('public.partials.helpful-button', [
            'targetType' => 'character',
            'targetId' => $character->id,
            'helpfulCount' => $character->helpful_count ?? 0,
        ])
    </main>

    <div id="page-bottom"></div>

    @include(
        'partials.page-jump-navigation',
        ['position' => 'bottom']
    )
</body>
</html>
