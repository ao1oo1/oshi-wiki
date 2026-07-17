@php
    $current = $section ?? null;

    $oldEvents = old(
        'events',
        $current
            ? $current->events->map(fn ($event) => [
                'event_number' => $event->event_number,
                'title' => $event->title,
                'timing' => $event->timing,
                'summary' => $event->summary,
                'location' => $event->location,
                'outcome' => $event->outcome,
                'spoiler_level' => $event->spoiler_level,
                'notes' => $event->notes,
                'sort_order' => $event->sort_order,
            ])->toArray()
            : []
    );

    $eventRows = array_values($oldEvents);
    while (count($eventRows) < 3) {
        $eventRows[] = [
            'event_number' => '',
            'title' => '',
            'timing' => '',
            'summary' => '',
            'location' => '',
            'outcome' => '',
            'spoiler_level' => 'none',
            'notes' => '',
            'sort_order' => '',
        ];
    }

    $selectedCharacterData = collect(
        old(
            'section_characters',
            $current
                ? $current->characters->map(fn ($character) => [
                    'character_id' => $character->id,
                    'selected' => 1,
                    'appearance_type' => $character->pivot->appearance_type,
                    'age_at_section' => $character->pivot->age_at_section,
                    'school_grade_at_section' => $character->pivot->school_grade_at_section,
                    'class_at_section' => $character->pivot->class_at_section,
                    'affiliation_at_section' => $character->pivot->affiliation_at_section,
                    'position_at_section' => $character->pivot->position_at_section,
                    'character_state' => $character->pivot->character_state,
                    'first_appearance' => $character->pivot->first_appearance,
                    'notes' => $character->pivot->notes,
                    'sort_order' => $character->pivot->sort_order,
                ])->toArray()
                : []
        )
    )->keyBy(fn ($item) => (int) ($item['character_id'] ?? 0));
@endphp

@if ($errors->any())
    <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">
        <ul class="list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="space-y-6">
    <details class="oshi-card" open>
        <summary class="cursor-pointer text-lg font-bold">基本情報</summary>

        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <label class="oshi-label" for="section_type">種別</label>
                <select class="oshi-input" id="section_type" name="section_type" required>
                    @foreach ($sectionTypes as $value => $label)
                        <option value="{{ $value }}" @selected(old('section_type', $current->section_type ?? 'chapter') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="oshi-label" for="parent_section_id">親となる編・部</label>
                <select class="oshi-input" id="parent_section_id" name="parent_section_id">
                    <option value="">親なし</option>
                    @foreach ($parentSectionOptions as $option)
                        <option value="{{ $option->id }}" @selected((int) old('parent_section_id', $current->parent_section_id ?? 0) === (int) $option->id)>
                            {{ $option->title }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    階層は「編・部 ＞ 章・話」の2階層までです。
                </p>
            </div>

            <div>
                <label class="oshi-label" for="section_number">番号</label>
                <input class="oshi-input" id="section_number" type="number" min="0" max="9999" name="section_number" value="{{ old('section_number', $current->section_number ?? '') }}">
            </div>

            <div>
                <label class="oshi-label" for="sort_order">表示順</label>
                <input class="oshi-input" id="sort_order" type="number" min="0" max="9999" name="sort_order" value="{{ old('sort_order', $current->sort_order ?? 0) }}">
            </div>

            <div class="md:col-span-2">
                <label class="oshi-label" for="title">章・編名</label>
                <input class="oshi-input" id="title" type="text" name="title" required value="{{ old('title', $current->title ?? '') }}" placeholder="例：第1章 真紅の暴君">
            </div>

            <div>
                <label class="oshi-label" for="title_kana">読み仮名</label>
                <input class="oshi-input" id="title_kana" type="text" name="title_kana" value="{{ old('title_kana', $current->title_kana ?? '') }}">
            </div>

            <div>
                <label class="oshi-label" for="short_label">短い表示名</label>
                <input class="oshi-input" id="short_label" type="text" name="short_label" value="{{ old('short_label', $current->short_label ?? '') }}" placeholder="例：1章">
            </div>

            <div>
                <label class="oshi-label" for="spoiler_level">ネタバレ区分</label>
                <select class="oshi-input" id="spoiler_level" name="spoiler_level">
                    @foreach ($spoilerLevels as $value => $label)
                        <option value="{{ $value }}" @selected(old('spoiler_level', $current->spoiler_level ?? 'none') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="oshi-label" for="status">状態</label>
                <select class="oshi-input" id="status" name="status">
                    <option value="draft" @selected(old('status', $current->status ?? 'draft') === 'draft')>下書き</option>
                    <option value="published" @selected(old('status', $current->status ?? 'draft') === 'published')>公開</option>
                    <option value="private" @selected(old('status', $current->status ?? 'draft') === 'private')>非公開</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="oshi-label" for="synopsis">章・編の概要</label>
                <textarea class="oshi-input min-h-40" id="synopsis" name="synopsis">{{ old('synopsis', $current->synopsis ?? '') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="oshi-label" for="cumulative_settings">
                    この章までに登場する設定
                </label>
                <textarea class="oshi-input min-h-56" id="cumulative_settings" name="cumulative_settings">{{ old('cumulative_settings', $current->cumulative_settings ?? '') }}</textarea>
            </div>

            <div class="md:col-span-2">
                <label class="oshi-label" for="notes">備考</label>
                <textarea class="oshi-input min-h-32" id="notes" name="notes">{{ old('notes', $current->notes ?? '') }}</textarea>
            </div>
        </div>
    </details>

    <details class="oshi-card" open>
        <summary class="cursor-pointer text-lg font-bold">
            物語詳細（最大{{ $eventLimit }}件）
        </summary>

        <p class="mt-3 text-sm text-gray-600">
            タイトルが入力された行だけ保存されます。
        </p>

        <div class="mt-5 space-y-4">
            @foreach ($eventRows as $index => $event)
                <details class="rounded-xl border border-gray-200 p-4" @if ($index < 3) open @endif>
                    <summary class="cursor-pointer font-bold">
                        物語詳細 {{ $index + 1 }}
                    </summary>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="oshi-label">番号</label>
                            <input class="oshi-input" type="number" name="events[{{ $index }}][event_number]" value="{{ $event['event_number'] ?? '' }}">
                        </div>
                        <div>
                            <label class="oshi-label">表示順</label>
                            <input class="oshi-input" type="number" name="events[{{ $index }}][sort_order]" value="{{ $event['sort_order'] ?? ($index + 1) }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="oshi-label">タイトル</label>
                            <input class="oshi-input" type="text" name="events[{{ $index }}][title]" value="{{ $event['title'] ?? '' }}">
                        </div>
                        <div>
                            <label class="oshi-label">タイミング</label>
                            <input class="oshi-input" type="text" name="events[{{ $index }}][timing]" value="{{ $event['timing'] ?? '' }}">
                        </div>
                        <div>
                            <label class="oshi-label">場所</label>
                            <input class="oshi-input" type="text" name="events[{{ $index }}][location]" value="{{ $event['location'] ?? '' }}">
                        </div>
                        <div class="md:col-span-2">
                            <label class="oshi-label">詳細</label>
                            <textarea class="oshi-input min-h-32" name="events[{{ $index }}][summary]">{{ $event['summary'] ?? '' }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="oshi-label">結果</label>
                            <textarea class="oshi-input min-h-28" name="events[{{ $index }}][outcome]">{{ $event['outcome'] ?? '' }}</textarea>
                        </div>
                        <div>
                            <label class="oshi-label">ネタバレ区分</label>
                            <select class="oshi-input" name="events[{{ $index }}][spoiler_level]">
                                @foreach ($spoilerLevels as $value => $label)
                                    <option value="{{ $value }}" @selected(($event['spoiler_level'] ?? 'none') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="oshi-label">備考</label>
                            <textarea class="oshi-input min-h-24" name="events[{{ $index }}][notes]">{{ $event['notes'] ?? '' }}</textarea>
                        </div>
                    </div>
                </details>
            @endforeach
        </div>
    </details>

    <details class="oshi-card" open>
        <summary class="cursor-pointer text-lg font-bold">
            登場キャラクター
        </summary>

        <p class="mt-3 text-sm text-gray-600">
            この作品に主作品・追加作品として紐づくキャラクターから選択します。
        </p>

        <div class="mt-5 space-y-4">
            @forelse ($characters as $index => $character)
                @php
                    $saved = $selectedCharacterData->get($character->id, []);
                    $selected = ! empty($saved['selected']);
                @endphp

                <details class="rounded-xl border border-gray-200 p-4" @if ($selected) open @endif>
                    <summary class="cursor-pointer font-bold">
                        {{ $character->name }}
                    </summary>

                    <input type="hidden" name="section_characters[{{ $index }}][character_id]" value="{{ $character->id }}">

                    <div class="mt-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="section_characters[{{ $index }}][selected]" value="1" @checked($selected)>
                            この章・編に登場する
                        </label>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="oshi-label">登場区分</label>
                            <select class="oshi-input" name="section_characters[{{ $index }}][appearance_type]">
                                @foreach ([
                                    'main' => '主要登場',
                                    'appears' => '登場',
                                    'flashback' => '回想',
                                    'name_only' => '名前のみ',
                                    'other' => 'その他',
                                ] as $value => $label)
                                    <option value="{{ $value }}" @selected(($saved['appearance_type'] ?? 'appears') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="oshi-label">表示順</label>
                            <input class="oshi-input" type="number" name="section_characters[{{ $index }}][sort_order]" value="{{ $saved['sort_order'] ?? ($index + 1) }}">
                        </div>
                        <div>
                            <label class="oshi-label">当時の年齢</label>
                            <input class="oshi-input" type="text" name="section_characters[{{ $index }}][age_at_section]" value="{{ $saved['age_at_section'] ?? $character->age }}">
                        </div>
                        <div>
                            <label class="oshi-label">当時の学年</label>
                            <input class="oshi-input" type="text" name="section_characters[{{ $index }}][school_grade_at_section]" value="{{ $saved['school_grade_at_section'] ?? $character->school_grade_class }}">
                        </div>
                        <div>
                            <label class="oshi-label">当時のクラス</label>
                            <input class="oshi-input" type="text" name="section_characters[{{ $index }}][class_at_section]" value="{{ $saved['class_at_section'] ?? '' }}">
                        </div>
                        <div>
                            <label class="oshi-label">当時の所属</label>
                            <input class="oshi-input" type="text" name="section_characters[{{ $index }}][affiliation_at_section]" value="{{ $saved['affiliation_at_section'] ?? $character->affiliation }}">
                        </div>
                        <div>
                            <label class="oshi-label">当時の役職</label>
                            <input class="oshi-input" type="text" name="section_characters[{{ $index }}][position_at_section]" value="{{ $saved['position_at_section'] ?? $character->occupation_position }}">
                        </div>
                        <div>
                            <label class="flex items-center gap-2 pt-8">
                                <input type="checkbox" name="section_characters[{{ $index }}][first_appearance]" value="1" @checked(! empty($saved['first_appearance']))>
                                初登場
                            </label>
                        </div>
                        <div class="md:col-span-2">
                            <label class="oshi-label">当時の状態・立場</label>
                            <textarea class="oshi-input min-h-24" name="section_characters[{{ $index }}][character_state]">{{ $saved['character_state'] ?? '' }}</textarea>
                        </div>
                        <div class="md:col-span-2">
                            <label class="oshi-label">備考</label>
                            <textarea class="oshi-input min-h-24" name="section_characters[{{ $index }}][notes]">{{ $saved['notes'] ?? '' }}</textarea>
                        </div>
                    </div>
                </details>
            @empty
                <p class="oshi-muted">
                    この作品に紐づくキャラクターがいません。
                </p>
            @endforelse
        </div>
    </details>
</div>
