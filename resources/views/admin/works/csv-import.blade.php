<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            CSV取り込み
        </h2>
    </x-slot>

    @php
        $columnDescriptions = [
            'title' => ['作品名', '必須'],
            'title_kana' => ['読み仮名', '任意'],
            'slug' => ['URL用識別子。空欄の場合は作品名から自動生成', '任意'],
            'genre' => ['ジャンル', '任意'],
            'original_media' => ['原作媒体', '任意'],
            'official_url' => ['公式URL', '任意'],
            'guideline_url' => ['二次創作ガイドラインURL', '任意'],
            'description' => ['作品概要・説明', '任意'],
            'timeline_setting' => ['原作のどの時点を基準にするか', '任意'],
            'building_layout' => ['校舎や寮の間取り・構造', '任意'],
            'character_room_seat' => ['キャラクターごとの部屋・席の位置', '任意'],
            'hangout_places' => ['キャラクターがよくいる場所・たまり場', '任意'],
            'restricted_secret_places' => ['立ち入り禁止区域・秘密の場所', '任意'],
            'cafeteria_store_menu' => ['食堂・購買のメニューや人気商品', '任意'],
            'daily_schedule' => ['一日のスケジュール', '任意'],
            'school_dorm_rules' => ['校則・寮則', '任意'],
            'uniform_details' => ['制服の詳細', '任意'],
            'casual_holiday_rules' => ['私服・休日の過ごし方のルール', '任意'],
            'duty_system' => ['当番制度', '任意'],
            'class_grade_system' => ['クラス編成・学年の仕組み', '任意'],
            'organizations_memberships' => ['生徒会・委員会・部活動と所属', '任意'],
            'ranking_system' => ['成績・序列の制度', '任意'],
            'adult_roles' => ['教師・寮母など大人キャラクターの配置と役割', '任意'],
            'annual_events' => ['年間行事とその時期', '任意'],
            'event_flow' => ['行事の具体的な流れ・名物イベント', '任意'],
            'story_season' => ['作中の季節・月がわかる情報', '任意'],
            'school_location' => ['学園など主要舞台の所在地', '任意'],
            'commute_environment' => ['通学手段・通学路の風景', '任意'],
            'nearby_shops' => ['近くの店・登場人物の行きつけ', '任意'],
            'climate_nature' => ['気候・自然環境', '任意'],
            'sounds' => ['作中で重要な音・環境音', '任意'],
            'symbolic_motifs' => ['作品や舞台の象徴的なモチーフ', '任意'],
            'required_belongings' => ['指定の持ち物・必携品', '任意'],
            'status' => ['published / draft / private', '任意'],
        ];
    @endphp

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
                        CSVをアップロードして、複数の作品をまとめて登録・更新できます。
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
                    <label for="default_status" class="mb-1 block font-medium">
                        初期状態
                    </label>
                    <select id="default_status" name="default_status" class="w-full">
                        <option value="draft" @selected(old('default_status', 'draft') === 'draft')>下書き</option>
                        <option value="published" @selected(old('default_status') === 'published')>公開</option>
                        <option value="private" @selected(old('default_status') === 'private')>非公開</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-600">
                        CSV内のstatusが空の場合、この状態で登録します。
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
                        required
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
            <h2 class="mb-3 text-xl font-bold">
                CSVの形式
            </h2>

            <p class="mb-3">
                1行目はヘッダーです。以下の列名を使用してください。
                エクスポートしたCSVを編集して、そのまま再インポートできます。
            </p>

            <div class="mb-4 rounded bg-blue-50 px-4 py-3 text-sm text-blue-900">
                work_idが既存データと一致する行は更新されます。
                tag_idsまたはtag_names列を含めた場合は、作品タグをCSVの内容に同期します。
                canon_events_jsonとterm_usages_jsonを含めた場合は、年表・用語使用例もCSVの内容に同期します。
                character_idsまたはcharacter_names列を含めた場合は、作品とキャラクターの紐付けをCSVの内容に同期します。
                ただし、その作品を主作品にしているキャラクターは解除できません。
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
                        <tr>
                            <td>work_id</td>
                            <td>既存IDと一致した場合は更新。空欄または存在しないIDは新規登録。</td>
                            <td>任意</td>
                        </tr>

                        @foreach (($workColumns ?? []) as $column)
                            @php
                                [$description, $required] = $columnDescriptions[$column]
                                    ?? ['作品情報', '任意'];
                            @endphp
                            <tr>
                                <td>{{ $column }}</td>
                                <td>{{ $description }}</td>
                                <td>{{ $required }}</td>
                            </tr>
                        @endforeach

                        <tr>
                            <td>character_ids</td>
                            <td>既存キャラクターIDをカンマ区切りで指定。列を含めた場合は作品との紐付けをCSVの内容に同期します。主作品として登録されているキャラクターは解除できません。</td>
                            <td>任意</td>
                        </tr>
                        <tr>
                            <td>character_names</td>
                            <td>既存キャラクター名を「｜」区切りで指定。同名キャラクターが複数存在する場合はcharacter_idsを使用してください。</td>
                            <td>任意</td>
                        </tr>
                        <tr>
                            <td>tag_ids</td>
                            <td>既存の作品タグIDをカンマ区切りで指定。列を含めた場合はCSVの内容に同期。</td>
                            <td>任意</td>
                        </tr>
                        <tr>
                            <td>tag_names</td>
                            <td>既存の作品タグ名をカンマ区切りで指定。列を含めた場合はCSVの内容に同期。</td>
                            <td>任意</td>
                        </tr>
                        <tr>
                            <td>canon_events_json</td>
                            <td>
                                原作の重要イベント年表をJSON配列で指定。
                                使用できるキー：{{ implode(', ', $canonEventFields ?? []) }}。
                                最大50件。
                            </td>
                            <td>任意</td>
                        </tr>
                        <tr>
                            <td>term_usages_json</td>
                            <td>
                                用語の意味・作中での使用例をJSON配列で指定。
                                使用できるキー：{{ implode(', ', $termUsageFields ?? []) }}。
                                最大50件。
                            </td>
                            <td>任意</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="oshi-card mt-6">
            <h2 class="mb-3 text-xl font-bold">
                年表・用語使用例の入力方法
            </h2>

            <p class="mb-4 text-sm text-gray-700">
                1作品をCSVの1行で管理するため、複数件の年表・用語使用例はJSON配列として入力します。
                手入力よりも、作品をエクスポートして生成された値を編集する方法を推奨します。
            </p>

            <div class="space-y-5">
                <div class="rounded border border-gray-200 bg-gray-50 p-4">
                    <h3 class="font-bold">canon_events_json</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        原作の重要イベント年表。最大50件です。
                    </p>
                    <p class="mt-2 break-all font-mono text-xs text-gray-700">
                        [{"timing":"第1章後","event_name":"〇〇事件","event_status":"occurred","notes":"補足"}]
                    </p>
                </div>

                <div class="rounded border border-gray-200 bg-gray-50 p-4">
                    <h3 class="font-bold">term_usages_json</h3>
                    <p class="mt-1 text-sm text-gray-600">
                        用語の意味と、作中での使用例。最大50件です。
                    </p>
                    <p class="mt-2 break-all font-mono text-xs text-gray-700">
                        [{"term":"用語名","meaning":"用語の意味","usage_example":"作中での使用例"}]
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
