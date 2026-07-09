@php
    $relationship = $relationship ?? $originalCharacterRelationship ?? null;

    $oldValue = function (string $key, $default = '') use ($relationship) {
        return old($key, $relationship?->{$key} ?? $default);
    };

    $fromRef = old('from_character_ref');
    $toRef = old('to_character_ref');

    if (! $fromRef && $relationship) {
        if (($relationship->from_character_source ?? null) === 'v1_character' && $relationship->from_character_id) {
            $fromRef = 'v1_character:' . $relationship->from_character_id;
        } elseif ($relationship->from_original_character_id) {
            $fromRef = 'original:' . $relationship->from_original_character_id;
        }
    }

    if (! $toRef && $relationship) {
        if (($relationship->to_character_source ?? null) === 'v1_character' && $relationship->to_character_id) {
            $toRef = 'v1_character:' . $relationship->to_character_id;
        } elseif ($relationship->to_original_character_id) {
            $toRef = 'original:' . $relationship->to_original_character_id;
        }
    }

    $status = old('status', $relationship?->status ?? 'active');
    $characters = $characters ?? collect();
    $officialCharacters = $officialCharacters ?? collect();
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
                「誰から誰へ」の関係性かを選びます。オリジナルキャラクターと、Oshi-Wikiに登録済みの作品キャラクターを組み合わせられます。
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

                    <optgroup label="作品キャラクター">
                        @foreach ($officialCharacters as $character)
                            <option value="v1_character:{{ $character->id }}" @selected($fromRef === 'v1_character:' . $character->id)>
                                {{ $character->name }}
                            </option>
                        @endforeach
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

                    <optgroup label="作品キャラクター">
                        @foreach ($officialCharacters as $character)
                            <option value="v1_character:{{ $character->id }}" @selected($toRef === 'v1_character:' . $character->id)>
                                {{ $character->name }}
                            </option>
                        @endforeach
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
                印象や気持ち、プロンプトに反映したい補足を入力します。
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
