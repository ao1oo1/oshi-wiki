@php
    $workFieldCategories = [
        '建物・空間' => [
            ['building_layout', '校舎や寮の間取り・構造', '例：二人部屋が基本。寮長は一人部屋。教室は3階建て校舎に配置、渡り廊下あり。'],
            ['character_room_seat', 'キャラごとの部屋・席の位置', '例：〇〇と△△はルームメイト。〇〇の席は窓際最後列。'],
            ['hangout_places', 'キャラがよくいる場所・たまり場', '例：〇〇は放課後よく屋上にいる。部室が主なたまり場。'],
            ['restricted_secret_places', '立ち入り禁止区域・秘密の場所', '例：旧校舎は立ち入り禁止。裏山に秘密の抜け道がある。'],
            ['cafeteria_store_menu', '食堂・購買のメニューや人気商品', '例：購買の〇〇パンが人気で争奪戦になる。'],
        ],
        '生活・ルール' => [
            ['daily_schedule', '一日のスケジュール', '例：6時起床、22時門限、23時消灯。'],
            ['school_dorm_rules', '校則・寮則', '例：スマホは夜間没収。外出は届け出制。違反すると謹慎。'],
            ['uniform_details', '制服の詳細', '例：〇〇寮は赤を基調とした色合い。冬服・夏服の違い。'],
            ['casual_holiday_rules', '私服・休日の過ごし方のルール', '例：休日は届け出をすれば外出可能。私服は自由。'],
            ['duty_system', '当番制度', '例：掃除当番は週替わり。日直は出席番号順。'],
        ],
        '組織・制度' => [
            ['class_grade_system', 'クラス編成・学年の仕組み', '例：1学年4クラス。クラス替えなし。'],
            ['organizations_memberships', '生徒会・委員会・部活動とキャラの所属', '例：〇〇は生徒会長。△△はバスケ部所属。'],
            ['ranking_system', '成績・序列の制度', '例：試験順位が掲示される。特待生制度あり。'],
            ['adult_roles', '教師・寮母など大人キャラの配置と役割', '例：〇〇先生は1年の担任兼寮監。'],
        ],
        '行事・時間の流れ' => [
            ['annual_events', '年間行事とその時期', '例：〇月に体育祭、〇月に文化祭が行われる。'],
            ['event_flow', '行事の具体的な流れ・名物イベント', '例：文化祭最終日に後夜祭のキャンプファイヤーがある。'],
            ['story_season', '作中の季節・月がわかる情報', '例：物語は春の入学式から始まり、現在は夏休み前。'],
        ],
        '地理・周辺環境' => [
            ['school_location', '学園の所在地', '例：山奥の全寮制学園。都会から電車で2時間。'],
            ['commute_environment', '通学手段・通学路の風景', '例：最寄り駅から徒歩20分。桜並木の坂道を登る。'],
            ['nearby_shops', '近くの店・生徒の行きつけ', '例：駅前のファミレスが定番のたまり場。'],
            ['climate_nature', '気候・自然環境', '例：冬は雪が積もる地域。海が近く潮の香りがする。'],
        ],
        '小物・感覚的な情報' => [
            ['sounds', '音', '例：始業は鐘の音。夜9時に消灯放送が流れる。'],
            ['symbolic_motifs', '学園の象徴的なモチーフ', '例：校章は百合の紋章。スクールカラーは紺。'],
            ['required_belongings', '持ち物の指定', '例：指定カバンとIDカードの携帯が必須。'],
        ],
    ];

    $oldCanonEvents = old(
        'canon_events',
        isset($work)
            ? $work->canonEvents->map(fn ($item) => [
                'timing' => $item->timing,
                'event_name' => $item->event_name,
                'event_status' => $item->event_status,
                'notes' => $item->notes,
            ])->toArray()
            : []
    );
    $canonEvents = array_values($oldCanonEvents);
    while (count($canonEvents) < 3) {
        $canonEvents[] = ['timing' => '', 'event_name' => '', 'event_status' => '', 'notes' => ''];
    }

    $oldTermUsages = old(
        'term_usages',
        isset($work)
            ? $work->termUsages->map(fn ($item) => [
                'term' => $item->term,
                'meaning' => $item->meaning,
                'usage_example' => $item->usage_example,
            ])->toArray()
            : []
    );
    $termUsages = array_values($oldTermUsages);
    while (count($termUsages) < 3) {
        $termUsages[] = ['term' => '', 'meaning' => '', 'usage_example' => ''];
    }
@endphp

@if ($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-red-800">
        <p class="font-bold">入力内容をご確認ください。</p>
        <ul class="mt-2 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="work-editor-form space-y-6">
    <details class="oshi-card" open>
        <summary class="cursor-pointer text-lg font-bold">基本情報</summary>
        <div class="work-basic-grid mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div>
                <label for="title" class="oshi-label">作品名</label>
                <input id="title" type="text" name="title" value="{{ old('title', $work->title ?? '') }}" class="oshi-input" required>
            </div>
            <div>
                <label for="title_kana" class="oshi-label">読み仮名</label>
                <input id="title_kana" type="text" name="title_kana" value="{{ old('title_kana', $work->title_kana ?? '') }}" class="oshi-input">
            </div>
            <div>
                <label for="genre" class="oshi-label">ジャンル</label>
                <input id="genre" type="text" name="genre" value="{{ old('genre', $work->genre ?? '') }}" class="oshi-input">
            </div>
            <div>
                <label for="original_media" class="oshi-label">原作媒体</label>
                <input id="original_media" type="text" name="original_media" value="{{ old('original_media', $work->original_media ?? '') }}" class="oshi-input" placeholder="例：漫画、アニメ、ゲーム、小説">
            </div>
            <div>
                <label for="official_url" class="oshi-label">公式URL</label>
                <input id="official_url" type="url" name="official_url" value="{{ old('official_url', $work->official_url ?? '') }}" class="oshi-input">
            </div>
            <div>
                <label for="guideline_url" class="oshi-label">ガイドラインURL</label>
                <input id="guideline_url" type="url" name="guideline_url" value="{{ old('guideline_url', $work->guideline_url ?? '') }}" class="oshi-input">
            </div>
            <div class="work-form-field-full lg:col-span-2">
                <label for="description" class="oshi-label">説明</label>
                <textarea id="description" name="description" rows="7" class="oshi-input work-description-input">{{ old('description', $work->description ?? '') }}</textarea>
            </div>
            <div class="md:col-span-2">
                <p class="oshi-label">作品タグ</p>
                <div class="work-tag-options rounded-xl border border-gray-200 bg-white p-4">
                    @forelse (($tags ?? collect()) as $tag)
                        <label class="work-tag-option">
                            <input
                                type="checkbox"
                                name="tag_ids[]"
                                value="{{ $tag->id }}"
                                class="work-tag-checkbox"
                                @checked(in_array($tag->id, old('tag_ids', isset($work) ? $work->tags->pluck('id')->toArray() : [])))
                            >
                            <span>{{ $tag->name }}</span>
                        </label>
                    @empty
                        <p class="oshi-muted">登録済みのタグがありません。</p>
                    @endforelse
                </div>
            </div>
            @if (auth()->user()?->isSuperAdmin())
                <div>
                    <label for="status" class="oshi-label">状態</label>
                    <select id="status" name="status" class="oshi-input">
                        <option value="draft" @selected(old('status', $work->status ?? 'draft') === 'draft')>下書き</option>
                        <option value="published" @selected(old('status', $work->status ?? '') === 'published')>公開</option>
                        <option value="private" @selected(old('status', $work->status ?? '') === 'private')>非公開</option>
                    </select>
                </div>
            @endif
        </div>
    </details>

    <details class="oshi-card" open>
        <summary class="cursor-pointer text-lg font-bold">物語の設計</summary>
        <div class="mt-5">
            <label for="timeline_setting" class="oshi-label">時間軸の指定</label>
            <p class="mb-2 text-sm text-gray-500">原作のどの時点を基準にするか記載します。</p>
            <textarea id="timeline_setting" name="timeline_setting" rows="7" class="oshi-input work-long-textarea" placeholder="例：原作〇話以降、〇〇イベント後の時間軸。">{{ old('timeline_setting', $work->timeline_setting ?? '') }}</textarea>
        </div>

        <div class="mt-7">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold">原作の重要イベント年表</h3>
                    <p class="text-sm text-gray-500">触れてよい出来事・まだ起きていない出来事を整理します。最大50件です。</p>
                </div>
                <button type="button" id="add-canon-event" class="oshi-btn oshi-btn-sub">＋ 年表を追加</button>
            </div>

            <div id="canon-event-list" class="space-y-4">
                @foreach ($canonEvents as $index => $event)
                    @include('admin.works._canon_event_row', ['index' => $index, 'event' => $event])
                @endforeach
            </div>
        </div>
    </details>

    @foreach ($workFieldCategories as $category => $fields)
        <details class="oshi-card">
            <summary class="cursor-pointer text-lg font-bold">{{ $category }}</summary>
            <div class="work-category-fields mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
                @foreach ($fields as [$name, $label, $placeholder])
                    <div>
                        <label for="{{ $name }}" class="oshi-label">{{ $label }}</label>
                        <textarea id="{{ $name }}" name="{{ $name }}" rows="7" class="oshi-input work-long-textarea" placeholder="{{ $placeholder }}">{{ old($name, $work->{$name} ?? '') }}</textarea>
                    </div>
                @endforeach
            </div>
        </details>
    @endforeach

    <details class="oshi-card">
        <summary class="cursor-pointer text-lg font-bold">用語</summary>
        <div class="mt-5">
            <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold">用語の使用例</h3>
                    <p class="text-sm text-gray-500">単語の意味だけでなく、作中でどう使われるかも登録します。最大50件です。</p>
                </div>
                <button type="button" id="add-term-usage" class="oshi-btn oshi-btn-sub">＋ 用語を追加</button>
            </div>

            <div id="term-usage-list" class="space-y-4">
                @foreach ($termUsages as $index => $term)
                    @include('admin.works._term_usage_row', ['index' => $index, 'term' => $term])
                @endforeach
            </div>
        </div>
    </details>

    <div class="work-form-actions flex flex-wrap justify-end gap-3 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <a href="{{ isset($work) ? route('admin.works.show', $work) : route('admin.works.index') }}" class="oshi-btn oshi-btn-sub">キャンセル</a>
        <button type="submit" class="oshi-btn oshi-btn-main">保存する</button>
    </div>
</div>

<template id="canon-event-template">
    @include('admin.works._canon_event_row', [
        'index' => '__INDEX__',
        'event' => ['timing' => '', 'event_name' => '', 'event_status' => '', 'notes' => ''],
    ])
</template>

<template id="term-usage-template">
    @include('admin.works._term_usage_row', [
        'index' => '__INDEX__',
        'term' => ['term' => '', 'meaning' => '', 'usage_example' => ''],
    ])
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function setupRepeater(config) {
        const list = document.getElementById(config.listId);
        const addButton = document.getElementById(config.addButtonId);
        const template = document.getElementById(config.templateId);

        if (!list || !addButton || !template) return;

        function updateState() {
            const rows = list.querySelectorAll(config.rowSelector);
            addButton.disabled = rows.length >= 50;
            addButton.textContent = rows.length >= 50 ? '最大50件です' : config.buttonText;
        }

        addButton.addEventListener('click', function () {
            const count = list.querySelectorAll(config.rowSelector).length;
            if (count >= 50) return;

            const wrapper = document.createElement('div');
            wrapper.innerHTML = template.innerHTML.replaceAll('__INDEX__', String(Date.now()));
            const row = wrapper.firstElementChild;
            list.appendChild(row);
            row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            updateState();
        });

        list.addEventListener('click', function (event) {
            const removeButton = event.target.closest('[data-remove-row]');
            if (!removeButton) return;

            const row = removeButton.closest(config.rowSelector);
            if (row) row.remove();
            updateState();
        });

        updateState();
    }

    setupRepeater({
        listId: 'canon-event-list',
        addButtonId: 'add-canon-event',
        templateId: 'canon-event-template',
        rowSelector: '[data-canon-event-row]',
        buttonText: '＋ 年表を追加'
    });

    setupRepeater({
        listId: 'term-usage-list',
        addButtonId: 'add-term-usage',
        templateId: 'term-usage-template',
        rowSelector: '[data-term-usage-row]',
        buttonText: '＋ 用語を追加'
    });
});
</script>
