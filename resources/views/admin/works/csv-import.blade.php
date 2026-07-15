<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">作品CSV取り込み</h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        @if (session('csv_errors') && count(session('csv_errors')))
            <div class="mb-4 rounded bg-red-50 px-4 py-3 text-red-800">
                <p class="font-bold">一部の行を登録できませんでした。</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach (session('csv_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="oshi-card">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold">作品CSV取り込み</h1>
                    <p class="oshi-muted">
                        エクスポートしたCSVを編集し、そのまま再取り込みできます。
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.works.csv-import.sample') }}" class="oshi-btn">
                        CSVサンプルをダウンロード
                    </a>
                    <a href="{{ route('admin.works.index') }}" class="oshi-btn oshi-btn-sub">
                        作品一覧へ戻る
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.works.csv-import.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-5">
                    <label for="default_status" class="mb-1 block font-medium">初期状態</label>
                    <select id="default_status" name="default_status" class="w-full">
                        <option value="draft" @selected(old('default_status', 'draft') === 'draft')>下書き</option>
                        <option value="published" @selected(old('default_status') === 'published')>公開</option>
                        <option value="private" @selected(old('default_status') === 'private')>非公開</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-600">
                        CSV内のstatusが空の場合に使用されます。
                    </p>
                </div>

                <div class="mb-5">
                    <label for="csv_file" class="mb-1 block font-medium">CSVファイル</label>
                    <input
                        id="csv_file"
                        type="file"
                        name="csv_file"
                        accept=".csv,text/csv"
                        class="w-full rounded border border-gray-300 p-3"
                        required
                    >
                    @error('csv_file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="oshi-btn">CSVから一括登録する</button>
            </form>
        </div>

        <div class="oshi-card mt-6">
            <h2 class="mb-3 text-xl font-bold">CSVの仕様</h2>

            <div class="space-y-4 text-sm">
                <p>
                    <strong>新規登録：</strong>
                    work_idを空欄にします。
                </p>
                <p>
                    <strong>既存作品の更新：</strong>
                    エクスポートされたwork_idを残したまま再取り込みします。
                </p>
                <p>
                    <strong>通常の作品項目：</strong>
                    {{ implode(', ', $workColumns ?? []) }}
                </p>
                <p>
                    <strong>タグ：</strong>
                    tag_idsまたはtag_namesを指定できます。両方が空欄の場合はタグなしとして同期します。
                </p>
            </div>
        </div>

        <div class="oshi-card mt-6">
            <h2 class="mb-3 text-xl font-bold">年表・用語使用例の形式</h2>

            <p class="mb-4 text-sm text-gray-700">
                1作品をCSVの1行で管理するため、複数件のデータはJSON配列として保存します。
                どちらも最大50件です。通常はエクスポートした値を編集してご利用ください。
            </p>

            <div class="space-y-5">
                <div>
                    <h3 class="font-bold">canon_events_json</h3>
                    <p class="text-sm text-gray-600">
                        原作の重要イベント年表。使用できるキー：
                        {{ implode(', ', $canonEventFields ?? []) }}
                    </p>
                </div>

                <div>
                    <h3 class="font-bold">term_usages_json</h3>
                    <p class="text-sm text-gray-600">
                        用語の使用例。使用できるキー：
                        {{ implode(', ', $termUsageFields ?? []) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
