@php
    $writerCsvColumns = app(\App\Services\WriterCsvService::class)
        ->headers($type);

    $writerCsvColumnDescriptions = [
        'name' => '必須。キャラクター名',
        'name_kana' => 'キャラクター名の読み仮名',
        'age' => '年齢',
        'gender' => '性別',
        'birthday' => '誕生日',
        'height' => '身長',
        'weight' => '体重',
        'blood_type' => '血液型',
        'affiliation' => '所属',
        'grade_class' => '学年・クラス',
        'occupation' => '職業・役職',
        'first_person' => '一人称',
        'second_person' => '二人称',
        'speech_style' => '口調・話し方',
        'speech_examples' => '口調の例',
        'personality' => '性格',
        'appearance' => '外見の特徴',
        'background' => '背景・経歴',
        'abilities' => '能力・特技',
        'likes' => '好きなもの',
        'dislikes' => '苦手・嫌いなもの',
        'notes' => '補足事項',
        'from_character_name' => '必須。関係元の登録済みキャラクター名',
        'to_character_name' => '必須。関係先の登録済みキャラクター名',
        'called_name' => '相手を呼ぶときの呼称',
        'relationship_type' => '関係の種類',
        'impression' => '相手への印象',
        'feelings' => '相手への感情・想い',
        'relationship_detail' => '関係性の詳しい内容',
        'title' => '必須。タイトル',
        'prompt_body' => '必須。プロンプト本文',
        'description' => '説明・概要',
        'category' => '分類・カテゴリ',
        'body' => '必須。ストーリー本文',
        'summary' => 'あらすじ・概要',
        'status' => '状態',
        'sort_order' => '表示順。整数',
        'created_at' => '作成日時。エクスポート時の確認用',
        'updated_at' => '更新日時。エクスポート時の確認用',
    ];
@endphp

<details
    class="mt-5 overflow-hidden rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC]"
    data-writer-csv-format="{{ $type }}"
>
    <summary
        class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 font-bold text-[#2D3748] marker:content-none"
    >
        <span>CSVの形式</span>

        <span
            aria-hidden="true"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-white text-lg shadow-sm"
        >
            ＋
        </span>
    </summary>

    <div class="border-t border-[#E2E8F0] bg-white px-5 py-5">
        <p class="text-sm font-bold leading-7 text-[#718096]">
            1行目には、次の列名を英字のまま入力してください。
            必須項目が空欄の場合は登録できません。
        </p>

        <div class="mt-4 overflow-x-auto rounded-xl border border-[#E2E8F0]">
            <table class="min-w-full border-collapse text-left text-sm">
                <thead class="bg-[#FFF1F5] text-[#2D3748]">
                    <tr>
                        <th class="whitespace-nowrap px-4 py-3 font-bold">
                            列名
                        </th>
                        <th class="min-w-72 px-4 py-3 font-bold">
                            説明
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-[#E2E8F0]">
                    @foreach ($writerCsvColumns as $column)
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-4 py-3 font-mono font-bold text-[#2D3748]">
                                {{ $column }}
                            </td>
                            <td class="px-4 py-3 font-bold leading-6 text-[#718096]">
                                {{ $writerCsvColumnDescriptions[$column]
                                    ?? '登録する内容を入力する列' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</details>
