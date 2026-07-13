@include('writer.original_characters._layout_start', ['title' => 'オリジナルキャラクター詳細'])

@php
    $character = $character
        ?? $originalCharacter
        ?? null;
@endphp

@if (! $character)
    <div class="rounded-3xl border border-red-200 bg-white p-8 text-red-600">
        キャラクターデータが見つかりません。
    </div>
@else
    <div class="mb-8">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-[#2D3748]">オリジナルキャラクター詳細</h1>
                <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
                    プロンプト作成に使用するキャラクター情報を確認できます。
                </p>
            </div>

            <div class="writer-original-character-top-actions-hidden flex flex-wrap gap-3">
                <a href="{{ route('writer.original-characters.index') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                    一覧へ戻る
                </a>

                <a href="{{ route('writer.original-characters.edit', $character) }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
                    編集
                </a>

                <form method="POST"
                      action="{{ route('writer.original-characters.destroy', $character) }}"
                      class="inline"
                      onsubmit="return confirm('このキャラクターを削除しますか？');">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-2xl border border-red-200 bg-white px-5 py-3 text-sm font-bold text-red-600 hover:bg-red-50">
                        削除
                    </button>
                </form>
            </div>
        </div>
    </div>

    <section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        {{-- V3_ORIGINAL_CHARACTER_IMAGE_SHOW --}}
        <div class="mb-8 grid gap-6 md:grid-cols-[240px_minmax(0,1fr)] md:items-start">
            <div>
                @if ($character->image_path)
                    <img
                        src="{{ route('writer.original-characters.image', $character) }}"
                        alt="{{ $character->name }}の登録画像"
                        class="rounded-3xl border border-[#E2E8F0] bg-[#F7FAFC]"
                        style="display:block; width:min(33.333vw, 360px); height:auto; max-width:100%; max-height:420px; object-fit:contain;"
                    >
                @else
                    <div class="flex items-center justify-center rounded-3xl border border-dashed border-[#CBD5E0] bg-[#F7FAFC]"
                         style="width:min(33.333vw, 360px); height:min(33.333vw, 360px); max-width:100%;">
                        <div class="text-center">
                            <p class="text-5xl">🖼️</p>
                            <p class="mt-3 text-sm font-bold text-[#A0AEC0]">
                                画像は登録されていません
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            <div class="min-w-0">
        {{-- /V3_ORIGINAL_CHARACTER_IMAGE_SHOW --}}

        <div class="mb-5 flex flex-wrap items-center gap-2">
            @if ($character->is_main_character)
                <span class="rounded-full bg-[#FED7E2] px-3 py-1 text-xs font-bold text-[#2D3748]">
                    主人公
                </span>
            @else
                <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#4A5568]">
                    通常キャラクター
                </span>
            @endif

            @if (($character->status ?? 'active') === 'active')
                <span class="rounded-full bg-[#FFF1F5] px-3 py-1 text-xs font-bold text-[#2D3748]">
                    有効
                </span>
            @else
                <span class="rounded-full bg-[#EDF2F7] px-3 py-1 text-xs font-bold text-[#4A5568]">
                    {{ $character->status }}
                </span>
            @endif

            @if ($character->gender)
                <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#4A5568]">
                    {{ $character->gender }}
                </span>
            @endif

            @if ($character->age)
                <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#4A5568]">
                    {{ $character->age }}
                </span>
            @endif
        </div>

        <h2 class="text-4xl font-bold leading-snug text-[#2D3748]">
            {{ $character->name }}
        </h2>

        @if ($character->name_kana)
            <p class="mt-2 text-base font-bold text-[#A0AEC0]">
                {{ $character->name_kana }}
            </p>
        @endif
            </div>
        </div>
    </section>

    <section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <h3 class="mb-6 text-xl font-bold text-[#2D3748]">基本情報</h3>

        <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">所属</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $character->affiliation ?: '未入力' }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">学年・クラス</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $character->school_grade ?: '未入力' }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">一人称</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $character->first_person ?: '未入力' }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">作成日</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $character->created_at?->format('Y/m/d H:i') }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">更新日</p>
                <p class="mt-2 font-bold text-[#2D3748]">{{ $character->updated_at?->format('Y/m/d H:i') }}</p>
            </div>
        </div>
    </section>

    <section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <h3 class="mb-6 text-xl font-bold text-[#2D3748]">話し方</h3>

        <div class="grid gap-5 md:grid-cols-2">
            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">口調</p>
                <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $character->speech_style ?: '未入力' }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">口調例</p>
                <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $character->speech_examples ?: '未入力' }}</p>
            </div>
        </div>
    </section>

    <section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <h3 class="mb-6 text-xl font-bold text-[#2D3748]">キャラクター設定</h3>

        <div class="grid gap-5">
            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">性格・特徴</p>
                <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $character->personality ?: '未入力' }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">外見</p>
                <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $character->appearance ?: '未入力' }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">背景・経歴</p>
                <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $character->background ?: '未入力' }}</p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <h3 class="mb-6 text-xl font-bold text-[#2D3748]">プロンプト用メモ</h3>

        <div class="grid gap-5">
            <div class="rounded-2xl bg-[#FFF1F5] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">絶対に守りたい設定</p>
                <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $character->important_points ?: '未入力' }}</p>
            </div>

            <div class="rounded-2xl bg-[#FFF1F5] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">NG設定・避けたい表現</p>
                <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $character->ng_points ?: '未入力' }}</p>
            </div>

            <div class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="text-xs font-bold text-[#A0AEC0]">備考</p>
                <p class="mt-2 whitespace-pre-wrap font-bold leading-7 text-[#2D3748]">{{ $character->notes ?: '未入力' }}</p>
            </div>
        </div>
    </section>
@endif


<div class="writer-original-character-bottom-actions mt-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
    <div class="grid gap-3 md:grid-cols-3">
        <a href="{{ route('writer.original-characters.index') }}"
           class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
            一覧へ戻る
        </a>

        <a href="{{ route('writer.original-characters.edit', $character) }}"
           class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
            編集
        </a>

        <form method="POST"
              action="{{ route('writer.original-characters.destroy', $character) }}"
              onsubmit="return confirm('このキャラクターを削除しますか？');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-2xl border border-red-200 bg-white px-6 py-3 font-bold text-red-600 hover:bg-red-50">
                削除
            </button>
        </form>
    </div>
</div>


@include('writer.original_characters._layout_end')
