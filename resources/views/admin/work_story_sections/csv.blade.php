<x-app-layout>
    <div class="oshi-admin-layout">
        @include('admin.partials.navigation')

        <main class="oshi-admin-main">
            @include('admin.partials.flash')

            <div class="mb-6">
                <p class="oshi-muted">{{ $work->title }}</p>
                <h1 class="oshi-admin-title">
                    章・編CSV取り込み・出力
                </h1>
            </div>

            @if (session('csv_errors'))
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
                    <p class="font-bold">
                        取り込めなかった行があります。
                    </p>
                    <ul class="mt-2 list-disc pl-5">
                        @foreach (session('csv_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-5 lg:grid-cols-3">
                @foreach ([
                    'sections' => [
                        '章・編CSV',
                        '章名、概要、累積設定、状態など',
                    ],
                    'events' => [
                        '物語詳細CSV',
                        '各章の出来事、場所、結果など',
                    ],
                    'characters' => [
                        '登場キャラクターCSV',
                        '章時点の年齢・学年・所属など',
                    ],
                ] as $type => [$label, $description])
                    <section class="oshi-card">
                        <h2 class="text-xl font-bold">
                            {{ $label }}
                        </h2>
                        <p class="mt-2 text-sm text-gray-600">
                            {{ $description }}
                        </p>

                        <div class="mt-5 flex flex-wrap gap-2">
                            <a
                                class="oshi-btn oshi-btn-sub"
                                href="{{ route(
                                    'admin.works.story-sections.csv.sample',
                                    $type
                                ) }}"
                            >
                                サンプルCSV
                            </a>

                            <a
                                class="oshi-btn oshi-btn-sub"
                                href="{{ route(
                                    'admin.works.story-sections.csv.export',
                                    [$work, $type]
                                ) }}"
                            >
                                エクスポート
                            </a>
                        </div>

                        <form
                            method="POST"
                            enctype="multipart/form-data"
                            action="{{ route(
                                'admin.works.story-sections.csv.import',
                                $work
                            ) }}"
                            class="mt-5 space-y-4"
                        >
                            @csrf
                            <input
                                type="hidden"
                                name="type"
                                value="{{ $type }}"
                            >

                            <div>
                                <label>CSVファイル</label>
                                <input
                                    type="file"
                                    name="csv_file"
                                    accept=".csv,.txt"
                                    required
                                >
                            </div>

                            @if ($type === 'sections')
                                <div>
                                    <label>状態が空欄の場合</label>
                                    <select name="default_status">
                                        <option value="draft">
                                            下書き
                                        </option>
                                        <option value="published">
                                            公開
                                        </option>
                                        <option value="private">
                                            非公開
                                        </option>
                                    </select>
                                </div>
                            @endif

                            <button class="oshi-btn" type="submit">
                                {{ $label }}を取り込む
                            </button>
                        </form>
                    </section>
                @endforeach
            </div>

            <div class="mt-6">
                <a
                    class="oshi-btn oshi-btn-sub"
                    href="{{ route(
                        'admin.works.story-sections.index',
                        $work
                    ) }}"
                >
                    章・編一覧へ戻る
                </a>
            </div>
        </main>
    </div>
</x-app-layout>
