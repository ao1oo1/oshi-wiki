@include('writer.original_characters._layout_start', [
    'title' => '使い方ガイド',
])

@php
    $user = auth()->user();

    $originalCharacterLimit = $user
        ? \App\Support\WritingAssistLimits::originalCharactersPerUser($user)
        : 30;

    $relationshipLimit = $user
        ? \App\Support\WritingAssistLimits::relationshipsPerUser($user)
        : 100;

    $promptLimit = $user
        ? \App\Support\WritingAssistLimits::promptsPerUser($user)
        : 50;

    $limitLabel = function (?int $limit): string {
        return $limit === null
            ? '制限なし'
            : number_format($limit) . '件';
    };
@endphp

<div class="space-y-8">
    <section class="rounded-3xl border border-[#FED7E2] bg-[#FFF7FA] p-6 shadow-sm md:p-8">
        <p class="text-sm font-bold text-[#A0AEC0]">
            Oshi-Wiki 創作支援機能
        </p>

        <h1 class="mt-2 text-3xl font-bold text-[#2D3748]">
            使い方ガイド
        </h1>

        <p class="mt-4 text-sm font-bold leading-8 text-[#718096]">
            Oshi-Wikiの創作支援機能では、オリジナルキャラクター、
            キャラクター同士の関係性、ストーリー、文体分析結果などを登録し、
            小説のプロットや本文を作成するためのプロンプトを組み立てられます。
        </p>

        <p class="mt-3 text-sm font-bold leading-8 text-[#718096]">
            この機能がAIへ直接接続して小説を生成することはありません。
            作成したプロンプトをコピーし、利用するAIサービスへ貼り付けて使用してください。
        </p>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-bold text-[#A0AEC0]">
                    基本的な利用順序
                </p>

                <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                    おすすめの作業手順
                </h2>
            </div>
        </div>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ([
                [
                    'number' => '1',
                    'title' => 'キャラクターを登録',
                    'text' => '物語に登場させたいオリジナルキャラクターを登録します。',
                ],
                [
                    'number' => '2',
                    'title' => '関係性を登録',
                    'text' => 'オリジナルキャラクターや登録済みキャラクターとの関係を登録します。',
                ],
                [
                    'number' => '3',
                    'title' => 'ストーリーを登録',
                    'text' => '自分が書いた小説を登録し、文体分析用の資料として使用します。',
                ],
                [
                    'number' => '4',
                    'title' => '文体を分析',
                    'text' => 'ストーリーを選び、AIへ渡す文体分析用プロンプトを作成します。',
                ],
                [
                    'number' => '5',
                    'title' => 'プロンプトを作成',
                    'text' => '作品、登場人物、関係性、文体、あらすじなどを組み合わせます。',
                ],
                [
                    'number' => '6',
                    'title' => 'AIの回答を保存',
                    'text' => 'AIが返したプロットや執筆用データをプロンプト詳細へ保存します。',
                ],
            ] as $step)
                <div class="rounded-3xl bg-[#F7FAFC] p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#FED7E2] text-lg font-bold text-[#2D3748]">
                        {{ $step['number'] }}
                    </div>

                    <h3 class="mt-4 text-lg font-bold text-[#2D3748]">
                        {{ $step['title'] }}
                    </h3>

                    <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                        {{ $step['text'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-bold text-[#A0AEC0]">
                    STEP 1
                </p>

                <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                    オリジナルキャラクターを登録する
                </h2>
            </div>

            <a
                href="{{ route('writer.original-characters.index') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90"
            >
                オリジナルキャラクター管理へ
            </a>
        </div>

        <p class="mt-5 text-sm font-bold leading-8 text-[#718096]">
            自分で作成したキャラクターの情報を登録します。
            登録した内容は、プロンプト作成時の「登場人物詳細」に反映されます。
        </p>

        <div class="mt-6 grid gap-5 lg:grid-cols-2">
            <div class="rounded-3xl bg-[#F7FAFC] p-5">
                <h3 class="font-bold text-[#2D3748]">
                    登録できる主な項目
                </h3>

                <ul class="mt-3 space-y-2 text-sm font-bold leading-7 text-[#4A5568]">
                    <li>・名前・読み仮名</li>
                    <li>・年齢・性別</li>
                    <li>・所属・学年・クラス</li>
                    <li>・一人称・口調・口調例</li>
                    <li>・性格・特徴</li>
                    <li>・外見</li>
                    <li>・背景・経歴</li>
                    <li>・絶対に守りたい設定</li>
                    <li>・NG設定・避けたい表現</li>
                    <li>・備考</li>
                </ul>
            </div>

            <div class="rounded-3xl bg-[#FFF7FA] p-5">
                <h3 class="font-bold text-[#2D3748]">
                    登録のポイント
                </h3>

                <ul class="mt-3 space-y-2 text-sm font-bold leading-7 text-[#4A5568]">
                    <li>・一人称や口調は具体的に登録してください。</li>
                    <li>・口調例を複数登録すると、会話の再現精度が上がります。</li>
                    <li>・守ってほしい設定とNG事項は分けて入力してください。</li>
                    <li>・未確定の情報は無理に入力する必要はありません。</li>
                </ul>
            </div>
        </div>

        <p class="mt-5 rounded-2xl bg-[#F7FAFC] p-5 text-sm font-bold leading-7 text-[#718096]">
            登録上限：
            {{ $limitLabel($originalCharacterLimit) }}
        </p>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-bold text-[#A0AEC0]">
                    STEP 2
                </p>

                <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                    キャラクター同士の関係性を登録する
                </h2>
            </div>

            <a
                href="{{ route('writer.original-character-relationships.index') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90"
            >
                関係性管理へ
            </a>
        </div>

        <p class="mt-5 text-sm font-bold leading-8 text-[#718096]">
            「誰が、誰を、どのように呼び、どのように思っているか」を登録します。
            プロンプトへ登場人物同士の関係を正確に反映するために、事前登録をおすすめします。
        </p>

        <div class="mt-6 grid gap-5 lg:grid-cols-2">
            <div class="rounded-3xl bg-[#F7FAFC] p-5">
                <h3 class="font-bold text-[#2D3748]">
                    選択できる組み合わせ
                </h3>

                <ul class="mt-3 space-y-2 text-sm font-bold leading-7 text-[#4A5568]">
                    <li>・オリジナル → オリジナル</li>
                    <li>・オリジナル → 登録済みキャラクター</li>
                    <li>・登録済みキャラクター → オリジナル</li>
                    <li>・登録済みキャラクター → 登録済みキャラクター</li>
                </ul>
            </div>

            <div class="rounded-3xl bg-[#FFF7FA] p-5">
                <h3 class="font-bold text-[#2D3748]">
                    FromとToについて
                </h3>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    Fromは、呼び方や感情を向ける側です。
                    Toは、呼び方や感情を向けられる側です。
                </p>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    例：主人公が相手を「先輩」と呼び、尊敬している場合は、
                    主人公をFrom、相手をToに設定します。
                </p>
            </div>
        </div>

        <div class="mt-5 rounded-3xl bg-[#F7FAFC] p-5">
            <h3 class="font-bold text-[#2D3748]">
                年表データ
            </h3>

            <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                「5歳の頃に出会う」「物語開始時に再会する」など、
                関係性に関わる出来事を時系列で最大10件登録できます。
            </p>

            <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                プロンプト作成画面で
                「関係性の年表データもプロンプトに反映する」にチェックすると、
                選択した登場人物同士の年表がプロンプトへ追加されます。
            </p>
        </div>

        <p class="mt-5 rounded-2xl bg-[#F7FAFC] p-5 text-sm font-bold leading-7 text-[#718096]">
            登録上限：
            {{ $limitLabel($relationshipLimit) }}
        </p>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-bold text-[#A0AEC0]">
                    STEP 3
                </p>

                <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                    ストーリーを登録する
                </h2>
            </div>

            <a
                href="{{ route('writer.stories.index') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90"
            >
                ストーリー管理へ
            </a>
        </div>

        <p class="mt-5 text-sm font-bold leading-8 text-[#718096]">
            自分が書いた小説や文章を登録できます。
            登録した文章は、文体・会話・描写・文章構成などを分析するための資料として使用します。
        </p>

        <div class="mt-6 grid gap-5">
            <div class="rounded-3xl bg-[#F7FAFC] p-5">
                <h3 class="font-bold text-[#2D3748]">
                    登録時のおすすめ
                </h3>

                <ul class="mt-3 space-y-2 text-sm font-bold leading-7 text-[#4A5568]">
                    <li>・話数がある場合は、話数を入力してください。</li>
                    <li>・文体を分析したい本文を省略せず登録してください。</li>
                    <li>・異なる文体の作品は分けて登録してください。</li>
                    <li>・分析に不要なメモや指示文は本文へ混ぜないでください。</li>
                </ul>
            </div>

        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-bold text-[#A0AEC0]">
                    STEP 4
                </p>

                <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                    ストーリーの文体を分析する
                </h2>
            </div>

            <a
                href="{{ route('writer.story-analyses.index') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90"
            >
                ストーリー分析へ
            </a>
        </div>

        <p class="mt-5 text-sm font-bold leading-8 text-[#718096]">
            登録済みストーリーを選択し、AIに文体を分析させるためのプロンプトを作成します。
        </p>

        <div class="mt-6 space-y-4">
            @foreach ([
                [
                    'title' => '1. ストーリーを選択',
                    'text' => '分析したいストーリーにチェックを入れます。複数のストーリーをまとめて分析できます。',
                ],
                [
                    'title' => '2. 分析用プロンプトを生成',
                    'text' => '必要に応じて追加指示を入力し、文体分析用のプロンプトを生成します。',
                ],
                [
                    'title' => '3. AIへ貼り付ける',
                    'text' => '生成した分析用プロンプトをコピーし、利用するAIへ貼り付けます。',
                ],
                [
                    'title' => '4. AIの分析結果を保存',
                    'text' => 'AIが返した文体・構成・描写・会話傾向などの結論を貼り付けて保存します。',
                ],
            ] as $item)
                <div class="rounded-3xl bg-[#F7FAFC] p-5">
                    <h3 class="font-bold text-[#2D3748]">
                        {{ $item['title'] }}
                    </h3>

                    <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                        {{ $item['text'] }}
                    </p>
                </div>
            @endforeach
        </div>

        <div class="mt-5 rounded-3xl bg-[#FFF7FA] p-5">
            <h3 class="font-bold text-[#2D3748]">
                保存した文体分析の利用方法
            </h3>

            <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                保存した文体分析は、プロンプト作成画面の
                「保存済みの文体分析」に新しいものから最大10件表示されます。
            </p>

            <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                使用する分析結果にチェックを入れると、
                AIが出した文体分析の結論が生成プロンプトへ追加されます。
            </p>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-bold text-[#A0AEC0]">
                    STEP 5
                </p>

                <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                    プロンプトを作成する
                </h2>
            </div>

            <a
                href="{{ route('writer.prompts.create') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90"
            >
                プロンプトを作成する
            </a>
        </div>

        <p class="mt-5 text-sm font-bold leading-8 text-[#718096]">
            登録済みの作品・登場人物・関係性・文体・構成などを組み合わせ、
            AIへ渡す小説制作用プロンプトを作成します。
        </p>

        <div class="mt-6 space-y-6">
            <div class="rounded-3xl bg-[#F7FAFC] p-5">
                <h3 class="text-lg font-bold text-[#2D3748]">
                    STEP 1：基本情報・原作作品
                </h3>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    プロンプトの管理名、用途・目的、原作作品を設定します。
                </p>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    原作作品を選択すると、次の登場人物欄には、
                    その作品に紐づいた公開キャラクターだけが表示されます。
                </p>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    「オリジナル作品」を選択した場合は、登録済みキャラクターは表示されません。
                </p>
            </div>

            <div class="rounded-3xl bg-[#F7FAFC] p-5">
                <h3 class="text-lg font-bold text-[#2D3748]">
                    STEP 2：登場人物
                </h3>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    左側には、自分のオリジナルキャラクターが表示されます。
                    オリジナルキャラクターは、原作作品の選択に関係なく使用できます。
                </p>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    右側には、STEP 1で選択した作品の公開キャラクターだけが表示されます。
                    作品を変更した場合、変更前の作品のキャラクター選択は解除されます。
                </p>

                <div class="mt-4 rounded-2xl bg-[#FFF7FA] p-4">
                    <p class="text-sm font-bold leading-7 text-[#4A5568]">
                        ※
                        <a
                            href="{{ route('writer.original-character-relationships.index') }}"
                            class="text-[#2D3748] underline decoration-[#FED7E2] decoration-2 underline-offset-4 hover:opacity-80"
                        >
                            関係性登録
                        </a>
                        を行うと、登場人物同士のつながりもプロンプトに反映され、
                        より精度の高いプロンプトを作成できます。
                        あわせて登録するのがおすすめです。
                    </p>
                </div>
            </div>

            <div class="rounded-3xl bg-[#F7FAFC] p-5">
                <h3 class="text-lg font-bold text-[#2D3748]">
                    STEP 3：作風・ジャンル
                </h3>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    小説の雰囲気やジャンルを選択します。
                    選択肢にない場合は「その他」を選び、自由入力できます。
                </p>
            </div>

            <div class="rounded-3xl bg-[#FFF7FA] p-5">
                <h3 class="text-lg font-bold text-[#2D3748]">
                    保存済みの文体分析
                </h3>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    ストーリー分析で保存した文体分析が、新しいものから最大10件表示されます。
                </p>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    チェックした分析結果は、
                    「参考にする保存済み文体分析」として生成プロンプトへ追加されます。
                </p>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    AIには、分析結果の文章をそのまま転載するのではなく、
                    文章のリズム、描写、会話、視点、語彙などの特徴として反映するよう指示します。
                </p>
            </div>

            <div class="rounded-3xl bg-[#F7FAFC] p-5">
                <h3 class="text-lg font-bold text-[#2D3748]">
                    STEP 4：あらすじ・構成
                </h3>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    あらすじ、起・承・転・結、備考を入力します。
                    起承転結は空欄でも保存・プレビューできます。
                </p>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    AIに必ず守ってほしい条件や、避けてほしい表現は備考へ入力してください。
                </p>
            </div>

            <div class="rounded-3xl bg-[#FFF7FA] p-5">
                <h3 class="text-lg font-bold text-[#2D3748]">
                    長編・短編設定
                </h3>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    「長編・短編を指定する」にチェックした場合のみ、
                    話数や文字数、出力方法を指定できます。
                </p>

                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    <div class="rounded-2xl bg-white p-4">
                        <p class="font-bold text-[#2D3748]">
                            短編・1話完結
                        </p>

                        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                            全体約10,000字を想定し、
                            起・承・転・結を各約2,500字で構成します。
                        </p>
                    </div>

                    <div class="rounded-2xl bg-white p-4">
                        <p class="font-bold text-[#2D3748]">
                            長編・全10話
                        </p>

                        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                            全10話、1話約10,000字を想定し、
                            各話を起・承・転・結に分けます。
                        </p>
                    </div>
                </div>

                <ul class="mt-4 space-y-2 text-sm font-bold leading-7 text-[#4A5568]">
                    <li>・本文より先に詳細プロットを出力する</li>
                    <li>・起・承・転・結を順番に分けて出力する</li>
                </ul>
            </div>

            <div class="rounded-3xl bg-[#F7FAFC] p-5">
                <h3 class="text-lg font-bold text-[#2D3748]">
                    STEP 5：プレビュー
                </h3>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    保存前に、実際に生成されるプロンプト本文を確認できます。
                </p>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    内容を変更した場合は、再度「プレビュー生成」を押してください。
                    「プレビューをコピー」から生成内容をコピーできます。
                </p>
            </div>
        </div>

        <p class="mt-6 rounded-2xl bg-[#F7FAFC] p-5 text-sm font-bold leading-7 text-[#718096]">
            保存上限：
            {{ $limitLabel($promptLimit) }}
        </p>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="text-sm font-bold text-[#A0AEC0]">
                    STEP 6
                </p>

                <h2 class="mt-1 text-2xl font-bold text-[#2D3748]">
                    生成したプロンプトをAIへ貼り付ける
                </h2>
            </div>

            <a
                href="{{ route('writer.prompts.index') }}"
                class="inline-flex items-center justify-center rounded-2xl bg-[#FED7E2] px-5 py-3 text-sm font-bold text-[#2D3748] hover:opacity-90"
            >
                プロンプト管理へ
            </a>
        </div>

        <p class="mt-5 text-sm font-bold leading-8 text-[#718096]">
            保存したプロンプトの詳細画面を開き、
            生成したプロンプトをコピーして利用するAIへ貼り付けます。
        </p>

        <div class="mt-6 rounded-3xl bg-[#FFF7FA] p-5">
            <h3 class="font-bold text-[#2D3748]">
                プロンプト冒頭の指示
            </h3>

            <p class="mt-3 text-sm font-bold leading-7 text-[#4A5568]">
                あなたは小説制作に精通した敏腕編集者です。
                以下の情報を整理・分析し、プロット作成および小説本文の執筆に必要な要素をまとめたうえで、
                すぐに執筆を始められる形にしてください。
            </p>
        </div>

        <p class="mt-5 text-sm font-bold leading-8 text-[#718096]">
            AIは、選択した作品、登場人物、関係性、文体分析、作風、ジャンル、
            あらすじ、起承転結、長編・短編設定などをもとに回答します。
        </p>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <h2 class="text-2xl font-bold text-[#2D3748]">
            AIが返したプロット・執筆用データを保存する
        </h2>

        <p class="mt-4 text-sm font-bold leading-8 text-[#718096]">
            プロンプト詳細画面では、生成したプロンプトの下に、
            AIが返したプロット、構成案、設定整理、キャラクターの役割、
            場面案、伏線、執筆時の注意点などを保存できます。
        </p>

        <div class="mt-6 grid gap-5 lg:grid-cols-2">
            <div class="rounded-3xl bg-[#F7FAFC] p-5">
                <h3 class="font-bold text-[#2D3748]">
                    保存できる件数
                </h3>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    AI回答は、1つの保存プロンプトにつき1件です。
                </p>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    別の内容を保存する場合は、
                    現在の内容を削除後、再度登録してください。
                </p>
            </div>

            <div class="rounded-3xl bg-[#FFF7FA] p-5">
                <h3 class="font-bold text-[#2D3748]">
                    文字数上限
                </h3>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    保存できるAI回答は最大10,000文字です。
                </p>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    入力欄には現在の文字数が表示されます。
                </p>
            </div>
        </div>

        <div class="mt-5 rounded-3xl bg-[#F7FAFC] p-5">
            <h3 class="font-bold text-[#2D3748]">
                保存時点のプロンプト
            </h3>

            <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                AI回答を保存すると、回答を作成した時点の生成プロンプトも一緒に保存されます。
            </p>

            <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                後から元のプロンプトを編集しても、
                「回答生成時のプロンプトを確認」から当時の内容を確認できます。
            </p>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <h2 class="text-2xl font-bold text-[#2D3748]">
            保存・編集・複製について
        </h2>

        <div class="mt-6 grid gap-5 lg:grid-cols-3">
            <div class="rounded-3xl bg-[#F7FAFC] p-5">
                <h3 class="font-bold text-[#2D3748]">
                    下書き保存
                </h3>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    作成途中のプロンプトは下書きとして保存できます。
                </p>
            </div>

            <div class="rounded-3xl bg-[#F7FAFC] p-5">
                <h3 class="font-bold text-[#2D3748]">
                    編集
                </h3>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    保存済みの作品、キャラクター、文体分析、長編・短編設定などは編集画面へ復元されます。
                </p>
            </div>

            <div class="rounded-3xl bg-[#F7FAFC] p-5">
                <h3 class="font-bold text-[#2D3748]">
                    複製
                </h3>

                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    保存済みプロンプトを複製し、設定を一部変更して新しいプロンプトを作成できます。
                </p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#FED7E2] bg-[#FFF7FA] p-6 shadow-sm md:p-8">
        <h2 class="text-2xl font-bold text-[#2D3748]">
            注意事項
        </h2>

        <ul class="mt-5 space-y-3 text-sm font-bold leading-8 text-[#4A5568]">
            <li>
                ・AIの回答には誤りや、登録していない設定が含まれることがあります。必ず内容を確認してください。
            </li>
            <li>
                ・公開中の作品・キャラクターが非公開または削除された場合、参照できなくなることがあります。
            </li>
            <li>
                ・原作作品や一次創作者のガイドラインを確認し、モラルを守ってご利用ください。
            </li>
            <li>
                ・保存上限に達した場合は、不要なデータを削除してから新規登録してください。
            </li>
            <li>
                ・重要な文章や設定は、Oshi-Wiki以外の場所にもバックアップすることをおすすめします。
            </li>
        </ul>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <h2 class="text-2xl font-bold text-[#2D3748]">
            各機能へ移動
        </h2>

        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <a
                href="{{ route('writer.original-characters.index') }}"
                class="rounded-3xl bg-[#F7FAFC] p-5 transition hover:bg-[#FFF1F5]"
            >
                <p class="font-bold text-[#2D3748]">
                    オリジナルキャラクター管理
                </p>

                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    キャラクターの登録・編集・複製・削除
                </p>
            </a>

            <a
                href="{{ route('writer.original-character-relationships.index') }}"
                class="rounded-3xl bg-[#F7FAFC] p-5 transition hover:bg-[#FFF1F5]"
            >
                <p class="font-bold text-[#2D3748]">
                    関係性管理
                </p>

                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    呼び方・関係性・印象・年表の登録
                </p>
            </a>

            <a
                href="{{ route('writer.stories.index') }}"
                class="rounded-3xl bg-[#F7FAFC] p-5 transition hover:bg-[#FFF1F5]"
            >
                <p class="font-bold text-[#2D3748]">
                    ストーリー管理
                </p>

                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    小説本文や話数の登録・管理
                </p>
            </a>

            <a
                href="{{ route('writer.story-analyses.index') }}"
                class="rounded-3xl bg-[#F7FAFC] p-5 transition hover:bg-[#FFF1F5]"
            >
                <p class="font-bold text-[#2D3748]">
                    ストーリー分析
                </p>

                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    文体分析用プロンプトの生成と分析結果の保存
                </p>
            </a>

            <a
                href="{{ route('writer.prompts.index') }}"
                class="rounded-3xl bg-[#F7FAFC] p-5 transition hover:bg-[#FFF1F5]"
            >
                <p class="font-bold text-[#2D3748]">
                    プロンプト管理
                </p>

                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    保存済みプロンプトの確認・編集・複製
                </p>
            </a>

            <a
                href="{{ route('writer.prompts.create') }}"
                class="rounded-3xl bg-[#FED7E2] p-5 transition hover:opacity-90"
            >
                <p class="font-bold text-[#2D3748]">
                    新しいプロンプトを作成
                </p>

                <p class="mt-2 text-sm font-bold leading-7 text-[#4A5568]">
                    作品・人物・文体・構成を組み合わせる
                </p>
            </a>
        </div>
    </section>
</div>

@include('writer.original_characters._layout_end')
