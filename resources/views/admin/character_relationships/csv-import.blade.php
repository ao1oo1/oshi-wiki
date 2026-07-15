<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">関係性CSV取り込み</h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="oshi-card">
            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold">関係性CSV取り込み</h1>
                    <p class="oshi-muted">
                        作品ID・キャラクターIDを使って関係性を新規登録または更新します。
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.character-relationships.csv-import.sample') }}" class="oshi-btn oshi-btn-sub">
                        サンプルCSV
                    </a>
                    <a href="{{ route('admin.character-relationships.index') }}" class="oshi-btn oshi-btn-sub">
                        一覧へ戻る
                    </a>
                </div>
            </div>

            <div class="mb-6 rounded-3xl bg-[#FFF5F7] p-5 text-sm leading-7 text-[#4A5568]">
                <p class="font-bold text-[#2D3748]">CSV仕様</p>
                <p>relationship_idが空欄の場合は新規登録、入力済みの場合は更新します。</p>
                <p>work_id、from_character_id、to_character_idは必須です。</p>
                <p>送信元・送信先キャラクターは、指定した作品に主作品または追加作品として紐付いている必要があります。</p>
            </div>

            @if (session('csv_errors'))
                <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 p-5">
                    <p class="mb-2 font-bold text-red-700">取り込めなかった行</p>
                    <ul class="list-disc space-y-1 pl-5 text-sm text-red-700">
                        @foreach (session('csv_errors') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.character-relationships.csv-import.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div>
                    <label class="oshi-label" for="csv_file">CSVファイル</label>
                    <input id="csv_file" name="csv_file" type="file" accept=".csv,text/csv" required class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3">
                    @error('csv_file')
                        <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="oshi-label" for="default_status">状態が空欄の場合</label>
                    <select id="default_status" name="default_status" class="w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3">
                        <option value="draft">下書き</option>
                        <option value="published">公開</option>
                        <option value="private">非公開</option>
                    </select>
                </div>

                <button type="submit" class="oshi-btn">CSVを取り込む</button>
            </form>
        </div>
    </div>
</x-app-layout>
