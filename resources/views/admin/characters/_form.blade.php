@if ($errors->any())
    <div class="mb-6 rounded-2xl bg-red-100 px-5 py-4 text-red-800">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@php
    $fieldClass = 'w-full rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3';
    $sectionClass = 'mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6';
    $currentCharacter = $character ?? null;
@endphp

<section class="{{ $sectionClass }}">
    <h3 class="mb-5 text-xl font-bold text-[#2D3748]">作品・公開設定</h3>

    @php
        $primaryWorkId = (int) old(
            'work_id',
            $currentCharacter?->work_id ?? $selectedWorkId ?? 0
        );

        $linkedWorkIds = collect(old(
            'linked_work_ids',
            $currentCharacter
                ? $currentCharacter->linkedWorks->pluck('id')->all()
                : []
        ))
            ->map(fn ($id) => (int) $id)
            ->push($primaryWorkId)
            ->filter()
            ->unique()
            ->values()
            ->all();
    @endphp

    <div class="mb-5">
        <label for="work_id" class="mb-1 block font-bold">主作品</label>
        <p class="mb-2 text-sm text-[#718096]">
            一覧やキャラクター詳細で代表として表示する作品を1件選択してください。
        </p>
        <select id="work_id" name="work_id" class="{{ $fieldClass }}" required>
            <option value="">選択してください</option>
            @foreach ($works as $work)
                <option value="{{ $work->id }}" @selected($primaryWorkId === (int) $work->id)>
                    {{ $work->title }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-5">
        <label for="linked-work-search" class="mb-1 block font-bold">
            追加で紐付ける作品
        </label>
        <p class="mb-2 text-sm text-[#718096]">
            同じキャラクターを章・シリーズ・派生作品など複数の作品へ紐付けできます。
            主作品は自動的に含まれます。
        </p>

        <input
            id="linked-work-search"
            type="search"
            class="{{ $fieldClass }}"
            placeholder="作品名で絞り込み"
            autocomplete="off"
        >

        <div
            id="linked-work-options"
            class="mt-3 max-h-72 overflow-y-auto rounded-2xl border border-[#E2E8F0] bg-white p-4"
        >
            <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
                @foreach ($works as $work)
                    <label
                        class="linked-work-option flex items-start gap-3 rounded-xl px-3 py-2 hover:bg-[#FFF5F7]"
                        data-work-title="{{ mb_strtolower($work->title) }}"
                    >
                        <input
                            type="checkbox"
                            name="linked_work_ids[]"
                            value="{{ $work->id }}"
                            class="linked-work-checkbox mt-1"
                            @checked(in_array((int) $work->id, $linkedWorkIds, true))
                        >
                        <span>{{ $work->title }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <p id="linked-work-empty" class="mt-3 hidden text-sm text-[#718096]">
            該当する作品がありません。
        </p>
    </div>

    @if (auth()->user()?->canManageAllAdminFeatures())
        <div>
            <label for="status" class="mb-1 block font-bold">状態</label>
            <p class="mb-2 text-sm text-[#718096]">公開ページに表示する場合は「公開」を選択してください。</p>
            <select id="status" name="status" class="{{ $fieldClass }}">
                <option value="draft" @selected(old('status', $currentCharacter?->status ?? 'draft') === 'draft')>下書き</option>
                <option value="published" @selected(old('status', $currentCharacter?->status ?? '') === 'published')>公開</option>
                <option value="private" @selected(old('status', $currentCharacter?->status ?? '') === 'private')>非公開</option>
            </select>
        </div>
    @else
        <div class="rounded-2xl bg-pink-50 p-4 text-sm text-[#4A5568]">
            情報入力スタッフによる登録・編集は、最高管理者への承認申請として保存されます。
        </div>
    @endif
</section>

<section class="{{ $sectionClass }}">
    <h3 class="mb-5 text-xl font-bold text-[#2D3748]">基本情報</h3>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        @foreach ([
            ['name', '名前', true],
            ['name_kana', '読み仮名', false],
            ['real_name', '本名', false],
            ['name_english', '英語表記', false],
            ['gender', '性別', false],
            ['age', '年齢', false],
            ['birthday', '生年月日・誕生日', false],
            ['height', '身長', false],
            ['weight', '体重', false],
            ['blood_type', '血液型', false],
            ['birthplace', '出身地', false],
            ['species', '種族', false],
            ['affiliation', '所属', false],
            ['school_grade_class', '学校・学年・クラス', false],
            ['occupation_position', '職業・役職', false],
        ] as [$field, $label, $required])
            <div>
                <label for="{{ $field }}" class="mb-1 block font-bold">{{ $label }}</label>
                <input
                    id="{{ $field }}"
                    type="text"
                    name="{{ $field }}"
                    value="{{ old($field, data_get($currentCharacter, $field, '')) }}"
                    class="{{ $fieldClass }}"
                    @required($required)
                >
            </div>
        @endforeach
    </div>

    <div class="mt-4">
        <label for="aliases" class="mb-1 block font-bold">別名・愛称</label>
        <textarea id="aliases" name="aliases" rows="3" class="{{ $fieldClass }}">{{ old('aliases', $currentCharacter?->aliases ?? '') }}</textarea>
    </div>

    <div class="mt-4">
        <label for="family_structure" class="mb-1 block font-bold">家族構成</label>
        <textarea id="family_structure" name="family_structure" rows="3" class="{{ $fieldClass }}">{{ old('family_structure', $currentCharacter?->family_structure ?? '') }}</textarea>
    </div>
</section>

<section class="{{ $sectionClass }}">
    <h3 class="mb-5 text-xl font-bold text-[#2D3748]">外見・性格</h3>

    <div class="mb-5">
        <label for="appearance" class="mb-1 block font-bold">外見</label>
        <p class="mb-2 text-sm text-[#718096]">作品内で客観的に確認できる外見情報を入力してください。</p>
        <textarea id="appearance" name="appearance" rows="6" class="{{ $fieldClass }}">{{ old('appearance', $currentCharacter?->appearance ?? '') }}</textarea>
    </div>

    <div>
        <label for="personality" class="mb-1 block font-bold">性格・特徴</label>
        <textarea id="personality" name="personality" rows="6" class="{{ $fieldClass }}">{{ old('personality', $currentCharacter?->personality ?? '') }}</textarea>
    </div>
</section>

<section class="{{ $sectionClass }}">
    <h3 class="mb-5 text-xl font-bold text-[#2D3748]">一人称・口調</h3>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="first_person" class="mb-1 block font-bold">一人称</label>
            <input id="first_person" type="text" name="first_person" value="{{ old('first_person', $currentCharacter?->first_person ?? '') }}" class="{{ $fieldClass }}">
        </div>

        <div>
            <label for="second_person" class="mb-1 block font-bold">二人称</label>
            <input id="second_person" type="text" name="second_person" value="{{ old('second_person', $currentCharacter?->second_person ?? '') }}" class="{{ $fieldClass }}">
        </div>
    </div>

    @foreach ([
        ['basic_tone', '基本口調', 4],
        ['catchphrases', '口癖', 3],
        ['distinctive_speech', '特徴的な言い回し', 4],
        ['tone_by_relationship', '相手による口調の違い', 5],
        ['short_quote_examples', '短いセリフ例', 5],
    ] as [$field, $label, $rows])
        <div class="mt-5">
            <label for="{{ $field }}" class="mb-1 block font-bold">{{ $label }}</label>
            @if ($field === 'short_quote_examples')
                <p class="mb-2 text-sm text-[#718096]">
                    短いセリフ例のみ入力してください。長文の書き起こしや、台詞の大量転載は行わないでください。
                </p>
            @endif
            <textarea id="{{ $field }}" name="{{ $field }}" rows="{{ $rows }}" class="{{ $fieldClass }}">{{ old($field, data_get($currentCharacter, $field, '')) }}</textarea>
        </div>
    @endforeach
</section>

<section class="{{ $sectionClass }}">
    <h3 class="mb-5 text-xl font-bold text-[#2D3748]">能力・経歴・活躍</h3>

    @foreach ([
        ['abilities', '能力・技・戦闘', '能力、技、武器、制限、弱点、戦闘スタイルなど'],
        ['background', '背景・経歴', '過去から現在までの経歴'],
        ['story_activities', '作品内での活躍', '主要な登場、事件、戦闘、成長など'],
    ] as [$field, $label, $description])
        <div class="mb-5 last:mb-0">
            <label for="{{ $field }}" class="mb-1 block font-bold">{{ $label }}</label>
            <p class="mb-2 text-sm text-[#718096]">{{ $description }}</p>
            <textarea id="{{ $field }}" name="{{ $field }}" rows="7" class="{{ $fieldClass }}">{{ old($field, data_get($currentCharacter, $field, '')) }}</textarea>
        </div>
    @endforeach
</section>

<section class="{{ $sectionClass }}">
    <h3 class="mb-5 text-xl font-bold text-[#2D3748]">出典</h3>

    <div class="mb-5">
        <label for="source_title" class="mb-1 block font-bold">ページ名または資料名</label>
        <textarea id="source_title" name="source_title" rows="4" class="{{ $fieldClass }}">{{ old('source_title', $currentCharacter?->source_title ?? '') }}</textarea>
    </div>

    <div class="mb-5">
        <label for="source_url" class="mb-1 block font-bold">URL</label>
        <textarea id="source_url" name="source_url" rows="4" class="{{ $fieldClass }}">{{ old('source_url', $currentCharacter?->source_url ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <label for="source_type" class="mb-1 block font-bold">情報源区分</label>
            <select id="source_type" name="source_type" class="{{ $fieldClass }}">
                <option value="">選択してください</option>
                @foreach (\App\Models\Character::SOURCE_TYPES as $value => $label)
                    <option value="{{ $value }}" @selected(old('source_type', $currentCharacter?->source_type ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="source_reliability" class="mb-1 block font-bold">信頼度</label>
            <select id="source_reliability" name="source_reliability" class="{{ $fieldClass }}">
                <option value="">選択してください</option>
                @foreach (\App\Models\Character::SOURCE_RELIABILITIES as $value => $label)
                    <option value="{{ $value }}" @selected(old('source_reliability', $currentCharacter?->source_reliability ?? '') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="source_checked_at" class="mb-1 block font-bold">確認日</label>
            <input
                id="source_checked_at"
                type="date"
                name="source_checked_at"
                value="{{ old('source_checked_at', $currentCharacter?->source_checked_at?->format('Y-m-d') ?? '') }}"
                class="{{ $fieldClass }}"
            >
        </div>
    </div>
</section>

<section class="{{ $sectionClass }}">
    <h3 class="mb-5 text-xl font-bold text-[#2D3748]">ネタバレ・タグ</h3>

    <div class="mb-6">
        <label for="spoiler_level" class="mb-1 block font-bold">ネタバレ</label>
        <select id="spoiler_level" name="spoiler_level" class="{{ $fieldClass }}">
            @foreach (\App\Models\Character::SPOILER_LEVELS as $value => $label)
                <option value="{{ $value }}" @selected(old('spoiler_level', $currentCharacter?->spoiler_level ?? 'none') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="mb-2 block font-bold">タグ</label>

        @if (($tags ?? collect())->count())
            <div class="grid grid-cols-1 gap-2 rounded-2xl border border-[#E2E8F0] p-4 md:grid-cols-3">
                @foreach ($tags as $tag)
                    <label class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            name="tag_ids[]"
                            value="{{ $tag->id }}"
                            @checked(in_array($tag->id, old('tag_ids', $currentCharacter ? $currentCharacter->tags->pluck('id')->toArray() : [])))
                        >
                        <span>{{ $tag->name }}</span>
                    </label>
                @endforeach
            </div>
        @else
            <p class="text-sm text-[#718096]">まだタグが登録されていません。</p>
        @endif
    </div>
</section>

<button
    type="submit"
    class="rounded-2xl bg-[#2D3748] px-8 py-3 font-bold text-white"
>
    保存する
</button>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const primarySelect = document.getElementById('work_id');
    const searchInput = document.getElementById('linked-work-search');
    const options = Array.from(document.querySelectorAll('.linked-work-option'));
    const emptyMessage = document.getElementById('linked-work-empty');

    const syncPrimaryWork = () => {
        const primaryId = primarySelect?.value;

        document.querySelectorAll('.linked-work-checkbox').forEach((checkbox) => {
            const isPrimary = primaryId !== '' && checkbox.value === primaryId;

            if (isPrimary) {
                checkbox.checked = true;
            }

            checkbox.disabled = isPrimary;
        });
    };

    const filterWorks = () => {
        const keyword = (searchInput?.value || '').trim().toLowerCase();
        let visibleCount = 0;

        options.forEach((option) => {
            const title = option.dataset.workTitle || '';
            const visible = keyword === '' || title.includes(keyword);
            option.classList.toggle('hidden', !visible);

            if (visible) {
                visibleCount += 1;
            }
        });

        emptyMessage?.classList.toggle('hidden', visibleCount !== 0);
    };

    primarySelect?.addEventListener('change', syncPrimaryWork);
    searchInput?.addEventListener('input', filterWorks);

    syncPrimaryWork();
    filterWorks();
});
</script>
