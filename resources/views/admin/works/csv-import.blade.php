<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">CSV取り込み</h2>
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
                    <h1 class="text-2xl font-bold">CSV取り込み</h1>
                    <p class="oshi-muted">CSVをアップロードして作品をまとめて登録します。</p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.works.csv-import.sample') }}" class="oshi-btn">CSVサンプルをダウンロード</a>
                    <a href="{{ route('admin.works.index') }}" class="oshi-btn oshi-btn-sub">作品一覧へ戻る</a>
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
                </div>

                <div class="mb-5">
                    <label for="csv_file" class="mb-1 block font-medium">CSVファイル</label>
                    <input id="csv_file" type="file" name="csv_file" accept=".csv,text/csv" class="w-full rounded border border-gray-300 p-3">
                </div>

                <button type="submit" class="oshi-btn">CSVから一括登録する</button>
            </form>
        </div>

        <div class="oshi-card mt-6">
            <h2 class="mb-3 text-xl font-bold">CSVの形式</h2>
            <p>列名：title,title_kana,genre,original_media,official_url,guideline_url,description,status</p>
        </div>
    </div>
</x-app-layout>
