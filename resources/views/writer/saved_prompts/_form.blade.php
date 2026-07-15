@php
    $prompt = $prompt ?? $savedPrompt ?? null;

    $oldValue = function (string $key, $default = '') use ($prompt) {
        return old($key, $prompt?->{$key} ?? $default);
    };

    $characters = $characters
        ?? $originalCharacters
        ?? $characterItems
        ?? collect();

    $publishedWorks = $publishedWorks ?? collect();

    $selectedCharacterRefs = old(
        'selected_character_refs',
        $prompt?->selected_character_refs ?? []
    );

    if (! is_array($selectedCharacterRefs)) {
        $selectedCharacterRefs = [];
    }

    $defaultWorkRef = (
        $prompt?->work_source
            === \App\Models\SavedPrompt::WORK_SOURCE_V1
        && $prompt?->work_id
    )
        ? 'work:' . $prompt->work_id
        : 'original';

    $workRef = old('work_ref', $defaultWorkRef);

    $selectedWorkId = str_starts_with($workRef, 'work:')
        ? (int) str_replace('work:', '', $workRef)
        : null;

    $writingStyle = old('writing_style', $prompt?->writing_style ?? '');
    $genre = old('genre', $prompt?->genre ?? '');
    $status = old('status', $prompt?->status ?? 'active');

    $writingStyleLabels = \App\Models\SavedPrompt::writingStyleLabels();
    $genreLabels = \App\Models\SavedPrompt::genreLabels();

    $includeTimeline = (bool) old(
        'include_relationship_timeline',
        $prompt?->include_relationship_timeline ?? false
    );

    $includeWorkWorldbuilding = (bool) old(
        'include_work_worldbuilding',
        $prompt?->include_work_worldbuilding ?? false
    );

    $workWorldbuildingCategoryLabels =
        \App\Models\SavedPrompt::workWorldbuildingCategoryLabels();

    $selectedWorkWorldbuildingCategories = old(
        'selected_work_worldbuilding_categories',
        $prompt?->selected_work_worldbuilding_categories ?? []
    );

    if (! is_array($selectedWorkWorldbuildingCategories)) {
        $selectedWorkWorldbuildingCategories = [];
    }

    $storyAnalyses = $storyAnalyses ?? collect();

    $selectedStoryAnalysisIds = old(
        'selected_story_analysis_ids',
        $prompt?->selected_story_analysis_ids ?? []
    );

    if (! is_array($selectedStoryAnalysisIds)) {
        $selectedStoryAnalysisIds = [];
    }

    $selectedStoryAnalysisIds = array_map(
        'intval',
        $selectedStoryAnalysisIds
    );
@endphp

@if ($errors->any())
    <div class="rounded-2xl border border-red-200 bg-red-50 p-5 text-sm font-bold text-red-600">
        <p>入力内容を確認してください。</p>
        <ul class="mt-3 list-disc space-y-1 pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<input type="hidden" name="status" id="prompt-status-input" value="{{ $status }}">

<section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="mb-6">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 1</p>
        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">基本情報</h2>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            プロンプトの管理名と用途を設定します。
        </p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="title">タイトル <span class="text-red-500">必須</span></label>
            <input id="title"
                   type="text"
                   name="title"
                   value="{{ $oldValue('title') }}"
                   placeholder="例：日常シーン用プロンプト"
                   required>
        </div>
        <div>
            <label for="work_ref">
                原作作品 <span class="text-red-500">必須</span>
            </label>

            <select
                id="work_ref"
                name="work_ref"
                required
            >
                <option
                    value="original"
                    @selected($workRef === 'original')
                >
                    オリジナル作品
                </option>

                @foreach ($publishedWorks as $work)
                    <option
                        value="work:{{ $work->id }}"
                        @selected($workRef === 'work:' . $work->id)
                    >
                        {{ $work->title }}
                    </option>
                @endforeach
            </select>

            <p class="mt-2 text-xs font-bold leading-6 text-[#A0AEC0]">
                原作作品を選ぶと、その作品に登録されている公開キャラクターを選択できます。
            </p>
        </div>

        <div class="md:col-span-2">
            <label for="purpose">用途・目的</label>
            <textarea id="purpose"
                      name="purpose"
                      placeholder="例：キャラクター同士の日常会話を書くためのプロンプト">{{ $oldValue('purpose') }}</textarea>
        </div>
    </div>
</section>

<section
    id="work-worldbuilding-section"
    class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8"
    style="display: {{ $selectedWorkId ? 'block' : 'none' }};"
>
    <div class="mb-6">
        <p class="text-sm font-bold text-[#A0AEC0]">OPTION</p>
        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
            作品設定
        </h2>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            選択した原作作品に登録されている設定のうち、必要なカテゴリだけをプロンプトへ反映します。
            空欄の項目は自動的に除外されます。
        </p>
    </div>

    <div class="rounded-2xl bg-[#FFF1F5] p-5">
        <label class="flex items-start gap-3">
            <input
                id="include_work_worldbuilding"
                type="checkbox"
                name="include_work_worldbuilding"
                value="1"
                class="mt-1"
                @checked($includeWorkWorldbuilding && $selectedWorkId)
            >
            <span>
                <span class="block font-bold text-[#2D3748]">
                    作品設定をプロンプトに反映する
                </span>
                <span class="mt-1 block text-sm font-bold leading-7 text-[#718096]">
                    全文ではなく、下で選択したカテゴリだけを反映します。
                </span>
            </span>
        </label>
    </div>

    <div
        id="work-worldbuilding-categories"
        class="mt-5 grid gap-3 md:grid-cols-2"
        style="display: {{ $includeWorkWorldbuilding && $selectedWorkId ? 'grid' : 'none' }};"
    >
        @foreach ($workWorldbuildingCategoryLabels as $key => $label)
            <label class="flex items-start gap-3 rounded-2xl border border-[#E2E8F0] bg-[#F7FAFC] p-4">
                <input
                    type="checkbox"
                    name="selected_work_worldbuilding_categories[]"
                    value="{{ $key }}"
                    class="work-worldbuilding-category mt-1"
                    @checked(
                        $selectedWorkId
                        && in_array(
                            $key,
                            $selectedWorkWorldbuildingCategories,
                            true
                        )
                    )
                >
                <span class="font-bold text-[#2D3748]">{{ $label }}</span>
            </label>
        @endforeach
    </div>

    <p class="mt-4 text-xs font-bold leading-6 text-[#A0AEC0]">
        原作作品を変更すると、作品設定カテゴリの選択は一度解除されます。
    </p>
</section>

<section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="mb-6">
        <p class="text-sm font-bold text-[#A0AEC0]">
            STEP 2
        </p>

        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
            登場人物
        </h2>

        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            自分のオリジナルキャラクターと、STEP1で選択した原作作品の公開キャラクターを選択できます。
        </p>
    </div>

    <div class="grid gap-6 xl:grid-cols-2">
        <div class="rounded-3xl bg-[#F7FAFC] p-5">
            <h3 class="font-bold text-[#2D3748]">
                オリジナルキャラクター
            </h3>

            <p class="mt-2 text-sm font-bold leading-7 text-[#A0AEC0]">
                自分で登録したキャラクターです。原作作品の選択に関係なく使用できます。
            </p>

            <div class="mt-4 max-h-[420px] space-y-3 overflow-y-auto pr-2">
                @forelse ($characters as $character)
                    @php
                        $ref = 'original:' . $character->id;
                    @endphp

                    <label class="flex items-start gap-3 rounded-2xl bg-white p-4">
                        <input
                            type="checkbox"
                            name="selected_character_refs[]"
                            value="{{ $ref }}"
                            class="mt-1"
                            @checked(in_array(
                                $ref,
                                $selectedCharacterRefs,
                                true
                            ))
                        >

                        <span>
                            <span class="block font-bold text-[#2D3748]">
                                {{ $character->name }}
                            </span>

                            @if ($character->affiliation || $character->age)
                                <span class="mt-1 block text-xs font-bold text-[#A0AEC0]">
                                    {{
                                        collect([
                                            $character->age,
                                            $character->affiliation,
                                        ])->filter()->implode(' / ')
                                    }}
                                </span>
                            @endif
                        </span>
                    </label>
                @empty
                    <div class="rounded-2xl bg-white p-5 text-sm font-bold text-[#A0AEC0]">
                        オリジナルキャラクターがまだ登録されていません。
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-3xl bg-[#FFF7FA] p-5">
            <h3 class="font-bold text-[#2D3748]">
                原作作品の登録済みキャラクター
            </h3>

            <p class="mt-2 text-sm font-bold leading-7 text-[#A0AEC0]">
                STEP1で選択した作品に紐づく公開キャラクターだけを表示します。
            </p>

            <div
                id="v1-character-empty-message"
                class="mt-4 rounded-2xl bg-white p-5 text-sm font-bold leading-7 text-[#A0AEC0]"
                style="display: {{ $selectedWorkId ? 'none' : 'block' }};"
            >
                原作作品を選択するとキャラクターが表示されます。
            </div>

            <div
                id="v1-character-no-results-message"
                class="mt-4 rounded-2xl bg-white p-5 text-sm font-bold leading-7 text-[#A0AEC0]"
                style="display:none;"
            >
                この作品には選択できる公開キャラクターがありません。
            </div>

            <div class="mt-4 max-h-[420px] space-y-3 overflow-y-auto pr-2">
                @foreach ($publishedWorks as $work)
                    @foreach ($work->characters as $character)
                        @php
                            $ref = 'v1:' . $character->id;
                            $isVisible =
                                $selectedWorkId === (int) $work->id;
                        @endphp

                        <div
                            class="v1-character-option"
                            data-work-id="{{ $work->id }}"
                            @if (! $isVisible) hidden @endif
                        >
                            <label class="flex items-start gap-3 rounded-2xl bg-white p-4">
                                <input
                                    type="checkbox"
                                    name="selected_character_refs[]"
                                    value="{{ $ref }}"
                                    class="v1-character-checkbox mt-1"
                                    data-work-id="{{ $work->id }}"
                                    @checked(
                                        $isVisible
                                        && in_array(
                                            $ref,
                                            $selectedCharacterRefs,
                                            true
                                        )
                                    )
                                    @disabled(! $isVisible)
                                >

                                <span>
                                    <span class="block font-bold text-[#2D3748]">
                                        {{ $character->name }}
                                    </span>

                                    <span class="mt-1 block text-xs font-bold text-[#A0AEC0]">
                                        {{ $work->title }}

                                        @if (
                                            $character->affiliation
                                            || $character->age
                                        )
                                            /
                                            {{
                                                collect([
                                                    $character->age,
                                                    $character->affiliation,
                                                ])
                                                    ->filter()
                                                    ->implode(' / ')
                                            }}
                                        @endif
                                    </span>
                                </span>
                            </label>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>

    {{-- V3_RELATIONSHIP_REGISTRATION_GUIDE --}}
    <div class="mt-6 mb-4 rounded-2xl border border-[#E2E8F0] bg-white px-5 py-4">
        <p class="text-sm font-bold leading-7 text-[#718096]">
            ※
            <a
                href="{{ route('writer.original-character-relationships.index') }}"
                class="text-[#2D3748] underline decoration-[#FED7E2] decoration-2 underline-offset-4 hover:opacity-80"
            >
                関係性登録
            </a>
            を行うと、登場人物同士のつながりもプロンプトに反映され、より精度の高いプロンプトを作成できます。あわせて登録するのがおすすめです。
        </p>
    </div>
    {{-- /V3_RELATIONSHIP_REGISTRATION_GUIDE --}}

    <div class="mt-6 rounded-2xl bg-[#FFF1F5] p-5">
        <label class="flex items-start gap-3">
            <input
                type="checkbox"
                name="include_relationship_timeline"
                value="1"
                class="mt-1"
                @checked($includeTimeline)
            >

            <span>
                <span class="block font-bold text-[#2D3748]">
                    関係性の年表データもプロンプトに反映する
                </span>

                <span class="mt-1 block text-sm font-bold leading-7 text-[#718096]">
                    選択したオリジナルキャラクターと登録済みキャラクターの間に登録されている関係性年表も含めます。
                </span>
            </span>
        </label>
    </div>
</section>

<section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="mb-6">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 3</p>
        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">作風・ジャンル</h2>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            AIに依頼したい文章の雰囲気を設定します。
        </p>
    </div>

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <label for="writing_style">作風</label>
            <select id="writing_style" name="writing_style">
                <option value="">指定なし</option>
                @foreach ($writingStyleLabels as $key => $label)
                    <option value="{{ $key }}" @selected($writingStyle === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div id="writing-style-other-wrap" style="display: {{ $writingStyle === 'other' ? 'block' : 'none' }};">
            <label for="writing_style_other">作風 その他</label>
            <input id="writing_style_other"
                   type="text"
                   name="writing_style_other"
                   value="{{ $oldValue('writing_style_other') }}"
                   placeholder="例：切ない純文学風">
        </div>

        <div>
            <label for="genre">ジャンル</label>
            <select id="genre" name="genre">
                <option value="">指定なし</option>
                @foreach ($genreLabels as $key => $label)
                    <option value="{{ $key }}" @selected($genre === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div id="genre-other-wrap" style="display: {{ $genre === 'other' ? 'block' : 'none' }};">
            <label for="genre_other">ジャンル その他</label>
            <input id="genre_other"
                   type="text"
                   name="genre_other"
                   value="{{ $oldValue('genre_other') }}"
                   placeholder="例：主従関係、成長物語など">
        </div>
    </div>
</section>

{{-- V3_SAVED_STORY_ANALYSES --}}
<section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="mb-6">
        <p class="text-sm font-bold text-[#A0AEC0]">
            OPTION
        </p>

        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
            保存済みの文体分析
        </h2>

        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            ストーリー分析機能でAIが出した文体分析を、
            今回生成するプロンプトへ組み込めます。
        </p>

        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            使用する文体分析にチェックを入れてください。
            新しいものから最大10件を表示します。
        </p>
    </div>

    @if ($storyAnalyses->isEmpty())
        <div class="rounded-3xl bg-[#F7FAFC] p-8 text-center">
            <p class="text-lg font-bold text-[#2D3748]">
                保存済みの文体分析はありません。
            </p>

            <p class="mt-3 text-sm font-bold leading-7 text-[#A0AEC0]">
                ストーリー分析用プロンプトを作成し、
                AIの回答を保存すると、ここで選択できるようになります。
            </p>

            <div class="mt-5">
                <a
                    href="{{ route('writer.story-analyses.index') }}"
                    class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90"
                >
                    ストーリー分析へ
                </a>
            </div>
        </div>
    @else
        <div class="mb-5 rounded-2xl bg-[#FFF7FA] p-5">
            <p class="text-sm font-bold text-[#2D3748]">
                選択中：
                <span id="selected-story-analysis-count">
                    {{ number_format(count($selectedStoryAnalysisIds)) }}
                </span>
                / 10件
            </p>

            <p class="mt-2 text-xs font-bold leading-6 text-[#718096]">
                複数選択した場合は、すべての分析結果を参考情報としてプロンプトに追加します。
            </p>
        </div>

        <div class="space-y-4">
            @foreach ($storyAnalyses as $storyAnalysis)
                @php
                    $storyTitles = collect(
                        $storyAnalysis->story_snapshot ?? []
                    )
                        ->map(function ($snapshot) {
                            if (! is_array($snapshot)) {
                                return null;
                            }

                            $title = trim(
                                (string) ($snapshot['title'] ?? '')
                            );

                            if ($title === '') {
                                return null;
                            }

                            $episodeNumber =
                                $snapshot['episode_number'] ?? null;

                            return $episodeNumber !== null
                                ? '第'
                                    . (int) $episodeNumber
                                    . '話：'
                                    . $title
                                : $title;
                        })
                        ->filter()
                        ->values();
                @endphp

                <label class="flex cursor-pointer items-start gap-4 rounded-3xl border border-[#E2E8F0] bg-white p-5 transition hover:border-[#FED7E2] hover:bg-[#FFF7FA]">
                    <input
                        type="checkbox"
                        name="selected_story_analysis_ids[]"
                        value="{{ $storyAnalysis->id }}"
                        class="story-analysis-selection-checkbox mt-1 h-5 w-5 shrink-0 rounded"
                        @checked(in_array(
                            (int) $storyAnalysis->id,
                            $selectedStoryAnalysisIds,
                            true
                        ))
                    >

                    <span class="min-w-0 flex-1">
                        <span class="block text-lg font-bold text-[#2D3748]">
                            {{ $storyAnalysis->title }}
                        </span>

                        <span class="mt-2 block text-xs font-bold text-[#A0AEC0]">
                            保存日：
                            {{ $storyAnalysis->created_at?->format('Y/m/d H:i') }}
                        </span>

                        <span class="mt-4 block text-xs font-bold text-[#A0AEC0]">
                            分析対象ストーリー
                        </span>

                        @if ($storyTitles->isNotEmpty())
                            <span class="mt-2 block text-sm font-bold leading-7 text-[#4A5568]">
                                @foreach ($storyTitles as $storyTitle)
                                    <span class="mb-2 mr-2 inline-block rounded-full bg-[#F7FAFC] px-3 py-2">
                                        {{ $storyTitle }}
                                    </span>
                                @endforeach
                            </span>
                        @else
                            <span class="mt-2 block text-sm font-bold text-[#A0AEC0]">
                                ストーリータイトル情報なし
                            </span>
                        @endif

                        <span class="mt-3 block text-xs font-bold text-[#A0AEC0]">
                            分析結果：
                            {{ number_format($storyAnalysis->resultLength()) }}文字
                        </span>
                    </span>
                </label>
            @endforeach
        </div>

        <p
            id="story-analysis-selection-message"
            class="mt-4 hidden rounded-2xl bg-red-50 px-5 py-4 text-sm font-bold text-red-600"
        >
            選択できる文体分析は最大10件です。
        </p>
    @endif
</section>
{{-- /V3_SAVED_STORY_ANALYSES --}}

<section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="mb-6">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 4</p>
        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">あらすじ・構成</h2>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            書きたい内容や流れを入力します。起承転結は空欄でも保存できます。
        </p>
    </div>

    <div class="space-y-5">
        <div>
            <label for="synopsis">あらすじ</label>
            <textarea id="synopsis"
                      name="synopsis"
                      placeholder="どんな話を書きたいかを入力してください">{{ $oldValue('synopsis') }}</textarea>
        </div>

        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="plot_opening">起</label>
                <textarea id="plot_opening"
                          name="plot_opening"
                          placeholder="導入・出会い・始まり">{{ $oldValue('plot_opening') }}</textarea>
            </div>

            <div>
                <label for="plot_development">承</label>
                <textarea id="plot_development"
                          name="plot_development"
                          placeholder="関係の進展・出来事">{{ $oldValue('plot_development') }}</textarea>
            </div>

            <div>
                <label for="plot_turn">転</label>
                <textarea id="plot_turn"
                          name="plot_turn"
                          placeholder="転機・衝突・変化">{{ $oldValue('plot_turn') }}</textarea>
            </div>

            <div>
                <label for="plot_conclusion">結</label>
                <textarea id="plot_conclusion"
                          name="plot_conclusion"
                          placeholder="結末・余韻">{{ $oldValue('plot_conclusion') }}</textarea>
            </div>
        </div>

        <div>
            <label for="notes">備考</label>
            <textarea id="notes"
                      name="notes"
                      placeholder="AIに追加で守ってほしいこと、NG、補足など">{{ $oldValue('notes') }}</textarea>
        </div>
    </div>
</section>

{{-- V3_STORY_LENGTH_OPTIONS --}}
<section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="mb-6">
        <p class="text-sm font-bold text-[#A0AEC0]">OPTION</p>
        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
            長編・短編設定
        </h2>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            文字数や話数を指定したい場合だけ有効にしてください。
            チェックしない場合は、これまでどおりの通常プロンプトを生成します。
        </p>
    </div>

    @php
        $useStoryLengthOptions = (bool) old(
            'use_story_length_options',
            $prompt?->use_story_length_options ?? false
        );

        $storyLengthType = old(
            'story_length_type',
            $prompt?->story_length_type ?? 'short'
        );

        $outputPlotFirst = (bool) old(
            'output_plot_first',
            $prompt?->output_plot_first ?? true
        );

        $outputInParts = (bool) old(
            'output_in_parts',
            $prompt?->output_in_parts ?? true
        );
    @endphp

    <label class="flex cursor-pointer items-start gap-4 rounded-3xl border border-[#FED7E2] bg-[#FFF7FA] p-5">
        <input
            id="use_story_length_options"
            type="checkbox"
            name="use_story_length_options"
            value="1"
            class="mt-1 h-5 w-5 shrink-0 rounded"
            @checked($useStoryLengthOptions)
        >

        <span>
            <span class="block text-lg font-bold text-[#2D3748]">
                長編・短編を指定する
            </span>
            <span class="mt-2 block text-sm font-bold leading-7 text-[#718096]">
                チェックすると、話数・想定文字数・出力方法をプロンプトへ追加します。
            </span>
        </span>
    </label>

    <div
        id="story-length-options-panel"
        class="mt-6 space-y-5 rounded-3xl bg-[#F7FAFC] p-5 md:p-6"
        style="display: {{ $useStoryLengthOptions ? 'block' : 'none' }};"
    >
        <div>
            <p class="mb-3 text-sm font-bold text-[#2D3748]">
                物語形式
            </p>

            <div class="grid gap-4 md:grid-cols-2">
                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-[#E2E8F0] bg-white p-5">
                    <input
                        type="radio"
                        name="story_length_type"
                        value="short"
                        class="mt-1 h-5 w-5 shrink-0"
                        @checked($storyLengthType === 'short')
                    >

                    <span>
                        <span class="block font-bold text-[#2D3748]">
                            短編
                        </span>
                        <span class="mt-2 block text-sm font-bold leading-6 text-[#718096]">
                            全体約10,000字。起・承・転・結を各約2,500字で構成します。
                        </span>
                    </span>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-[#E2E8F0] bg-white p-5">
                    <input
                        type="radio"
                        name="story_length_type"
                        value="long"
                        class="mt-1 h-5 w-5 shrink-0"
                        @checked($storyLengthType === 'long')
                    >

                    <span>
                        <span class="block font-bold text-[#2D3748]">
                            長編
                        </span>
                        <span class="mt-2 block text-sm font-bold leading-6 text-[#718096]">
                            全10話。1話約10,000字、各話を起・承・転・結に分けます。
                        </span>
                    </span>
                </label>
            </div>
        </div>

        <div class="space-y-3">
            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-[#E2E8F0] bg-white p-5">
                <input
                    type="checkbox"
                    name="output_plot_first"
                    value="1"
                    class="mt-1 h-5 w-5 shrink-0 rounded"
                    @checked($outputPlotFirst)
                >

                <span>
                    <span class="block font-bold text-[#2D3748]">
                        本文より先に詳細プロットを出力する
                    </span>
                    <span class="mt-2 block text-sm font-bold leading-6 text-[#718096]">
                        場面、出来事、感情変化、次の場面へのつなぎを先に整理させます。
                    </span>
                </span>
            </label>

            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-[#E2E8F0] bg-white p-5">
                <input
                    type="checkbox"
                    name="output_in_parts"
                    value="1"
                    class="mt-1 h-5 w-5 shrink-0 rounded"
                    @checked($outputInParts)
                >

                <span>
                    <span class="block font-bold text-[#2D3748]">
                        起・承・転・結を順番に分けて出力する
                    </span>
                    <span class="mt-2 block text-sm font-bold leading-6 text-[#718096]">
                        一度にまとめず、各パートを明確に分けて出力させます。
                    </span>
                </span>
            </label>
        </div>
    </div>
</section>
{{-- /V3_STORY_LENGTH_OPTIONS --}}

<section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="mb-6">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 5</p>
        <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">プレビュー</h2>
        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
            保存前に、実際に生成されるプロンプト本文を確認できます。
        </p>
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="button"
                id="preview-button"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90">
            プレビュー生成
        </button>

        <button type="button"
                id="preview-copy-button"
                class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
            プレビューをコピー
        </button>
    </div>

    <p id="preview-message" class="mt-4 hidden rounded-2xl bg-[#FFF1F5] px-5 py-3 text-sm font-bold text-[#2D3748]"></p>

    <textarea id="prompt-preview"
              readonly
              class="mt-5 min-h-[680px] w-full rounded-2xl border-[#CBD5E0] bg-[#F7FAFC] p-5 font-mono text-sm leading-7 text-[#2D3748]"></textarea>
</section>

<div class="flex flex-col gap-3 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:flex-row md:items-center md:justify-between">
    <p class="text-sm font-bold text-[#718096]">
        下書き保存もできます。完成したら「作成・保存する」で有効状態にしてください。
    </p>

    <div class="flex flex-col gap-3 md:flex-row">
        <a href="{{ route('writer.prompts.index') }}"
           class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
            一覧へ戻る
        </a>

        <button type="submit"
                onclick="document.getElementById('prompt-status-input').value='draft';"
                class="inline-flex items-center justify-center rounded-2xl border border-[#CBD5E0] bg-white px-6 py-3 font-bold text-[#2D3748] hover:bg-[#F7FAFC]">
            下書きにする
        </button>

        <button type="submit"
                onclick="document.getElementById('prompt-status-input').value='active';"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
            作成・保存する
        </button>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const workSelect = document.getElementById('work_ref');
        const workWorldbuildingSection = document.getElementById(
            'work-worldbuilding-section'
        );
        const includeWorkWorldbuilding = document.getElementById(
            'include_work_worldbuilding'
        );
        const workWorldbuildingCategories = document.getElementById(
            'work-worldbuilding-categories'
        );
        const workWorldbuildingCategoryCheckboxes = Array.from(
            document.querySelectorAll('.work-worldbuilding-category')
        );
        const writingStyleSelect = document.getElementById('writing_style');
        const writingStyleOtherWrap = document.getElementById('writing-style-other-wrap');
        const genreSelect = document.getElementById('genre');
        const genreOtherWrap = document.getElementById('genre-other-wrap');
        const previewButton = document.getElementById('preview-button');
        const previewCopyButton = document.getElementById('preview-copy-button');
        const previewTextarea = document.getElementById('prompt-preview');
        const previewMessage = document.getElementById('preview-message');
        const form = document.getElementById('saved-prompt-form');
        const v1CharacterOptions = Array.from(
            document.querySelectorAll('.v1-character-option')
        );
        const v1CharacterCheckboxes = Array.from(
            document.querySelectorAll('.v1-character-checkbox')
        );
        const v1CharacterEmptyMessage = document.getElementById(
            'v1-character-empty-message'
        );
        const v1CharacterNoResultsMessage = document.getElementById(
            'v1-character-no-results-message'
        );
        const storyAnalysisCheckboxes = Array.from(
            document.querySelectorAll(
                '.story-analysis-selection-checkbox'
            )
        );

        const selectedStoryAnalysisCount =
            document.getElementById(
                'selected-story-analysis-count'
            );

        const storyAnalysisSelectionMessage =
            document.getElementById(
                'story-analysis-selection-message'
            );

        const useStoryLengthOptions = document.getElementById(
            'use_story_length_options'
        );
        const storyLengthOptionsPanel = document.getElementById(
            'story-length-options-panel'
        );

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }
        function refreshOtherFields() {
            if (writingStyleOtherWrap && writingStyleSelect) {
                writingStyleOtherWrap.style.display = writingStyleSelect.value === 'other' ? 'block' : 'none';
            }

            if (genreOtherWrap && genreSelect) {
                genreOtherWrap.style.display = genreSelect.value === 'other' ? 'block' : 'none';
            }
        }

        function showPreviewMessage(message) {
            if (!previewMessage) {
                return;
            }

            previewMessage.textContent = message;
            previewMessage.classList.remove('hidden');

            setTimeout(() => {
                previewMessage.classList.add('hidden');
            }, 2500);
        }

        async function generatePreview() {
            if (!form || !previewTextarea) {
                return;
            }

            const formData = new FormData(form);
            formData.delete('_method');

            previewButton.disabled = true;
            previewButton.classList.add('opacity-50');

            try {
                const response = await fetch('{{ route('writer.prompts.preview') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) {
                    previewTextarea.value = '';
                    showPreviewMessage(data.message || 'プレビュー生成に失敗しました。入力内容を確認してください。');
                    return;
                }

                previewTextarea.value = data.prompt_body || '';
                showPreviewMessage(`プレビューを生成しました。${data.length || previewTextarea.value.length}文字`);
            } catch (error) {
                previewTextarea.value = '';
                showPreviewMessage('プレビュー生成に失敗しました。');
            } finally {
                previewButton.disabled = false;
                previewButton.classList.remove('opacity-50');
            }
        }

        async function copyPreview() {
            if (!previewTextarea || !previewTextarea.value) {
                showPreviewMessage('コピーするプレビューがありません。');
                return;
            }

            try {
                await navigator.clipboard.writeText(previewTextarea.value);
            } catch (error) {
                previewTextarea.focus();
                previewTextarea.select();
                document.execCommand('copy');
            }

            showPreviewMessage('プレビューをコピーしました。');
        }
        function refreshV1Characters() {
            if (!workSelect) {
                return;
            }

            const workValue = workSelect.value || 'original';
            const selectedWorkId = workValue.startsWith('work:')
                ? workValue.replace('work:', '')
                : '';

            let visibleCount = 0;

            v1CharacterOptions.forEach((option) => {
                const isVisible =
                    selectedWorkId !== ''
                    && option.dataset.workId === selectedWorkId;

                /*
                 * label要素の共通CSSに影響されないよう、
                 * 外側divのhidden属性で表示を制御する。
                 */
                option.hidden = !isVisible;

                if (isVisible) {
                    visibleCount += 1;
                }
            });

            v1CharacterCheckboxes.forEach((checkbox) => {
                const belongsToSelectedWork =
                    selectedWorkId !== ''
                    && checkbox.dataset.workId === selectedWorkId;

                checkbox.disabled = !belongsToSelectedWork;

                if (!belongsToSelectedWork) {
                    checkbox.checked = false;
                }
            });

            if (v1CharacterEmptyMessage) {
                v1CharacterEmptyMessage.style.display =
                    selectedWorkId === ''
                        ? 'block'
                        : 'none';
            }

            if (v1CharacterNoResultsMessage) {
                v1CharacterNoResultsMessage.style.display =
                    selectedWorkId !== ''
                    && visibleCount === 0
                        ? 'block'
                        : 'none';
            }
        }

        function refreshWorkWorldbuilding(resetSelection = false) {
            const hasSelectedWork =
                workSelect
                && String(workSelect.value || '').startsWith('work:');

            if (workWorldbuildingSection) {
                workWorldbuildingSection.style.display =
                    hasSelectedWork ? 'block' : 'none';
            }

            if (!hasSelectedWork && includeWorkWorldbuilding) {
                includeWorkWorldbuilding.checked = false;
            }

            if (resetSelection || !hasSelectedWork) {
                workWorldbuildingCategoryCheckboxes.forEach((checkbox) => {
                    checkbox.checked = false;
                });
            }

            const isEnabled =
                hasSelectedWork
                && includeWorkWorldbuilding?.checked;

            if (workWorldbuildingCategories) {
                workWorldbuildingCategories.style.display =
                    isEnabled ? 'grid' : 'none';
            }

            workWorldbuildingCategoryCheckboxes.forEach((checkbox) => {
                checkbox.disabled = !isEnabled;
            });
        }

        function refreshStoryAnalysisSelection() {
            const selected = storyAnalysisCheckboxes.filter(
                checkbox => checkbox.checked
            );

            if (selectedStoryAnalysisCount) {
                selectedStoryAnalysisCount.textContent =
                    selected.length.toLocaleString();
            }

            storyAnalysisCheckboxes.forEach((checkbox) => {
                checkbox.disabled =
                    ! checkbox.checked
                    && selected.length >= 10;
            });
        }

        storyAnalysisCheckboxes.forEach((checkbox) => {
            checkbox.addEventListener('change', () => {
                const selected = storyAnalysisCheckboxes.filter(
                    item => item.checked
                );

                if (selected.length > 10) {
                    checkbox.checked = false;

                    if (storyAnalysisSelectionMessage) {
                        storyAnalysisSelectionMessage
                            .classList
                            .remove('hidden');

                        window.setTimeout(() => {
                            storyAnalysisSelectionMessage
                                .classList
                                .add('hidden');
                        }, 3000);
                    }
                }

                refreshStoryAnalysisSelection();
            });
        });

        function refreshStoryLengthOptions() {
            if (!useStoryLengthOptions || !storyLengthOptionsPanel) {
                return;
            }

            storyLengthOptionsPanel.style.display =
                useStoryLengthOptions.checked ? 'block' : 'none';
        }

        useStoryLengthOptions?.addEventListener(
            'change',
            refreshStoryLengthOptions
        );

        workSelect?.addEventListener(
            'change',
            function () {
                refreshV1Characters();
                refreshWorkWorldbuilding(true);
            }
        );

        includeWorkWorldbuilding?.addEventListener(
            'change',
            function () {
                refreshWorkWorldbuilding(false);
            }
        );

        writingStyleSelect?.addEventListener('change', refreshOtherFields);
        genreSelect?.addEventListener('change', refreshOtherFields);
        previewButton?.addEventListener('click', generatePreview);
        previewCopyButton?.addEventListener('click', copyPreview);
        refreshOtherFields();
        refreshV1Characters();
        refreshWorkWorldbuilding(false);
        refreshStoryAnalysisSelection();
        refreshStoryLengthOptions();
    });
</script>
