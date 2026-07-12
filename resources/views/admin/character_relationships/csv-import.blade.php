<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">関係性CSV取り込み</h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.navigation')

        @if (session('success'))
            <div class="mb-4 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-bold text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('csv_errors') && count(session('csv_errors')))
            <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">
                <p class="mb-2 font-bold">取り込みできなかった行があります。</p>
                <ul class="list-disc space-y-1 pl-5">
                    @foreach (session('csv_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="oshi-card">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold">関係性CSV取り込み</h1>
                    <p class="oshi-muted">
                        CSVをアップロードして、複数のキャラクター関係性をまとめて登録できます。
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.character-relationships.csv-import.sample') }}" class="oshi-btn">
                        CSVサンプルをダウンロード
                    </a>
                    <a href="{{ route('admin.character-relationships.index') }}" class="oshi-btn oshi-btn-sub">
                        関係性一覧へ戻る
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.character-relationships.csv-import.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-5">
                    <label for="work_id" class="mb-1 block font-medium">
                        作品
                    </label>
                    <select id="work_id" name="work_id" class="w-full rounded border border-gray-300 p-3">
                        <option value="">CSV内の work_id を使う</option>
                        @foreach ($works as $work)
                            <option value="{{ $work->id }}" @selected(old('work_id') == $work->id)>
                                {{ $work->title }}（ID: {{ $work->id }}）
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-gray-600">
                        ここで作品を選ぶと、CSV内の work_id が空でもその作品に登録されます。
                    </p>
                    @error('work_id')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-5">
                    <label for="default_status" class="mb-1 block font-medium">
                        CSV内のstatusが空の場合の状態
                    </label>
                    <select id="default_status" name="default_status" class="w-full rounded border border-gray-300 p-3">
                        <option value="draft" @selected(old('default_status', 'draft') === 'draft')>下書き</option>
                        <option value="published" @selected(old('default_status') === 'published')>公開</option>
                        <option value="private" @selected(old('default_status') === 'private')>非公開</option>
                    </select>
                    @error('default_status')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-5">
                    <label for="csv_file" class="mb-1 block font-medium">
                        CSVファイル
                    </label>
                    <input
                        id="csv_file"
                        type="file"
                        name="csv_file"
                        accept=".csv,text/csv"
                        class="w-full rounded border border-gray-300 p-3"
                    >
                    @error('csv_file')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="oshi-btn">
                    CSVから一括登録する
                </button>
            </form>
        </div>

        <div class="oshi-card mt-6">
            <h2 class="mb-3 text-xl font-bold">CSVの形式</h2>

            <p class="mb-4 text-sm text-gray-600">
                1行目はヘッダーにしてください。文字コードはUTF-8またはShift_JISに対応しています。
            </p>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr>
                            <th class="border-b p-2">項目名</th>
                            <th class="border-b p-2">必須</th>
                            <th class="border-b p-2">説明</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td class="border-b p-2">work_id</td><td class="border-b p-2">条件付き</td><td class="border-b p-2">作品ID。画面で作品を選択しない場合は必須です。</td></tr>
                        <tr><td class="border-b p-2">from_character_id</td><td class="border-b p-2">必須</td><td class="border-b p-2">関係元キャラクターID。同じ作品内のキャラクターを指定してください。</td></tr>
                        <tr><td class="border-b p-2">to_character_id</td><td class="border-b p-2">必須</td><td class="border-b p-2">関係先キャラクターID。同じ作品内のキャラクターを指定してください。</td></tr>
                        <tr><td class="border-b p-2">called_name</td><td class="border-b p-2">任意</td><td class="border-b p-2">関係元キャラクターから相手への呼び方。</td></tr>
                        <tr><td class="border-b p-2">relationship</td><td class="border-b p-2">任意</td><td class="border-b p-2">関係性。例：幼なじみ、上司、ライバルなど。</td></tr>
                        <tr><td class="border-b p-2">impression</td><td class="border-b p-2">任意</td><td class="border-b p-2">印象・気持ち。</td></tr>
                        <tr><td class="border-b p-2">notes</td><td class="border-b p-2">任意</td><td class="border-b p-2">補足メモ。</td></tr>
                        <tr><td class="border-b p-2">status</td><td class="border-b p-2">任意</td><td class="border-b p-2">draft / published / private。空の場合は画面で選択した状態になります。</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
