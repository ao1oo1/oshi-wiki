@php
    $character = $character ?? $originalCharacter ?? null;

    $oldValue = function (string $key, $default = '') use ($character) {
        return old($key, $character?->{$key} ?? $default);
    };

    $isMainCharacter = (bool) old('is_main_character', $character?->is_main_character ?? false);
    $status = old('status', $character?->status ?? 'active');
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm font-bold text-red-600">
        <p>入力内容を確認してください。</p>
        <ul class="mt-3 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-8">
    {{-- V3_ORIGINAL_CHARACTER_IMAGE_FORM --}}
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">キャラクター画像</p>
            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">見た目の参考画像</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                キャラクターの外見を確認しやすくするための画像を1枚登録できます。
                画像の登録は任意です。JPG、JPEG、PNG、WebP形式、4MB以下、
                縦横4,000px以下の画像を選択してください。
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-[220px_minmax(0,1fr)] md:items-start">
            <div>
                @if ($character?->image_path)
                    <img
                        id="character-image-current"
                        src="{{ route('writer.original-characters.image', $character) }}"
                        alt="{{ $character->name }}の登録画像"
                        class="rounded-3xl border border-[#E2E8F0] bg-[#F7FAFC]"
                        style="display:block; width:240px; height:240px; max-width:100%; object-fit:contain;"
                    >
                @else
                    <div
                        id="character-image-placeholder"
                        class="flex items-center justify-center rounded-3xl border border-dashed border-[#CBD5E0] bg-[#F7FAFC] p-6 text-center"
                        style="width:240px; height:240px; max-width:100%;"
                    >
                        <div>
                            <p class="text-4xl">🖼️</p>
                            <p class="mt-3 text-sm font-bold leading-6 text-[#A0AEC0]">
                                画像はまだ<br>登録されていません
                            </p>
                        </div>
                    </div>
                @endif

                <img
                    id="character-image-preview"
                    src=""
                    alt="選択した画像のプレビュー"
                    class="hidden rounded-3xl border border-[#E2E8F0] bg-[#F7FAFC]"
                    style="display:none; width:240px; height:240px; max-width:100%; object-fit:contain;"
                >
            </div>

            <div class="space-y-5">
                <div>
                    <label for="character_image"
                           class="mb-2 block text-sm font-bold text-[#2D3748]">
                        画像ファイル
                    </label>

                    <input
                        id="character_image"
                        type="file"
                        name="character_image"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        class="block w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3 text-sm font-bold text-[#4A5568] file:mr-4 file:rounded-xl file:border-0 file:bg-[#FED7E2] file:px-4 file:py-2 file:font-bold file:text-[#2D3748] hover:file:opacity-90"
                    >

                    <p class="mt-2 text-xs font-bold leading-6 text-[#A0AEC0]">
                        画像の登録は任意です。登録できる形式：JPG・JPEG・PNG・WebP ／ 最大4MB ／ 縦横4,000px以下
                    </p>
                </div>

                @if ($character?->image_path)
                    <div class="rounded-2xl bg-[#F7FAFC] p-4">
                        <p class="text-xs font-bold text-[#A0AEC0]">
                            現在登録されている画像
                        </p>
                        <p class="mt-1 break-all text-sm font-bold text-[#2D3748]">
                            {{ $character->image_original_name ?: '登録済み画像' }}
                        </p>
                    </div>

                    <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-red-100 bg-red-50 p-4">
                        <input
                            type="checkbox"
                            name="remove_image"
                            value="1"
                            class="mt-1 h-4 w-4 shrink-0 rounded border-red-300 text-red-500 focus:ring-red-300"
                            @checked(old('remove_image'))
                        >
                        <span>
                            <span class="block text-sm font-bold text-red-600">
                                現在の画像を削除する
                            </span>
                            <span class="mt-1 block text-xs font-bold leading-6 text-red-400">
                                新しい画像を選択した場合は、新しい画像への差し替えが優先されます。
                            </span>
                        </span>
                    </label>
                @endif
            </div>
        </div>
    </section>
    {{-- /V3_ORIGINAL_CHARACTER_IMAGE_FORM --}}

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">STEP 1</p>
            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">基本情報</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                キャラクター名や年齢、所属など、プロンプトに反映したい基本情報を入力します。
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="name">名前 <span class="text-red-500">必須</span></label>
                <input id="name"
                       type="text"
                       name="name"
                       value="{{ $oldValue('name') }}"
                       placeholder="例：キャラクター名"
                       required>
            </div>

            <div>
                <label for="name_kana">読み仮名</label>
                <input id="name_kana"
                       type="text"
                       name="name_kana"
                       value="{{ $oldValue('name_kana') }}"
                       placeholder="例：きゃらくたーめい">
            </div>

            <div>
                <label for="age">年齢</label>
                <input id="age"
                       type="text"
                       name="age"
                       value="{{ $oldValue('age') }}"
                       placeholder="例：17歳 / 不明 / 外見年齢20代">
            </div>

            <div>
                <label for="gender">性別</label>
                <input id="gender"
                       type="text"
                       name="gender"
                       value="{{ $oldValue('gender') }}"
                       placeholder="例：女性 / 男性 / 不明 / 未設定">
            </div>

            <div>
                <label for="affiliation">所属</label>
                <input id="affiliation"
                       type="text"
                       name="affiliation"
                       value="{{ $oldValue('affiliation') }}"
                       placeholder="例：学校名、組織名、職業など">
            </div>

            <div>
                <label for="school_grade">学年・クラス</label>
                <input id="school_grade"
                       type="text"
                       name="school_grade"
                       value="{{ $oldValue('school_grade') }}"
                       placeholder="例：2年A組、所属部署など">
            </div>
        </div>

        <div class="mt-6 flex flex-wrap gap-4">
            <label class="inline-flex items-center gap-3 rounded-2xl bg-[#F7FAFC] px-5 py-4">
                <input type="checkbox"
                       name="is_main_character"
                       value="1"
                       @checked($isMainCharacter)>
                <span class="font-bold text-[#2D3748]">主人公として扱う</span>
            </label>

            <div class="min-w-[220px]">
                <label for="status">ステータス</label>
                <select id="status" name="status">
                    <option value="active" @selected($status === 'active')>有効</option>
                    <option value="draft" @selected($status === 'draft')>下書き</option>
                </select>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">STEP 2</p>
            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">話し方</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                一人称や口調を登録しておくと、プロンプト作成時にキャラクターらしさを反映しやすくなります。
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="first_person">一人称</label>
                <input id="first_person"
                       type="text"
                       name="first_person"
                       value="{{ $oldValue('first_person') }}"
                       placeholder="例：私、俺、僕、自分など">
            </div>

            <div>
                <label for="speech_style">口調</label>
                <textarea id="speech_style"
                          name="speech_style"
                          placeholder="例：丁寧語で話す。親しい相手には少し砕ける。">{{ $oldValue('speech_style') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label for="speech_examples">口調例</label>
                <textarea id="speech_examples"
                          name="speech_examples"
                          placeholder="例：「こんにちは」「それは違うと思います」など、実際のセリフ例">{{ $oldValue('speech_examples') }}</textarea>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">STEP 3</p>
            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">キャラクター設定</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                性格、外見、背景を入力します。小説本文に反映したい情報を中心にまとめてください。
            </p>
        </div>

        <div class="space-y-5">
            <div>
                <label for="personality">性格・特徴</label>
                <textarea id="personality"
                          name="personality"
                          placeholder="性格、癖、価値観、得意なこと、苦手なことなど">{{ $oldValue('personality') }}</textarea>
            </div>

            <div>
                <label for="appearance">外見</label>
                <textarea id="appearance"
                          name="appearance"
                          placeholder="髪型、目の色、服装、体格、雰囲気など">{{ $oldValue('appearance') }}</textarea>
            </div>

            <div>
                <label for="background">背景・経歴</label>
                <textarea id="background"
                          name="background"
                          placeholder="過去、家族構成、所属理由、物語上の立ち位置など">{{ $oldValue('background') }}</textarea>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">STEP 4</p>
            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">プロンプト用メモ</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                AIに守ってほしい設定や、避けたい表現を登録します。重要な設定はここに入れておくと便利です。
            </p>
        </div>

        <div class="space-y-5">
            <div>
                <label for="important_points">絶対に守りたい設定</label>
                <textarea id="important_points"
                          name="important_points"
                          placeholder="例：この設定は必ず守る、性格を変えない、特定の呼び方を守るなど">{{ $oldValue('important_points') }}</textarea>
            </div>

            <div>
                <label for="ng_points">NG設定・避けたい表現</label>
                <textarea id="ng_points"
                          name="ng_points"
                          placeholder="例：させたくない行動、避けたい口調、苦手な描写など">{{ $oldValue('ng_points') }}</textarea>
            </div>

            <div>
                <label for="notes">備考</label>
                <textarea id="notes"
                          name="notes"
                          placeholder="その他、プロンプトに含めたい補足">{{ $oldValue('notes') }}</textarea>
            </div>
        </div>
    </section>

    <div class="flex flex-col gap-3 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
        <p class="text-sm font-bold text-[#718096]">
            入力内容はあとから編集できます。
        </p>

        <div class="flex flex-col gap-3 md:flex-row">
            <a href="{{ route('writer.original-characters.index') }}"
               class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
                一覧へ戻る
            </a>

            <button type="submit"
                    class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                保存する
            </button>
        </div>
    </div>
</div>

{{-- V3_ORIGINAL_CHARACTER_IMAGE_PREVIEW --}}
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('character_image');
        const preview = document.getElementById('character-image-preview');
        const currentImage = document.getElementById('character-image-current');
        const placeholder = document.getElementById('character-image-placeholder');

        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', () => {
            const file = input.files && input.files[0];

            if (!file) {
                preview.src = '';
                preview.classList.add('hidden');
                preview.style.display = 'none';

                if (currentImage) {
                    currentImage.classList.remove('hidden');
                }

                if (placeholder) {
                    placeholder.classList.remove('hidden');
                }

                return;
            }

            const reader = new FileReader();

            reader.addEventListener('load', (event) => {
                preview.src = event.target.result;
                preview.classList.remove('hidden');
                preview.style.display = 'block';

                if (currentImage) {
                    currentImage.classList.add('hidden');
                }

                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
            });

            reader.readAsDataURL(file);
        });
    });
</script>
{{-- /V3_ORIGINAL_CHARACTER_IMAGE_PREVIEW --}}
