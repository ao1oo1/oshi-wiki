@include('writer.original_characters._layout_start', ['title' => $originalCharacter->name])
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <p class="text-sm text-[#718096]">オリジナルキャラクター</p>
            <h1 class="mt-1 text-2xl font-bold">{{ $originalCharacter->name }}</h1>
            @if ($originalCharacter->name_kana)
                <p class="mt-1 text-sm text-[#718096]">{{ $originalCharacter->name_kana }}</p>
            @endif
        </div>

        <div class="flex gap-2">
            <a href="{{ route('writer.original-characters.edit', $originalCharacter) }}" class="rounded bg-[#FED7E2] px-5 py-2 font-bold text-[#2D3748]">
                編集
            </a>

            <form method="POST" action="{{ route('writer.original-characters.destroy', $originalCharacter) }}" onsubmit="return confirm('このオリジナルキャラクターを削除しますか？');">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded border border-red-300 px-5 py-2 text-red-600">
                    削除
                </button>
            </form>
        </div>
    </div>

    <div class="rounded-lg border border-[#E2E8F0] bg-white p-6">
        <dl class="grid gap-5 md:grid-cols-2">
            @foreach ([
                '年齢' => $originalCharacter->age,
                '性別' => $originalCharacter->gender,
                '所属' => $originalCharacter->affiliation,
                '学年・クラス' => $originalCharacter->school_grade,
                '一人称' => $originalCharacter->first_person,
                '状態' => $originalCharacter->status === 'active' ? '有効' : '下書き',
            ] as $label => $value)
                <div>
                    <dt class="text-sm font-bold text-[#718096]">{{ $label }}</dt>
                    <dd class="mt-1">{{ $value ?: '-' }}</dd>
                </div>
            @endforeach
        </dl>

        @foreach ([
            '口調' => $originalCharacter->speech_style,
            '口調例' => $originalCharacter->speech_examples,
            '性格・特徴' => $originalCharacter->personality,
            '外見' => $originalCharacter->appearance,
            '背景・経歴' => $originalCharacter->background,
            '絶対に守りたい設定' => $originalCharacter->important_points,
            'NG設定・避けたい表現' => $originalCharacter->ng_points,
            '備考' => $originalCharacter->notes,
        ] as $label => $value)
            <section class="mt-6 border-t border-[#E2E8F0] pt-5">
                <h2 class="font-bold">{{ $label }}</h2>
                <div class="mt-2 whitespace-pre-line text-sm leading-7 text-[#4A5568]">{{ $value ?: '-' }}</div>
            </section>
        @endforeach
    </div>

    <div class="mt-6">
        <a href="{{ route('writer.original-characters.index') }}" class="rounded border border-[#A0AEC0] px-5 py-2">
            一覧へ戻る
        </a>
    </div>
@include('writer.original_characters._layout_end')
