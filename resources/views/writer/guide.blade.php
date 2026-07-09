@include('writer.original_characters._layout_start', ['title' => '使い方ガイド'])

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">使い方ガイド</h2>
</div>

<div class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <p class="text-lg font-bold text-[#2D3748]">
        Oshi-Wiki 執筆補助は、登録したキャラクター情報や関係性をもとに、AIへ貼り付けるためのプロンプトを作成する機能です。
    </p>
    <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
        ChatGPTなどのAIに直接小説を書かせるのではなく、作品名・登場人物・作風・ジャンル・あらすじ・起承転結を整理したプロンプトを作り、コピーして利用します。
    </p>
</div>

<div class="space-y-6">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 1</p>
        <h3 class="mt-1 text-2xl font-bold text-[#2D3748]">オリジナルキャラクターを登録する</h3>
        <p class="mt-4 text-sm font-bold leading-7 text-[#4A5568]">
            まずは、自分の創作に使いたいオリジナルキャラクターを登録します。
            名前、一人称、口調、性格、外見、背景、NG設定などを入力しておくと、プロンプト作成時に自動で反映されます。
        </p>

        <div class="mt-5">
            <a href="{{ route('writer.original-characters.create') }}"
               class="inline-flex rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                オリジナルキャラクターを登録する
            </a>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 2</p>
        <h3 class="mt-1 text-2xl font-bold text-[#2D3748]">関係性を登録する</h3>
        <p class="mt-4 text-sm font-bold leading-7 text-[#4A5568]">
            必要に応じて、キャラクター同士の呼び方、関係性、印象、気持ちを登録します。
            オリジナルキャラクター同士だけでなく、Oshi-Wikiに登録済みの作品キャラクターとの関係性も登録できます。
        </p>

        <div class="mt-5">
            <a href="{{ route('writer.original-character-relationships.create') }}"
               class="inline-flex rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                関係性を登録する
            </a>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 3</p>
        <h3 class="mt-1 text-2xl font-bold text-[#2D3748]">プロンプトを作成する</h3>
        <p class="mt-4 text-sm font-bold leading-7 text-[#4A5568]">
            プロンプト管理から、作品名、登場人物、作風、ジャンル、あらすじ、起承転結を入力します。
            登場人物には、Oshi-Wikiに登録済みの作品キャラクターと、自分で登録したオリジナルキャラクターを選択できます。
        </p>

        <div class="mt-5">
            <a href="{{ route('writer.prompts.create') }}"
               class="inline-flex rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                プロンプトを作成する
            </a>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 4</p>
        <h3 class="mt-1 text-2xl font-bold text-[#2D3748]">プレビューで確認する</h3>
        <p class="mt-4 text-sm font-bold leading-7 text-[#4A5568]">
            保存前に「プレビュー生成」を押すと、実際にAIへ貼り付けるプロンプト本文を確認できます。
            内容を確認してから保存できます。
        </p>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 5</p>
        <h3 class="mt-1 text-2xl font-bold text-[#2D3748]">プロンプトをコピーしてAIに貼り付ける</h3>
        <p class="mt-4 text-sm font-bold leading-7 text-[#4A5568]">
            プロンプト詳細画面で「全文コピー」を押し、ChatGPTなどのAIチャットに貼り付けます。
            生成された小説本文は、必要に応じて自分で調整・加筆してください。
        </p>
    </section>
</div>

<div class="mt-8 rounded-3xl bg-[#FFF1F5] p-6 md:p-8">
    <h3 class="text-xl font-bold text-[#2D3748]">ポイント</h3>
    <ul class="mt-4 space-y-3 text-sm font-bold leading-7 text-[#4A5568]">
        <li>・キャラクター情報を詳しく登録するほど、プロンプトの精度が上がります。</li>
        <li>・関係性を登録しておくと、呼び方や距離感を反映しやすくなります。</li>
        <li>・プロンプトは保存・複製できるため、よく使う形をテンプレート化できます。</li>
        <li>・AIの出力内容は必ず確認し、必要に応じて修正してください。</li>
    </ul>
</div>

@include('writer.original_characters._layout_end')
