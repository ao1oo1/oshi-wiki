<!DOCTYPE html>
<html lang="ja">
<head>
    @include('partials.favicon')
    @include('partials.google-analytics')
    @include('partials.seo-meta', [
        'pageSeoTitle' => $entitySeo['title'] ?? null,
        'pageSeoDescription' => $entitySeo['description'] ?? null,
        'pageSeoKeywords' => $entitySeo['keywords'] ?? null,
    ])


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $entitySeo['title'] ?? $work->title }} | Oshi-Wiki</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Google AdSense site verification --}}
    <script
        async
        src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-3916030283806562"
        crossorigin="anonymous"
    ></script>
    @if (! empty($entitySeo['jsonLd']))
        <script type="application/ld+json">
            {!! json_encode(
                $entitySeo['jsonLd'],
                JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                    | JSON_HEX_TAG
                    | JSON_HEX_AMP
                    | JSON_HEX_APOS
                    | JSON_HEX_QUOT
            ) !!}
        </script>
    @endif
</head>
<body>


    @include('public.partials.header')

    @include(
        'public.partials.impression-ads',
        ['position' => 'page_top']
    )

    <div id="page-top"></div>

    @include(
        'partials.page-jump-navigation',
        ['position' => 'top']
    )

    <main class="oshi-container space-y-8">
        <nav
            class="public-work-shortcuts mt-10 mb-6"
            aria-label="作品詳細ページ内ショートカット"
        >
            <a
                href="{{ route('public.works.index') }}"
                class="public-work-shortcuts__back"
            >
                作品一覧へ戻る
            </a>

            <div class="public-work-shortcuts__links">
                <a href="#work-characters">キャラクター</a>

                @if ($work->publishedStorySections->isNotEmpty())
                    <a href="#work-story-sections">
                        章・編ごとの物語詳細
                    </a>
                @endif

                <a href="#work-character-relationships">
                    キャラクター関係性
                </a>
            </div>
        </nav>

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


        </section>

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

        <section
            id="work-characters"
            class="oshi-card public-work-anchor-section"
        >
            <h2 class="mb-4 text-2xl font-bold">
                キャラクター
            </h2>

            <p class="mb-5 rounded-2xl border border-[#FED7E2] bg-[#FFF7FA] px-4 py-3 text-sm leading-7 text-[#4A5568]">
                情報に誤りがある場合は
                <a
                    href="{{ route('public.contact.create', ['category' => 'correction']) }}"
                    class="font-bold text-[#D95F82] underline decoration-2 underline-offset-4 hover:opacity-80"
                >
                    お問い合わせフォーム
                </a>
                よりご連絡ください。
            </p>

            @if ($work->characters->count())
                @php
                    $characterAffiliations = $work->characters
                        ->pluck('affiliation')
                        ->filter(fn ($value) => filled($value))
                        ->unique()
                        ->sort(SORT_NATURAL)
                        ->values();

                    $characterSchools = $work->characters
                        ->pluck('school_grade_class')
                        ->filter(fn ($value) => filled($value))
                        ->unique()
                        ->sort(SORT_NATURAL)
                        ->values();

                    $characterOccupations = $work->characters
                        ->pluck('occupation_position')
                        ->filter(fn ($value) => filled($value))
                        ->unique()
                        ->sort(SORT_NATURAL)
                        ->values();
                @endphp

                <div
                    class="public-work-character-filters"
                    data-character-filter-root
                >
                    <div class="public-work-character-filters__grid">
                        <label>
                            <span>所属</span>
                            <select data-character-filter="affiliation">
                                <option value="">すべて</option>
                                @foreach ($characterAffiliations as $value)
                                    <option value="{{ $value }}">
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>学校・学年・クラス</span>
                            <select data-character-filter="school">
                                <option value="">すべて</option>
                                @foreach ($characterSchools as $value)
                                    <option value="{{ $value }}">
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>職業・役職</span>
                            <select data-character-filter="occupation">
                                <option value="">すべて</option>
                                @foreach ($characterOccupations as $value)
                                    <option value="{{ $value }}">
                                        {{ $value }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="public-work-character-filters__status">
                        <p aria-live="polite">
                            <span data-character-filter-count>
                                {{ $work->characters->count() }}
                            </span>
                            件表示
                        </p>

                        <button
                            type="button"
                            data-character-filter-reset
                        >
                            絞り込みを解除
                        </button>
                    </div>
                </div>

                <div
                    class="oshi-card-grid"
                    data-character-filter-list
                >
                    @foreach ($work->characters as $character)
                        <article
                            class="oshi-card public-work-character-card"
                            data-character-card
                            data-affiliation="{{ $character->affiliation }}"
                            data-school="{{ $character->school_grade_class }}"
                            data-occupation="{{ $character->occupation_position }}"
                        >
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

                            <div class="public-work-character-card__action">
                                <a
                                    href="{{ route(
                                        'public.characters.show',
                                        $character
                                    ) }}"
                                    class="public-work-character-card__detail"
                                >
                                    キャラクター詳細
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="text-gray-600">
                    公開中のキャラクターはまだ登録されていません。
                </p>
            @endif
        </section>

        @if ($work->publishedStorySections->isNotEmpty())
            <section
                id="work-story-sections"
                class="oshi-card public-work-anchor-section"
            >
                <div class="mb-6">
                    <h2 class="text-2xl font-bold">章・編ごとの物語詳細</h2>
                    <p class="mt-2 text-sm leading-7 text-[#718096]">登録されている章・編、物語の進行、登場キャラクターの時点情報を確認できます。</p>
                </div>
                <div class="grid grid-cols-1 gap-4">
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
                                    <div>
                                        <h3 class="mb-3 font-bold">
                                            物語詳細
                                        </h3>

                                        <div class="space-y-3">
                                            @foreach ($section->events as $event)
                                                @php($eventIsMajorSpoiler = ($event->spoiler_level ?? 'none') === 'major')

                                                <article class="rounded-xl border border-[#E2E8F0] p-4">
                                                    <div class="flex flex-wrap items-center gap-2">
                                                        <h4 class="font-bold">
                                                            {{ $event->title }}
                                                        </h4>

                                                        @if ($event->timing)
                                                            <span class="oshi-chip">
                                                                {{ $event->timing }}
                                                            </span>
                                                        @endif

                                                        @if ($event->location)
                                                            <span class="oshi-chip">
                                                                {{ $event->location }}
                                                            </span>
                                                        @endif

                                                        @if ($eventIsMajorSpoiler)
                                                            <span class="text-xs font-bold text-amber-700">
                                                                重大なネタバレ
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if ($event->summary)
                                                        <div class="mt-3 whitespace-pre-line leading-8">{{ trim($event->summary) }}</div>
                                                    @endif

                                                    @if ($event->outcome)
                                                        <div class="mt-3 rounded-xl bg-[#F7FAFC] p-3">
                                                            <h5 class="font-bold">
                                                                結果
                                                            </h5>
                                                            <div class="mt-1 whitespace-pre-line leading-7">{{ trim($event->outcome) }}</div>
                                                        </div>
                                                    @endif
                                                </article>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                                @if ($section->characters->isNotEmpty())
                                    <div><h3 class="mb-3 font-bold">登場キャラクター</h3><div class="grid gap-4 md:grid-cols-2">
                                        @foreach ($section->characters as $character)
                                            <article class="rounded-xl border border-[#E2E8F0] p-4">
                                                <div class="flex flex-wrap items-center gap-2"><a href="{{ route('public.characters.show', $character) }}" class="font-bold underline-offset-4 hover:underline">{{ $character->name }}</a>@if ($character->pivot->first_appearance)<span class="oshi-chip">初登場</span>@endif</div>
                                                @php($snapshot = collect([$character->pivot->age_at_section ? '年齢：'.$character->pivot->age_at_section : null,$character->pivot->school_grade_at_section ? '学年：'.$character->pivot->school_grade_at_section : null,$character->pivot->class_at_section ? 'クラス：'.$character->pivot->class_at_section : null,$character->pivot->affiliation_at_section ? '所属：'.$character->pivot->affiliation_at_section : null,$character->pivot->position_at_section ? '役職：'.$character->pivot->position_at_section : null])->filter())
                                                @if ($snapshot->isNotEmpty())<div class="mt-3 flex flex-wrap gap-2 text-sm text-[#4A5568]">@foreach ($snapshot as $item)<span class="rounded-full bg-[#F7FAFC] px-3 py-1">{{ $item }}</span>@endforeach</div>@endif
                                                @if ($character->pivot->character_state)<p class="mt-3 whitespace-pre-wrap text-sm leading-7 text-[#4A5568]">{{ $character->pivot->character_state }}</p>@endif
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
                                                <div class="mt-4 space-y-4">
                                                    @if ($childSection->synopsis)
                                                        <div class="whitespace-pre-wrap leading-8">
                                                            {{ $childSection->synopsis }}
                                                        </div>
                                                    @endif

                                                    @if ($childSection->cumulative_settings)
                                                        <div class="rounded-lg bg-[#FFF7FA] p-3">
                                                            <h4 class="font-bold">
                                                                この章までに登場する設定
                                                            </h4>
                                                            <div class="mt-2 whitespace-pre-wrap leading-8">
                                                                {{ $childSection->cumulative_settings }}
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if ($childSection->events->isNotEmpty())
                                                        <div class="space-y-3">
                                                            @foreach ($childSection->events as $event)
                                                                <article class="rounded-lg border border-[#E2E8F0] p-3">
                                                                    <h4 class="font-bold">
                                                                        {{ $event->title }}
                                                                    </h4>

                                                                    @if ($event->summary)
                                                                        <div class="mt-2 whitespace-pre-line leading-8">{{ trim($event->summary) }}</div>
                                                                    @endif

                                                                    @if ($event->outcome)
                                                                        <div class="mt-3 rounded-lg bg-[#F7FAFC] p-3">
                                                                            <h5 class="font-bold">
                                                                                結果
                                                                            </h5>
                                                                            <div class="mt-1 whitespace-pre-line leading-7">{{ trim($event->outcome) }}</div>
                                                                        </div>
                                                                    @endif
                                                                </article>
                                                            @endforeach
                                                        </div>
                                                    @endif

                                                    @if ($childSection->characters->isNotEmpty())
                                                        <div class="flex flex-wrap gap-2">
                                                            @foreach ($childSection->characters as $character)
                                                                <span class="oshi-badge">
                                                                    {{ $character->name }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </details>
                                        @endforeach
                                    </div></div>
                                @endif
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif

        <section
            id="work-character-relationships"
            class="oshi-card public-work-anchor-section"
        >
            <h2 class="mb-4 text-2xl font-bold">
                キャラクター関係性
            </h2>

            <?php
                $publicRelationshipRows =
                    $work->characterRelationships
                        ->sortBy(function ($relationship) {
                            $fromId = (int) optional(
                                $relationship->fromCharacter
                            )->id;

                            $toId = (int) optional(
                                $relationship->toCharacter
                            )->id;

                            return [
                                min($fromId, $toId),
                                max($fromId, $toId),
                                (int) $relationship->id,
                            ];
                        })
                        ->values();

                $publicRelationshipCharacters =
                    $publicRelationshipRows
                        ->flatMap(function ($relationship) {
                            return [
                                $relationship->fromCharacter,
                                $relationship->toCharacter,
                            ];
                        })
                        ->filter()
                        ->unique('id')
                        ->sortBy('id')
                        ->values();
            ?>

            <?php if ($publicRelationshipRows->isNotEmpty()): ?>
                <div
                    class="mb-5 rounded-2xl border border-[#E2E8F0] bg-[#F7FAFC] p-4"
                    data-public-work-relationship-filter
                >
                    <label
                        for="public-work-relationship-character-filter"
                        class="text-sm font-bold text-[#2D3748]"
                    >
                        キャラクターで絞り込む
                    </label>

                    <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-center">
                        <select
                            id="public-work-relationship-character-filter"
                            class="w-full rounded-xl border border-[#CBD5E0] bg-white px-4 py-3 text-sm font-bold text-[#2D3748] sm:max-w-md"
                            data-public-work-relationship-character-select
                        >
                            <option value="">すべてのキャラクター</option>

                            <?php foreach ($publicRelationshipCharacters as $filterCharacter): ?>
                                <option value="{{ $filterCharacter->id }}">
                                    {{ $filterCharacter->name }}
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button
                            type="button"
                            class="hidden rounded-xl border border-[#CBD5E0] bg-white px-4 py-3 text-sm font-bold text-[#4A5568] hover:bg-[#EDF2F7]"
                            data-public-work-relationship-filter-clear
                        >
                            絞り込みを解除
                        </button>
                    </div>

                    <p
                        class="mt-3 text-xs font-bold text-[#718096]"
                        data-public-work-relationship-filter-count
                        aria-live="polite"
                    >
                        {{ $publicRelationshipRows->count() }}件表示中
                    </p>
                </div>

                <div class="oshi-table-wrap public-work-relationship-table-wrap">
                    <table class="oshi-table public-work-relationship-table">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="px-4 py-2 text-left">キャラクター</th>
                                <th class="px-4 py-2 text-left">相手</th>
                                <th class="px-4 py-2 text-left">呼ばれ方</th>
                                <th class="px-4 py-2 text-left">関係性</th>
                                <th class="public-work-relationship-table__impression px-4 py-2 text-left">
                                    印象・気持ち等
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($publicRelationshipRows as $relation): ?>
                                <tr
                                    class="border-b"
                                    data-public-work-relationship-row
                                    data-character-ids="{{ collect([
                                        $relation->fromCharacter?->id,
                                        $relation->toCharacter?->id,
                                    ])->filter()->implode(',') }}"
                                >
                                    <td class="px-4 py-2" data-label="キャラクター">
                                        {{ $relation->fromCharacter?->name ?: '未設定' }}
                                    </td>
                                    <td class="px-4 py-2" data-label="相手">
                                        {{ $relation->toCharacter?->name ?: '未設定' }}
                                    </td>
                                    <td class="px-4 py-2" data-label="呼ばれ方">
                                        {{ $relation->called_name ?: '未設定' }}
                                    </td>
                                    <td class="px-4 py-2" data-label="関係性">
                                        {{ $relation->relationship ?: '未設定' }}
                                    </td>
                                    <td class="public-work-relationship-table__impression px-4 py-2" data-label="印象・気持ち等">
                                        {{ $relation->impression ?: '未設定' }}
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-600">
                    公開中の関係性はまだ登録されていません。
                </p>
            <?php endif; ?>
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
    @include(
        'public.partials.impression-ads',
        ['position' => 'page_middle']
    )

    @include(
        'public.partials.impression-ads',
        ['position' => 'page_bottom']
    )
@include('public.partials.legal-footer')

    @include('works.partials.character-fuzzy-search')

</body>
</html>
