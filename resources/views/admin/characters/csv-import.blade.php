<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            CSV取り込み
        </h2>
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
                    <h1 class="text-2xl font-bold">
                        CSV取り込み
                    </h1>
                    <p class="oshi-muted">
                        CSVをアップロードして、複数のキャラクターをまとめて登録できます。
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.characters.csv-import.sample') }}" class="oshi-btn">
                        CSVサンプルをダウンロード
                    </a>

                    <a href="{{ route('admin.characters.index') }}" class="oshi-btn oshi-btn-sub">
                        キャラクター一覧へ戻る
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.characters.csv-import.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-5">
                    <label for="work_id" class="mb-1 block font-medium">
                        登録先の作品
                    </label>
                    <select id="work_id" name="work_id" class="w-full">
                        <option value="">CSV内の work_id を使う</option>
                        @foreach ($works as $work)
                            <option value="{{ $work->id }}" @selected(old('work_id') == $work->id)>
                                {{ $work->title }}
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-sm text-gray-600">
                        ここで作品を選ぶと、CSV内の work_id が空でもその作品に登録されます。
                    </p>
                </div>

                <div class="mb-5">
                    <label for="default_status" class="mb-1 block font-medium">
                        初期状態
                    </label>
                    <select id="default_status" name="default_status" class="w-full">
                        <option value="draft" @selected(old('default_status', 'draft') === 'draft')>下書き</option>
                        <option value="published" @selected(old('default_status') === 'published')>公開</option>
                        <option value="private" @selected(old('default_status') === 'private')>非公開</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-600">
                        CSV内の status が空の場合、この状態で登録します。
                    </p>
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
                </div>

                <button type="submit" class="oshi-btn">
                    CSVから一括登録する
                </button>
            </form>
        </div>

        <div class="oshi-card mt-6">
            <h2 class="mb-3 text-xl font-bold">
                CSVの形式
            </h2>

            <p class="mb-3">
                1行目はヘッダーです。以下の列名を使用してください。
            </p>

            <div class="oshi-table-wrap">
                <table class="oshi-table">
                    <thead>
                        <tr>
                            <th>列名</th>
                            <th>内容</th>
                            <th>必須</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>work_id</td><td>登録先作品ID。画面で作品を選ぶ場合は空でOK。</td><td>条件付き</td></tr>
                        <tr><td>name</td><td>キャラクター名</td><td>必須</td></tr>
                        <tr><td>name_kana</td><td>読み仮名</td><td>任意</td></tr>
                        <tr><td>age</td><td>年齢</td><td>任意</td></tr>
                        <tr><td>affiliation</td><td>所属</td><td>任意</td></tr>
                        <tr><td>grade_class</td><td>学年クラス</td><td>任意</td></tr>
                        <tr><td>first_person</td><td>一人称</td><td>任意</td></tr>
                        <tr><td>tone</td><td>口調</td><td>任意</td></tr>
                        <tr><td>tone_examples</td><td>口調の例</td><td>任意</td></tr>
                        <tr><td>personality</td><td>性格・特徴</td><td>任意</td></tr>
                        <tr><td>appearance</td><td>外見の特徴</td><td>任意</td></tr>
                        <tr><td>background</td><td>背景・経歴</td><td>任意</td></tr>
                        <tr><td>status</td><td>published / draft / private</td><td>任意</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
