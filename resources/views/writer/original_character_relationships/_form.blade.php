@php
    $relationship = $relationship ?? $originalCharacterRelationship ?? null;

    $oldValue = function (string $key, $default = '') use ($relationship) {
        return old($key, $relationship?->{$key} ?? $default);
    };

    $fromRef = old('from_character_ref');
    $toRef = old('to_character_ref');

    if (! $fromRef && $relationship) { elseif ($relationship->from_original_character_id) {
            $fromRef = 'original:' . $relationship->from_original_character_id;
        }
    }

    if (! $toRef && $relationship) { elseif ($relationship->to_original_character_id) {
            $toRef = 'original:' . $relationship->to_original_character_id;
        }
    }

    $status = old('status', $relationship?->status ?? 'active');
    $characters = $characters ?? collect();

    $timelineItems = old('timeline_items', $relationship?->timeline_items ?? []);

    if (! is_array($timelineItems)) {
        $timelineItems = [];
    }

    $timelineItems = array_values($timelineItems);

    $filledTimelineCount = collect($timelineItems)
        ->filter(function ($item) {
            return is_array($item)
                && (
                    trim((string)($item['period'] ?? '')) !== ''
                    || trim((string)($item['content'] ?? '')) !== ''
                );
        })
        ->count();

    /*
     * 新規登録時：必ず3行だけ表示
     * 編集時：保存済みが4件以上あれば保存済み件数分を表示
     */
    $initialTimelineCount = min(10, max(3, $filledTimelineCount));

    for ($i = count($timelineItems); $i < $initialTimelineCount; $i++) {
        $timelineItems[$i] = [
            'period' => '',
            'content' => '',
        ];
    }

    $timelineItems = array_slice($timelineItems, 0, $initialTimelineCount);
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
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">STEP 1</p>
            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">関係を作るキャラクターを選ぶ</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                「誰から誰へ」の関係性かを選びます。オリジナルキャラクターと、Oshi-Wikiに登録済みのオリジナルキャラクターを組み合わせられます。
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="from_character_ref">From：関係元キャラクター <span class="text-red-500">必須</span></label>
                <select id="from_character_ref" name="from_character_ref" required>
                    <option value="">選択してください</option>

                    <optgroup label="オリジナルキャラクター">
                        @foreach ($characters as $character)
                            <option value="original:{{ $character->id }}" @selected($fromRef === 'original:' . $character->id)>
                                {{ $character->name }}
                            </option>
                        @endforeach
                    </optgroup>

                    <optgroup label="オリジナルキャラクター">
                    </optgroup>
                </select>
                <p class="mt-2 text-xs font-bold leading-6 text-[#A0AEC0]">
                    呼び方・印象を向ける側のキャラクターです。
                </p>
            </div>

            <div>
                <label for="to_character_ref">To：関係先キャラクター <span class="text-red-500">必須</span></label>
                <select id="to_character_ref" name="to_character_ref" required>
                    <option value="">選択してください</option>

                    <optgroup label="オリジナルキャラクター">
                        @foreach ($characters as $character)
                            <option value="original:{{ $character->id }}" @selected($toRef === 'original:' . $character->id)>
                                {{ $character->name }}
                            </option>
                        @endforeach
                    </optgroup>

                    <optgroup label="オリジナルキャラクター">
                    </optgroup>
                </select>
                <p class="mt-2 text-xs font-bold leading-6 text-[#A0AEC0]">
                    呼び方・印象を向けられる側のキャラクターです。
                </p>
            </div>
        </div>

        <div class="mt-5 rounded-2xl bg-[#F7FAFC] p-5">
            <p class="text-sm font-bold text-[#2D3748]">例</p>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                FromがキャラクターA、ToがキャラクターBの場合、「キャラクターAがキャラクターBをどう呼ぶか」「どう思っているか」を登録します。
            </p>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">STEP 2</p>
            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">呼び方・関係性</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                関係元キャラクターが、関係先キャラクターをどう呼ぶか、どんな関係かを登録します。
            </p>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="called_name">呼び方</label>
                <input id="called_name"
                       type="text"
                       name="called_name"
                       value="{{ $oldValue('called_name') }}"
                       placeholder="例：名前、苗字、先生、先輩、兄さん">
            </div>

            <div>
                <label for="relationship_type">関係性</label>
                <input id="relationship_type"
                       type="text"
                       name="relationship_type"
                       value="{{ $oldValue('relationship_type') }}"
                       placeholder="例：友人、幼なじみ、師弟、家族、敵対関係">
            </div>

            <div class="md:col-span-2">
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
            <p class="text-sm font-bold text-[#A0AEC0]">STEP 3</p>
            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">印象・備考</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                印象や気持ち、プロンプトに反映したい補足を入力します。時系列の出来事は次の年表データに入力します。
            </p>
        </div>

        <div class="space-y-5">
            <div>
                <label for="impression">印象・気持ち</label>
                <textarea id="impression"
                          name="impression"
                          placeholder="例：信頼している。気になる存在。苦手意識があるが放っておけない。">{{ $oldValue('impression') }}</textarea>
            </div>

            <div>
                <label for="notes">備考</label>
                <textarea id="notes"
                          name="notes"
                          placeholder="その他、関係性について補足したい内容">{{ $oldValue('notes') }}</textarea>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="mb-6">
            <p class="text-sm font-bold text-[#A0AEC0]">STEP 4</p>
            <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">年表データ</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                関係性に関わる出来事を、時期と内容のセットで登録できます。最初は3行表示し、「行を追加」で最大10行まで増やせます。
            </p>
        </div>

        <div class="space-y-4" id="relationship-timeline-list" data-current-count="{{ $initialTimelineCount }}" data-max-count="10">
            <div class="grid gap-3 md:grid-cols-[220px_1fr_90px]">
                <p class="text-sm font-bold text-[#A0AEC0]">時期</p>
                <p class="text-sm font-bold text-[#A0AEC0]">内容</p>
                <p class="text-sm font-bold text-[#A0AEC0]">操作</p>
            </div>

            @foreach ($timelineItems as $index => $item)
                <div class="relationship-timeline-row grid gap-3 rounded-2xl bg-[#F7FAFC] p-4 md:grid-cols-[220px_1fr_90px]"
                     data-timeline-index="{{ $index }}">
                    <div>
                        <label for="timeline_period_{{ $index }}" class="sr-only">
                            年表 {{ $index + 1 }} 時期
                        </label>
                        <input id="timeline_period_{{ $index }}"
                               type="text"
                               name="timeline_items[{{ $index }}][period]"
                               value="{{ $item['period'] ?? '' }}"
                               placeholder="{{ $index === 0 ? '例：5歳の頃' : '時期' }}">
                    </div>

                    <div>
                        <label for="timeline_content_{{ $index }}" class="sr-only">
                            年表 {{ $index + 1 }} 内容
                        </label>
                        <input id="timeline_content_{{ $index }}"
                               type="text"
                               name="timeline_items[{{ $index }}][content]"
                               value="{{ $item['content'] ?? '' }}"
                               placeholder="{{ $index === 0 ? '例：出会う' : '内容' }}">
                    </div>

                    <div class="flex items-center md:justify-end">
                        <button type="button"
                                class="relationship-timeline-clear rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3 text-sm font-bold text-[#4A5568] hover:bg-[#F7FAFC]">
                            クリア
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <p id="relationship-timeline-count" class="text-sm font-bold text-[#A0AEC0]">
                {{ $initialTimelineCount }} / 10 行表示中
            </p>

            <button type="button"
                    id="relationship-timeline-add"
                    class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
                行を追加
            </button>
        </div>

        <div class="mt-5 rounded-2xl bg-[#FFF1F5] p-5">
            <p class="text-sm font-bold text-[#2D3748]">入力例</p>
            <ul class="mt-3 space-y-2 text-sm font-bold leading-7 text-[#4A5568]">
                <li>・時期：5歳の頃　内容：出会う</li>
                <li>・時期：中学生の頃　内容：一度離れる</li>
                <li>・時期：物語開始時　内容：再会する</li>
            </ul>
        </div>
    </section>

    <div class="flex flex-col gap-3 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
        <p class="text-sm font-bold text-[#718096]">
            登録した関係性は、プロンプト作成時に選択キャラクター同士の情報として反映されます。
        </p>

        <div class="flex flex-col gap-3 md:flex-row">
            <a href="{{ route('writer.original-character-relationships.index') }}"
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const list = document.getElementById('relationship-timeline-list');
        const addButton = document.getElementById('relationship-timeline-add');
        const countLabel = document.getElementById('relationship-timeline-count');

        if (!list || !addButton) {
            return;
        }

        const maxCount = Number(list.dataset.maxCount || 10);
        let currentCount = Number(list.dataset.currentCount || 3);

        function updateUi() {
            if (countLabel) {
                countLabel.textContent = `${currentCount} / ${maxCount} 行表示中`;
            }

            const disabled = currentCount >= maxCount;
            addButton.disabled = disabled;
            addButton.classList.toggle('opacity-50', disabled);
            addButton.classList.toggle('cursor-not-allowed', disabled);
        }

        function attachClearEvent(row) {
            const clearButton = row.querySelector('.relationship-timeline-clear');

            clearButton?.addEventListener('click', function () {
                row.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                });
            });
        }

        function createTimelineRow(index) {
            const row = document.createElement('div');
            row.className = 'relationship-timeline-row grid gap-3 rounded-2xl bg-[#F7FAFC] p-4 md:grid-cols-[220px_1fr_90px]';
            row.dataset.timelineIndex = String(index);

            row.innerHTML = `
                <div>
                    <label for="timeline_period_${index}" class="sr-only">
                        年表 ${index + 1} 時期
                    </label>
                    <input id="timeline_period_${index}"
                           type="text"
                           name="timeline_items[${index}][period]"
                           value=""
                           placeholder="時期">
                </div>

                <div>
                    <label for="timeline_content_${index}" class="sr-only">
                        年表 ${index + 1} 内容
                    </label>
                    <input id="timeline_content_${index}"
                           type="text"
                           name="timeline_items[${index}][content]"
                           value=""
                           placeholder="内容">
                </div>

                <div class="flex items-center md:justify-end">
                    <button type="button"
                            class="relationship-timeline-clear rounded-2xl border border-[#CBD5E0] bg-white px-4 py-3 text-sm font-bold text-[#4A5568] hover:bg-[#F7FAFC]">
                        クリア
                    </button>
                </div>
            `;

            attachClearEvent(row);

            return row;
        }

        document.querySelectorAll('.relationship-timeline-row').forEach((row) => {
            attachClearEvent(row);
        });

        addButton.addEventListener('click', function () {
            if (currentCount >= maxCount) {
                updateUi();
                return;
            }

            const row = createTimelineRow(currentCount);
            list.appendChild(row);

            currentCount += 1;

            const firstInput = row.querySelector('input');
            if (firstInput) {
                firstInput.focus();
            }

            updateUi();
        });

        updateUi();
    });
</script>
