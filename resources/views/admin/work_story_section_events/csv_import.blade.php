<x-app-layout>
    <div class="oshi-admin-layout">
        @include('admin.partials.navigation')

        <main class="oshi-admin-main">
            @include('admin.partials.flash')

            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="oshi-muted">
                        {{ $work->title }}
                    </p>
                    <h1 class="oshi-admin-title">
                        {{ $section->title }}の物語詳細CSV
                    </h1>
                    <p class="mt-2 text-sm leading-7 text-[#718096]">
                        この章の物語詳細だけをCSVから
                        追加・更新します。
                        現在{{ $section->events_count }}件 /
                        最大{{ $eventLimit }}件です。
                    </p>
                </div>

                <a
                    href="{{ route(
                        'admin.works.story-sections.show',
                        [$work, $section]
                    ) }}"
                    class="oshi-btn oshi-btn-sub"
                >
                    章の詳細へ戻る
                </a>
            </div>

            @if (session('csv_errors'))
                <section class="mb-6 rounded-3xl border border-red-200 bg-red-50 p-5">
                    <h2 class="font-bold text-red-800">
                        取り込めなかった行があります
                    </h2>

                    <p class="mt-1 text-sm text-red-700">
                        正常な行は登録済みです。
                        下記の行を修正して再度取り込んでください。
                    </p>

                    <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-red-800">
                        @foreach (
                            session('csv_errors')
                            as $error
                        )
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($errors->any())
                <section class="mb-6 rounded-3xl border border-red-200 bg-red-50 p-5">
                    <ul class="list-disc space-y-1 pl-5 text-sm text-red-800">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <div class="grid gap-6 xl:grid-cols-[minmax(0,1.25fr)_minmax(320px,0.75fr)]">
                <section class="oshi-card">
                    <div class="mb-5">
                        <p class="text-sm font-bold text-[#A0AEC0]">
                            CSV IMPORT
                        </p>
                        <h2 class="mt-1 text-xl font-bold text-[#2D3748]">
                            CSVファイルを取り込む
                        </h2>
                    </div>

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                        action="{{ route(
                            'admin.works.story-sections.events.csv.store',
                            [$work, $section]
                        ) }}"
                        class="space-y-5"
                    >
                        @csrf

                        <label
                            for="csv_file"
                            class="block cursor-pointer rounded-3xl border-2 border-dashed border-[#CBD5E0] bg-[#F7FAFC] p-8 text-center transition hover:border-[#FED7E2] hover:bg-[#FFF7FA]"
                        >
                            <span class="block text-lg font-bold text-[#2D3748]">
                                CSVファイルを選択
                            </span>
                            <span class="mt-2 block text-sm leading-6 text-[#718096]">
                                .csv または .txt /
                                最大10MB
                            </span>
                            <input
                                id="csv_file"
                                type="file"
                                name="csv_file"
                                accept=".csv,.txt,text/csv"
                                class="mt-5 block w-full rounded-xl border border-[#CBD5E0] bg-white p-3"
                                required
                            >
                        </label>

                        <div class="rounded-2xl border border-[#FED7E2] bg-[#FFF7FA] p-4 text-sm leading-7 text-[#4A5568]">
                            <p class="font-bold text-[#2D3748]">
                                登録方法
                            </p>
                            <p class="mt-1">
                                story_event_idが空欄の行は
                                新規登録されます。
                                現在のCSVを出力してIDを残したまま
                                編集すると、該当行を更新できます。
                            </p>
                        </div>

                        <button
                            type="submit"
                            class="oshi-btn w-full justify-center py-3 text-base"
                        >
                            CSVを取り込む
                        </button>
                    </form>
                </section>

                <div class="space-y-6">
                    <section class="oshi-card">
                        <h2 class="text-lg font-bold text-[#2D3748]">
                            CSVの準備
                        </h2>
                        <p class="mt-2 text-sm leading-7 text-[#718096]">
                            新規登録用のサンプル、または
                            現在登録されている内容を
                            ダウンロードして編集できます。
                        </p>

                        <div class="mt-5 grid gap-3">
                            <a
                                href="{{ route(
                                    'admin.story-section-events.csv.sample'
                                ) }}"
                                class="oshi-btn oshi-btn-sub justify-center"
                            >
                                サンプルCSVをダウンロード
                            </a>

                            <a
                                href="{{ route(
                                    'admin.works.story-sections.events.csv.export',
                                    [$work, $section]
                                ) }}"
                                class="oshi-btn oshi-btn-sub justify-center"
                            >
                                現在の物語詳細CSVを出力
                            </a>
                        </div>
                    </section>

                    <section class="oshi-card">
                        <h2 class="text-lg font-bold text-[#2D3748]">
                            使用できる列
                        </h2>

                        <dl class="mt-4 space-y-3 text-sm">
                            @foreach ([
                                ['story_event_id', '更新時のみ使用。新規登録は空欄'],
                                ['event_number', '作中の出来事番号'],
                                ['title', '必須。出来事の名称'],
                                ['timing', '時期・タイミング'],
                                ['summary', '出来事の詳しい内容'],
                                ['location', '場所'],
                                ['outcome', '出来事の結果'],
                                ['spoiler_level', 'none / minor / major'],
                                ['notes', '補足事項'],
                                ['sort_order', '表示順。整数'],
                            ] as [$column, $description])
                                <div class="rounded-xl bg-[#F7FAFC] p-3">
                                    <dt class="font-mono font-bold text-[#2D3748]">
                                        {{ $column }}
                                    </dt>
                                    <dd class="mt-1 text-[#718096]">
                                        {{ $description }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                </div>
            </div>
        </main>
    </div>
</x-app-layout>
