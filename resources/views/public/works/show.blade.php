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
    @include('public.partials.header')

    <div id="page-top"></div>

    @include(
        'partials.page-jump-navigation',
        ['position' => 'top']
    )

    <main class="oshi-container space-y-8">
        <div class="mt-10 mb-6">
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

        @include('public.works._monetization', [
            'monetization' => $monetization,
        ])

        @if ($work->parentWork)
            <section class="oshi-card">
                <p class="mb-2 text-sm font-bold text-[#A0AEC0]">
                    親作品
                </p>

                <a
                    href="{{ route('public.works.show', $work->parentWork) }}"
                    class="inline-flex font-bold text-[#2D3748] underline hover:no-underline"
                >
                    {{ $work->parentWork->title }}
                </a>
            </section>
        @endif

        @if ($work->publishedChildWorks->isNotEmpty())
            <section class="oshi-card">
                <div class="mb-5">
                    <h2 class="text-2xl font-bold">
                        関連作品
                    </h2>

                    <p class="mt-2 text-sm leading-7 text-[#718096]">
                        この作品に紐づく章・シリーズ作品です。
                    </p>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($work->publishedChildWorks as $childWork)
                        <a
                            href="{{ route('public.works.show', $childWork) }}"
                            class="block rounded-2xl border border-[#E2E8F0] bg-white p-5 transition hover:border-[#FED7E2] hover:bg-[#FFF7FA]"
                        >
                            <span class="text-xs font-bold text-[#E879A0]">
                                関連作品
                            </span>

                            <h3 class="mt-2 text-lg font-bold text-[#2D3748]">
                                {{ $childWork->title }}
                            </h3>

                            @if ($childWork->description)
                                <p class="mt-3 line-clamp-3 text-sm leading-7 text-[#718096]">
                                    {{ $childWork->description }}
                                </p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($work->publishedStorySections->isNotEmpty())
            <section class="oshi-card">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold">章・編ごとの物語詳細</h2>
                    <p class="mt-2 text-sm leading-7 text-[#718096]">登録されている章・編、物語の進行、登場キャラクターの時点情報を確認できます。</p>
                </div>
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:items-start">
                    @foreach ($work->publishedStorySections as $section)
                        @php($sectionIsMajorSpoiler = ($section->spoiler_level ?? 'none') === 'major')
                        <details class="min-w-0 rounded-2xl border border-[#E2E8F0] bg-white p-4 lg:p-5">
                            <summary class="cursor-pointer">
                                <div class="inline-flex flex-wrap items-center gap-2 lg:gap-3">
                                    <span class="text-xs font-bold text-[#E879A0]">{{ $section->typeLabel() }}</span>
                                    <span class="text-base font-bold text-[#2D3748] lg:text-[0.95rem]">{{ $section->title }}</span>
                                    @if ($section->short_label)<span class="oshi-chip">{{ $section->short_label }}</span>@endif
                                    @if ($sectionIsMajorSpoiler)<span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">重大なネタバレ</span>@endif
                                </div>
                            </summary>
                            <div class="mt-5 space-y-5">
                                @if ($sectionIsMajorSpoiler)<div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-900">この章・編には重大なネタバレが含まれます。</div>@endif
                                @if ($section->synopsis)<div><h3 class="mb-2 font-bold">章・編の概要</h3><div class="whitespace-pre-wrap rounded-xl bg-[#F7FAFC] p-4 leading-8">{{ $section->synopsis }}</div></div>@endif
                                @if ($section->cumulative_settings)<details class="rounded-xl border border-[#FED7E2] bg-[#FFF7FA] p-4"><summary class="cursor-pointer font-bold">この章までに登場する設定</summary><div class="mt-3 whitespace-pre-wrap leading-8">{{ $section->cumulative_settings }}</div></details>@endif
                                @if ($section->events->isNotEmpty())
                                    <div><h3 class="mb-3 font-bold">物語詳細</h3><div class="space-y-3">
                                        @foreach ($section->events as $event)
                                            @php($eventIsMajorSpoiler = ($event->spoiler_level ?? 'none') === 'major')
                                            <details class="rounded-xl border border-[#E2E8F0] p-4" @if (! $eventIsMajorSpoiler) open @endif>
                                                <summary class="cursor-pointer font-bold">{{ $event->title }} @if ($event->timing)<span class="oshi-chip">{{ $event->timing }}</span>@endif @if ($event->location)<span class="oshi-chip">{{ $event->location }}</span>@endif @if ($eventIsMajorSpoiler)<span class="ml-2 text-xs text-amber-700">重大なネタバレ</span>@endif</summary>
                                                <div class="mt-3 space-y-3">@if ($event->summary)<div class="whitespace-pre-wrap leading-8">{{ $event->summary }}</div>@endif @if ($event->outcome)<div class="rounded-lg bg-[#F7FAFC] p-3"><strong>結果</strong><p class="mt-1 whitespace-pre-wrap leading-8">{{ $event->outcome }}</p></div>@endif @if ($event->notes)<div class="text-sm text-[#718096]"><strong>備考：</strong><span class="whitespace-pre-wrap">{{ $event->notes }}</span></div>@endif</div>
                                            </details>
                                        @endforeach
                                    </div></div>
                                @endif
                                @if ($section->characters->isNotEmpty())
                                    <div><h3 class="mb-3 font-bold">登場キャラクター</h3><div class="grid gap-4 md:grid-cols-2">
                                        @foreach ($section->characters as $character)
                                            <article class="rounded-xl border border-[#E2E8F0] p-4">
                                                <div class="flex flex-wrap items-center gap-2"><a href="{{ route('public.characters.show', $character) }}" class="font-bold underline-offset-4 hover:underline">{{ $character->name }}</a>@if ($character->pivot->first_appearance)<span class="oshi-chip">初登場</span>@endif</div>
                                                @php($snapshot = collect([$character->pivot->age_at_section ? '年齢：'.$character->pivot->age_at_section : null,$character->pivot->school_grade_at_section ? '学年：'.$character->pivot->school_grade_at_section : null,$character->pivot->class_at_section ? 'クラス：'.$character->pivot->class_at_section : null,$character->pivot->affiliation_at_section ? '所属：'.$character->pivot->affiliation_at_section : null,$character->pivot->position_at_section ? '役職：'.$character->pivot->position_at_section : null])->filter())
                                                @if ($snapshot->isNotEmpty())<div class="mt-3 flex flex-wrap gap-2 text-sm text-[#4A5568]">@foreach ($snapshot as $item)<span class="rounded-full bg-[#F7FAFC] px-3 py-1">{{ $item }}</span>@endforeach</div>@endif
                                                @if ($character->pivot->character_state)<p class="mt-3 whitespace-pre-wrap text-sm leading-7 text-[#4A5568]">{{ $character->pivot->character_state }}</p>@endif
                                                @if ($character->pivot->notes)<p class="mt-3 whitespace-pre-wrap text-xs leading-6 text-[#718096]">備考：{{ $character->pivot->notes }}</p>@endif
                                            </article>
                                        @endforeach
                                    </div></div>
                                @endif
                                @if ($section->childSections->isNotEmpty())
                                    <div><h3 class="mb-3 font-bold">この編・部に含まれる章・話</h3><div class="space-y-4">
                                        @foreach ($section->childSections as $childSection)
                                            @php($childIsMajorSpoiler = ($childSection->spoiler_level ?? 'none') === 'major')
                                            <details class="rounded-xl border-l-4 border-[#FED7E2] bg-[#FFFDFE] p-4" @if (! $childIsMajorSpoiler) open @endif>
                                                <summary class="cursor-pointer font-bold">{{ $childSection->title }} @if ($childSection->short_label)<span class="oshi-chip">{{ $childSection->short_label }}</span>@endif @if ($childIsMajorSpoiler)<span class="ml-2 text-xs text-amber-700">重大なネタバレ</span>@endif</summary>
                                                <div class="mt-4 space-y-4">@if ($childSection->synopsis)<div class="whitespace-pre-wrap leading-8">{{ $childSection->synopsis }}</div>@endif @if ($childSection->cumulative_settings)<details class="rounded-lg bg-[#FFF7FA] p-3"><summary class="cursor-pointer font-bold">この章までに登場する設定</summary><div class="mt-2 whitespace-pre-wrap leading-8">{{ $childSection->cumulative_settings }}</div></details>@endif @if ($childSection->events->isNotEmpty())<div class="space-y-3">@foreach ($childSection->events as $event)<details class="rounded-lg border border-[#E2E8F0] p-3"><summary class="cursor-pointer font-bold">{{ $event->title }}</summary>@if ($event->summary)<div class="mt-2 whitespace-pre-wrap leading-8">{{ $event->summary }}</div>@endif</details>@endforeach</div>@endif @if ($childSection->characters->isNotEmpty())<div class="flex flex-wrap gap-2">@foreach ($childSection->characters as $character)<span class="oshi-badge">{{ $character->name }}</span>@endforeach</div>@endif</div>
                                            </details>
                                        @endforeach
                                    </div></div>
                                @endif
                                @if ($section->notes)<div class="rounded-xl bg-[#F7FAFC] p-4 text-sm text-[#718096]"><strong>備考</strong><div class="mt-2 whitespace-pre-wrap leading-7">{{ $section->notes }}</div></div>@endif
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="oshi-card">
            <h2 class="mb-4 text-2xl font-bold">
                キャラクター
            </h2>

            @if ($work->characters->count())
                <div class="oshi-card-grid">
                    @foreach ($work->characters as $character)
                        <article class="oshi-card">
                            <p class="mb-1 text-sm text-gray-500">
                                {{ $character->occupation_position ?: $character->affiliation ?: '所属・役職未設定' }}
                            </p>

                            <h3 class="mb-2 text-xl font-bold">
                                {{ $character->name }}
                            </h3>

                            @if ($character->name_kana)
                                <p class="mb-2 text-sm text-gray-500">
                                    {{ $character->name_kana }}
                                </p>
                            @endif

                            <div class="mb-3 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-700">
                                @if ($character->age)
                                    <span>年齢：{{ $character->age }}</span>
                                @endif

                                @if ($character->gender)
                                    <span>性別：{{ $character->gender }}</span>
                                @endif

                                @if ($character->species)
                                    <span>種族：{{ $character->species }}</span>
                                @endif

                                @if ($character->school_grade_class)
                                    <span>学校・学年・クラス：{{ $character->school_grade_class }}</span>
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

                            <p class="mb-4 line-clamp-3 whitespace-pre-wrap text-gray-700">
                                {{ $character->personality
                                    ?: $character->appearance
                                    ?: $character->abilities
                                    ?: $character->background
                                    ?: $character->story_activities
                                    ?: '説明はまだ登録されていません。' }}
                            </p>

                            @if (($character->spoiler_level ?? 'none') !== 'none')
                                <p class="mb-3 rounded-xl bg-amber-50 px-3 py-2 text-xs font-bold text-amber-800">
                                    ネタバレ：
                                    {{ \App\Models\Character::SPOILER_LEVELS[$character->spoiler_level] ?? 'あり' }}
                                </p>
                            @endif

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

    <div id="page-bottom"></div>

    @include(
        'partials.page-jump-navigation',
        ['position' => 'bottom']
    )
</body>
</html>
