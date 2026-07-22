@include(
    'writer.original_characters._layout_start',
    ['title' => 'CSV機能の使い方']
)

<div class="mb-8">
    <p class="text-sm font-bold tracking-wide text-[#A05A70]">
        CSV GUIDE
    </p>

    <h1 class="mt-2 text-3xl font-bold text-[#2D3748]">
        CSVインポート・エクスポートの使い方
    </h1>

    <p class="mt-4 max-w-4xl text-sm font-bold leading-8 text-[#718096]">
        CSVを使ったことがない方でも操作できるように、
        ダウンロードから編集、取り込みまでを順番に説明します。
        まずはこのページを上から読んでから操作してください。
    </p>

    <a href="{{ route('writer.csv.index') }}"
       class="mt-5 inline-flex rounded-2xl bg-[#2D3748] px-5 py-3 text-sm font-bold text-white">
        CSV管理画面へ戻る
    </a>
</div>

<section class="mb-8 rounded-3xl border-2 border-[#FED7E2] bg-[#FFF1F5] p-6 lg:p-8">
    <h2 class="text-2xl font-bold text-[#2D3748]">
        そもそもCSVとは？
    </h2>

    <div class="mt-4 space-y-4 text-sm font-bold leading-8 text-[#4A5568]">
        <p>
            CSVは、表の形でデータを保存するためのファイルです。
            Excel、Googleスプレッドシート、Numbersなどで開けます。
        </p>
        <p>
            1行目には「名前」「年齢」などの項目名が入り、
            2行目以降にキャラクターやストーリーのデータが並びます。
        </p>
        <p>
            Oshi-Wikiでは、CSVを使ってデータを端末へ保存したり、
            複数件をまとめて新規登録したりできます。
        </p>
    </div>
</section>

<div class="grid gap-8 xl:grid-cols-2">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm lg:p-8">
        <p class="text-sm font-bold text-[#A05A70]">EXPORT</p>
        <h2 class="mt-2 text-2xl font-bold text-[#2D3748]">
            エクスポートの使い方
        </h2>

        <p class="mt-4 text-sm font-bold leading-8 text-[#718096]">
            エクスポートは、Oshi-Wikiに登録している自分のデータを
            CSVファイルとして端末へ保存する機能です。
            無料プランでも利用できます。
        </p>

        <ol class="mt-6 space-y-5">
            <li class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="font-bold text-[#2D3748]">1. 保存したい種類を選ぶ</p>
                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    「オリジナルキャラクター」「関係性」
                    「保存プロンプト」「ストーリー」から選びます。
                </p>
            </li>

            <li class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="font-bold text-[#2D3748]">2. 「CSVエクスポート」を押す</p>
                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    CSVファイルのダウンロードが始まります。
                    通常は端末の「ダウンロード」フォルダに保存されます。
                </p>
            </li>

            <li class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="font-bold text-[#2D3748]">3. ファイルを保管する</p>
                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    日付が分かる名前で保存し、
                    外付けストレージやクラウドにも複製しておくと安心です。
                </p>
            </li>
        </ol>

        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <p class="font-bold text-amber-950">エクスポート時の注意</p>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm font-bold leading-7 text-amber-950">
                <li>出力されるのは、現在ログイン中の本人のデータだけです。</li>
                <li>キャラクター画像はCSVに含まれません。</li>
                <li>定期的なバックアップをおすすめします。</li>
            </ul>
        </div>
    </section>

    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm lg:p-8">
        <p class="text-sm font-bold text-[#A05A70]">IMPORT</p>
        <h2 class="mt-2 text-2xl font-bold text-[#2D3748]">
            インポートの使い方
        </h2>

        <p class="mt-4 text-sm font-bold leading-8 text-[#718096]">
            インポートは、CSVに書いたデータを
            Oshi-Wikiへまとめて新規登録する機能です。
            Plus契約中のみ利用できます。
        </p>

        <ol class="mt-6 space-y-5">
            <li class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="font-bold text-[#2D3748]">1. サンプルCSVをダウンロードする</p>
                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    登録したい種類の「サンプルCSV」を押してください。
                    最初から自分で列を作るより、サンプルを使う方が安全です。
                </p>
            </li>

            <li class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="font-bold text-[#2D3748]">2. 表計算ソフトで開く</p>
                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    Excel、Googleスプレッドシート、Numbersなどで開きます。
                    1行目の英字は項目名なので、削除・変更しないでください。
                </p>
            </li>

            <li class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="font-bold text-[#2D3748]">3. 2行目以降へデータを入力する</p>
                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    1件につき1行で入力します。
                    必須項目が空欄だと取り込めません。
                </p>
            </li>

            <li class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="font-bold text-[#2D3748]">4. CSV形式で保存する</p>
                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    保存形式は「CSV UTF-8」または「カンマ区切りCSV」を選びます。
                    Excel形式（.xlsx）のままでは取り込めません。
                </p>
            </li>

            <li class="rounded-2xl bg-[#F7FAFC] p-5">
                <p class="font-bold text-[#2D3748]">5. ファイルを選び、新規登録する</p>
                <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                    CSV管理画面でファイルを選び、
                    「CSVから新規登録」を押します。
                    完了メッセージが表示されれば取り込み完了です。
                </p>
            </li>
        </ol>

        <div class="mt-6 rounded-2xl border border-[#E2E8F0] bg-[#FFFDFD] p-4">
            <p class="text-sm font-bold text-[#2D3748]">
                入力例
            </p>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                サンプルCSVを開くと、1行目に列名、2行目以降に登録したい内容を入力します。
            </p>

            <figure class="mt-4 overflow-hidden rounded-2xl border border-[#E2E8F0] bg-white">
                <div class="flex justify-center bg-[#F8FAFC] px-3 py-4 sm:px-5 sm:py-6">
                    <img
                        src="{{ asset('images/writer/csv-guide-import-example.png') }}"
                        alt="CSVサンプルの入力例"
                        class="block h-auto max-h-[240px] w-auto max-w-full object-contain sm:max-h-[320px] lg:max-h-[380px] lg:max-w-[760px]"
                        loading="lazy"
                    >
                </div>

                <figcaption class="border-t border-[#E2E8F0] bg-white px-4 py-3 text-sm font-bold leading-6 text-[#718096] sm:px-5">
                    例：1行目の列名は変更せず、2行目以降へキャラクター情報を入力してください。
                </figcaption>
            </figure>
        </div>
    </section>
</div>

<section class="mt-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 lg:p-8">
    <h2 class="text-2xl font-bold text-[#2D3748]">
        種類ごとの大切なポイント
    </h2>

    <div class="mt-6 grid gap-5 md:grid-cols-2">
        <div class="rounded-2xl bg-[#F7FAFC] p-5">
            <h3 class="font-bold text-[#2D3748]">オリジナルキャラクター</h3>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                必須項目は「name」です。
                キャラクター画像はCSVでは登録できません。
            </p>
        </div>

        <div class="rounded-2xl bg-[#F7FAFC] p-5">
            <h3 class="font-bold text-[#2D3748]">関係性</h3>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                オリジナルキャラクター同士のみ対応します。
                先に対象キャラクターを登録してから取り込んでください。
            </p>
        </div>

        <div class="rounded-2xl bg-[#F7FAFC] p-5">
            <h3 class="font-bold text-[#2D3748]">保存プロンプト</h3>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                必須項目は「title」と「prompt_body」です。
            </p>
        </div>

        <div class="rounded-2xl bg-[#F7FAFC] p-5">
            <h3 class="font-bold text-[#2D3748]">ストーリー</h3>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                必須項目は「title」と「body」です。
            </p>
        </div>
    </div>
</section>

<section class="mt-8 rounded-3xl border border-red-200 bg-red-50 p-6 lg:p-8">
    <h2 class="text-xl font-bold text-red-950">
        インポート前に必ず確認してください
    </h2>

    <ul class="mt-4 list-disc space-y-3 pl-5 text-sm font-bold leading-8 text-red-950">
        <li>インポートしたデータは、すべて新規登録されます。</li>
        <li>同じ内容を再度取り込むと、重複して登録される可能性があります。</li>
        <li>1行目の列名は削除・変更・日本語化しないでください。</li>
        <li>行や列の途中に不要な空白を入れないでください。</li>
        <li>1回に取り込めるのは2,000行、ファイル容量は10MBまでです。</li>
        <li>登録上限を超える場合は取り込めません。</li>
        <li>本番データを取り込む前に、少ない件数で試すことをおすすめします。</li>
    </ul>
</section>

<section class="mt-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 lg:p-8">
    <h2 class="text-2xl font-bold text-[#2D3748]">
        よくあるエラーと確認方法
    </h2>

    <div class="mt-6 space-y-5">
        <div>
            <h3 class="font-bold text-[#2D3748]">「必須列がありません」と表示される</h3>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                1行目の列名が変更・削除されていないか確認してください。
                サンプルCSVをもう一度ダウンロードし直すと確実です。
            </p>
        </div>

        <div>
            <h3 class="font-bold text-[#2D3748]">ファイルを選べない</h3>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                拡張子が「.csv」または「.txt」になっているか確認してください。
                「.xlsx」のままでは選択できません。
            </p>
        </div>

        <div>
            <h3 class="font-bold text-[#2D3748]">文字化けする</h3>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                保存形式を「CSV UTF-8」にして保存し直してください。
            </p>
        </div>

        <div>
            <h3 class="font-bold text-[#2D3748]">関係性を取り込めない</h3>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                関係するキャラクター名が、登録済みの名前と完全に一致しているか確認してください。
            </p>
        </div>
    </div>
</section>

<section class="mt-8 rounded-3xl border-2 border-[#FED7E2] bg-[#FFF1F5] p-6 text-center lg:p-8">
    <h2 class="text-xl font-bold text-[#2D3748]">
        準備ができたらCSV管理画面へ進みましょう
    </h2>

    <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
        最初はサンプルCSVを使い、1〜2件だけで試すのがおすすめです。
    </p>

    <a href="{{ route('writer.csv.index') }}"
       class="mt-5 inline-flex rounded-2xl bg-[#D95F82] px-6 py-3 font-bold text-white">
        CSV管理画面へ戻る
    </a>
</section>

@include('writer.original_characters._layout_end')
