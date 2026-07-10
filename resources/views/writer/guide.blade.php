@include('writer.original_characters._layout_start', ['title' => '使い方ガイド'])

<div class="mb-8">
    <h1 class="text-3xl font-bold text-[#2D3748]">Oshi-Wiki 執筆補助</h1>
</div>

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h2 class="text-2xl font-bold text-[#2D3748]">使い方ガイド</h2>
</div>

<section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="grid gap-8 xl:grid-cols-[1fr_360px] xl:items-center">
        <div>
            <p class="text-sm font-bold text-[#A0AEC0]">Getting Started</p>
            <h3 class="mt-2 text-3xl font-bold leading-snug text-[#2D3748]">
                キャラクター情報から、AIに貼り付けるプロンプトを作成できます。
            </h3>
            <p class="mt-4 text-sm font-bold leading-7 text-[#718096]">
                Oshi-Wiki 執筆補助は、自分で登録したオリジナルキャラクター・関係性情報をもとに、
                ChatGPTなどのAIへ貼り付けるためのプロンプトを作成する機能です。
            </p>
        </div>

        <div class="rounded-3xl bg-[#FFF1F5] p-5">
            <p class="text-base font-bold text-[#2D3748]">すぐ始める</p>
            <div class="mt-4 space-y-3">
                <a href="{{ route('writer.original-characters.create') }}"
                   class="block rounded-2xl bg-white px-5 py-4 text-sm font-bold text-[#2D3748] hover:bg-[#FED7E2]">
                    1. キャラクターを登録する
                </a>
                <a href="{{ route('writer.original-character-relationships.create') }}"
                   class="block rounded-2xl bg-white px-5 py-4 text-sm font-bold text-[#2D3748] hover:bg-[#FED7E2]">
                    2. 関係性を登録する
                </a>
                <a href="{{ route('writer.prompts.create') }}"
                   class="block rounded-2xl bg-white px-5 py-4 text-sm font-bold text-[#2D3748] hover:bg-[#FED7E2]">
                    3. プロンプトを作成する
                </a>
            </div>
        </div>
    </div>
</section>

<section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
    <div class="grid gap-6 xl:grid-cols-[260px_1fr] xl:items-start">
        <div>
            <p class="text-sm font-bold text-[#A0AEC0]">Limit</p>
            <h3 class="mt-2 text-2xl font-bold text-[#2D3748]">登録上限について</h3>
        </div>

        <div>
            <p class="text-sm font-bold leading-7 text-[#4A5568]">
                一般会員は、執筆補助で登録できるデータ数に上限があります。
                各画面やサイドバーに「10 / 50」のような形式で、現在の登録数と上限数を表示しています。
            </p>

            <div class="mt-5 grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="text-xs font-bold text-[#A0AEC0]">オリジナルキャラクター</p>
                    <p class="mt-2 text-2xl font-bold text-[#2D3748]">最大30件</p>
                </div>

                <div class="rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="text-xs font-bold text-[#A0AEC0]">関係性</p>
                    <p class="mt-2 text-2xl font-bold text-[#2D3748]">最大100件</p>
                </div>

                <div class="rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="text-xs font-bold text-[#A0AEC0]">プロンプト管理</p>
                    <p class="mt-2 text-2xl font-bold text-[#2D3748]">最大50件</p>
                </div>
            </div>

            <p class="mt-5 text-sm font-bold leading-7 text-[#718096]">
                例：プロンプト管理が「10 / 50」と表示されている場合、50件まで登録できるうち10件を使用中という意味です。
                最高管理者は上限なしで利用できます。
            </p>
        </div>
    </div>
</section>

<section class="mb-8 grid gap-6 md:grid-cols-2 xl:grid-cols-5">
    <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 1</p>
        <h3 class="mt-2 text-lg font-bold text-[#2D3748]">キャラ登録</h3>
        <p class="mt-3 text-sm font-bold leading-7 text-[#4A5568]">
            名前、一人称、口調、性格、外見、背景などを登録します。
        </p>
    </div>

    <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 2</p>
        <h3 class="mt-2 text-lg font-bold text-[#2D3748]">関係性登録</h3>
        <p class="mt-3 text-sm font-bold leading-7 text-[#4A5568]">
            呼び方、関係性、印象、気持ちを登録します。
        </p>
    </div>

    <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 3</p>
        <h3 class="mt-2 text-lg font-bold text-[#2D3748]">条件入力</h3>
        <p class="mt-3 text-sm font-bold leading-7 text-[#4A5568]">
            作品、登場人物、作風、ジャンル、あらすじを選びます。
        </p>
    </div>

    <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 4</p>
        <h3 class="mt-2 text-lg font-bold text-[#2D3748]">プレビュー</h3>
        <p class="mt-3 text-sm font-bold leading-7 text-[#4A5568]">
            保存前に、生成されるプロンプト本文を確認します。
        </p>
    </div>

    <div class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">STEP 5</p>
        <h3 class="mt-2 text-lg font-bold text-[#2D3748]">AIへ貼付</h3>
        <p class="mt-3 text-sm font-bold leading-7 text-[#4A5568]">
            コピーしてChatGPTなどのAIに貼り付けます。
        </p>
    </div>
</section>

<div class="space-y-6">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="grid gap-6 xl:grid-cols-[220px_1fr]">
            <div>
                <p class="text-sm font-bold text-[#A0AEC0]">STEP 1</p>
                <h3 class="mt-2 text-2xl font-bold text-[#2D3748]">オリジナルキャラクターを登録する</h3>
            </div>

            <div>
                <p class="text-sm font-bold leading-7 text-[#4A5568]">
                    自分の創作に使いたいオリジナルキャラクターを登録します。
                    名前、読み仮名、年齢、性別、所属、一人称、口調、口調例、性格、外見、背景、NG設定などを入れておくと、
                    プロンプト作成時に自動で反映されます。
                </p>

                <div class="mt-5 rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="text-sm font-bold text-[#2D3748]">登録しておくと便利な項目</p>
                    <ul class="mt-3 grid gap-2 text-sm font-bold text-[#4A5568] md:grid-cols-2">
                        <li>・一人称</li>
                        <li>・口調</li>
                        <li>・口調例</li>
                        <li>・性格・特徴</li>
                        <li>・外見</li>
                        <li>・背景・経歴</li>
                        <li>・絶対に守りたい設定</li>
                        <li>・NG設定</li>
                    </ul>
                </div>

                <div class="mt-5">
                    <a href="{{ route('writer.original-characters.create') }}"
                       class="inline-flex rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                        オリジナルキャラクターを登録する
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="grid gap-6 xl:grid-cols-[220px_1fr]">
            <div>
                <p class="text-sm font-bold text-[#A0AEC0]">STEP 2</p>
                <h3 class="mt-2 text-2xl font-bold text-[#2D3748]">関係性を登録する</h3>
            </div>

            <div>
                <p class="text-sm font-bold leading-7 text-[#4A5568]">
                    キャラクター同士の呼び方、関係性、印象、気持ちを登録します。
                    自分で登録したオリジナルキャラクター同士の関係性を登録できます。
                </p>

                <div class="mt-5 rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="text-sm font-bold text-[#2D3748]">例</p>
                    <ul class="mt-3 space-y-2 text-sm font-bold text-[#4A5568]">
                        <li>・キャラクターA → キャラクターBへの呼び方</li>
                        <li>・友人、幼なじみ、師弟、家族、敵対などの関係性</li>
                        <li>・信頼している、苦手意識がある、気になる存在などの印象</li>
                    </ul>
                </div>

                <div class="mt-5">
                    <a href="{{ route('writer.original-character-relationships.create') }}"
                       class="inline-flex rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                        関係性を登録する
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="grid gap-6 xl:grid-cols-[220px_1fr]">
            <div>
                <p class="text-sm font-bold text-[#A0AEC0]">STEP 3</p>
                <h3 class="mt-2 text-2xl font-bold text-[#2D3748]">プロンプトを作成する</h3>
            </div>

            <div>
                <p class="text-sm font-bold leading-7 text-[#4A5568]">
                    プロンプト管理から、作品名、登場人物、作風、ジャンル、あらすじ、起承転結を入力します。
                    登場人物には、自分で登録したオリジナルキャラクターを選択できます。
                </p>

                <div class="mt-5 rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="text-sm font-bold text-[#2D3748]">選べる内容</p>
                    <ul class="mt-3 grid gap-2 text-sm font-bold text-[#4A5568] md:grid-cols-2">
                        <li>・作品名</li>
                        <li>・登場人物</li>
                        <li>・作風</li>
                        <li>・ジャンル</li>
                        <li>・あらすじ</li>
                        <li>・起承転結</li>
                        <li>・備考</li>
                    </ul>
                </div>

                <div class="mt-5">
                    <a href="{{ route('writer.prompts.create') }}"
                       class="inline-flex rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748] hover:opacity-90">
                        プロンプトを作成する
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="grid gap-6 xl:grid-cols-[220px_1fr]">
            <div>
                <p class="text-sm font-bold text-[#A0AEC0]">STEP 4</p>
                <h3 class="mt-2 text-2xl font-bold text-[#2D3748]">プレビューで確認する</h3>
            </div>

            <div>
                <p class="text-sm font-bold leading-7 text-[#4A5568]">
                    保存前に「プレビュー生成」を押すと、実際にAIへ貼り付けるプロンプト本文を確認できます。
                    内容を確認してから保存することで、あとから使いやすいプロンプトを管理できます。
                </p>
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm md:p-8">
        <div class="grid gap-6 xl:grid-cols-[220px_1fr]">
            <div>
                <p class="text-sm font-bold text-[#A0AEC0]">STEP 5</p>
                <h3 class="mt-2 text-2xl font-bold text-[#2D3748]">コピーしてAIに貼り付ける</h3>
            </div>

            <div>
                <p class="text-sm font-bold leading-7 text-[#4A5568]">
                    プロンプト詳細画面で「全文コピー」を押し、ChatGPTなどのAIチャットに貼り付けます。
                    生成された小説本文は、そのまま使うのではなく、必要に応じて自分で確認・調整・加筆してください。
                </p>

                <div class="mt-5 rounded-2xl bg-[#FFF1F5] p-5">
                    <p class="text-sm font-bold text-[#2D3748]">注意</p>
                    <ul class="mt-3 space-y-2 text-sm font-bold leading-7 text-[#4A5568]">
                        <li>・AIの出力は必ず確認してください。</li>
                        <li>・公式設定や登録情報と異なる内容が出る場合があります。</li>
                        <li>・公開する文章は、各作品のガイドラインやマナーに沿って扱ってください。</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="mt-8 rounded-3xl bg-[#FFF1F5] p-6 md:p-8">
    <h3 class="text-xl font-bold text-[#2D3748]">登録してほしいデータがある場合</h3>
    <p class="mt-3 text-sm font-bold leading-7 text-[#4A5568]">
        登録してほしい作品・ジャンル・キャラクターがある場合は、ダッシュボードの「データ登録リクエスト」からお問い合わせください。
        また、作品やキャラクターデータの登録に協力してくださるコントリビュータも募集しています。
    </p>
</div>

@include('writer.original_characters._layout_end')
