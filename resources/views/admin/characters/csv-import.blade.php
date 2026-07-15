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


            <div class="mb-6 rounded-2xl border border-[#FED7E2] bg-[#FFF5F7] p-5 text-sm leading-7 text-[#4A5568]">
                <p class="font-bold text-[#2D3748]">複数作品の指定方法</p>
                <p><code>primary_work_id</code>：主作品IDを1件指定します。</p>
                <p><code>work_ids</code>：紐付ける作品IDをカンマ区切りで指定します。主作品IDは自動的に含まれます。</p>
                <p>旧形式の<code>work_id</code>のみでも取り込めます。その場合は主作品として登録されます。</p>
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
                エクスポートしたCSVを編集して、そのまま再インポートできます。
            </p>

            <div class="mb-4 rounded bg-blue-50 px-4 py-3 text-sm text-blue-900">
                character_idが既存データと一致する行は更新されます。
                tag_idsまたはtag_names列を含めた場合は、キャラクターのタグをCSVの内容に同期します。
            </div>

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
                        <tr><td>character_id</td><td>既存IDと一致した場合は更新。空欄または存在しないIDは新規登録。</td><td>任意</td></tr>
                        <tr><td>work_id</td><td>登録先作品ID。画面で作品を選ぶ場合は空欄可。</td><td>条件付き</td></tr>
                        <tr><td>name / character_name</td><td>キャラクター名</td><td>必須</td></tr>
                        <tr><td>name_kana</td><td>読み仮名</td><td>任意</td></tr>
                        <tr><td>real_name</td><td>本名</td><td>任意</td></tr>
                        <tr><td>aliases</td><td>別名・愛称</td><td>任意</td></tr>
                        <tr><td>name_english</td><td>英語表記</td><td>任意</td></tr>
                        <tr><td>gender</td><td>性別</td><td>任意</td></tr>
                        <tr><td>age / birthday</td><td>年齢・生年月日／誕生日</td><td>任意</td></tr>
                        <tr><td>height / weight / blood_type</td><td>身長・体重・血液型</td><td>任意</td></tr>
                        <tr><td>birthplace / species</td><td>出身地・種族</td><td>任意</td></tr>
                        <tr><td>affiliation</td><td>所属</td><td>任意</td></tr>
                        <tr><td>school_grade_class</td><td>学校・学年・クラス</td><td>任意</td></tr>
                        <tr><td>occupation_position</td><td>職業・役職</td><td>任意</td></tr>
                        <tr><td>family_structure</td><td>家族構成</td><td>任意</td></tr>
                        <tr><td>appearance / personality</td><td>外見・性格／特徴</td><td>任意</td></tr>
                        <tr><td>first_person / second_person</td><td>一人称・二人称</td><td>任意</td></tr>
                        <tr><td>basic_tone</td><td>基本口調</td><td>任意</td></tr>
                        <tr><td>catchphrases</td><td>口癖</td><td>任意</td></tr>
                        <tr><td>distinctive_speech</td><td>特徴的な言い回し</td><td>任意</td></tr>
                        <tr><td>tone_by_relationship</td><td>相手による口調の違い</td><td>任意</td></tr>
                        <tr><td>short_quote_examples</td><td>短いセリフ例</td><td>任意</td></tr>
                        <tr><td>abilities</td><td>能力・技・戦闘</td><td>任意</td></tr>
                        <tr><td>background</td><td>背景・経歴</td><td>任意</td></tr>
                        <tr><td>story_activities</td><td>作品内での活躍</td><td>任意</td></tr>
                        <tr><td>source_title / source_url</td><td>出典名・URL</td><td>任意</td></tr>
                        <tr><td>source_type</td><td>official / semi_official / wikipedia / encyclopedia / personal_site。日本語表記も可。</td><td>任意</td></tr>
                        <tr><td>source_reliability</td><td>high / medium / low。高・中・低も可。</td><td>任意</td></tr>
                        <tr><td>source_checked_at</td><td>確認日。YYYY-MM-DD形式。</td><td>任意</td></tr>
                        <tr><td>spoiler_level</td><td>none / minor / major / latest_chapter / anime_spoiler。日本語表記も可。</td><td>任意</td></tr>
                        <tr><td>status</td><td>published / draft / private</td><td>任意</td></tr>
                        <tr><td>tag_ids</td><td>タグIDをカンマ区切りで指定</td><td>任意</td></tr>
                        <tr><td>tag_names</td><td>既存タグ名をカンマ区切りで指定</td><td>任意</td></tr>
                        <tr><td>grade_class / tone / tone_examples</td><td>旧CSV互換用。新規作成では新しい列名を推奨。</td><td>任意</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
