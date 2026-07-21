@include(
    'writer.original_characters._layout_start',
    ['title' => 'CSV管理']
)

<div class="mb-8">
    <p class="text-sm font-bold tracking-wide text-[#A05A70]">
        PLUS FEATURE
    </p>
    <h1 class="mt-2 text-3xl font-bold text-[#2D3748]">
        CSVインポート・エクスポート
    </h1>
    <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
        キャラクター、関係性、保存プロンプト、ストーリーを
        CSVでバックアップ・一括登録できます。
    </p>
</div>

@if (! $hasPlus)
    <section class="mb-8 rounded-3xl border-2 border-[#FED7E2] bg-[#FFF1F5] p-6">
        <h2 class="text-xl font-bold text-[#2D3748]">
            CSVインポートはPlus限定です
        </h2>
        <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
            CSVエクスポートは無料プランでも利用できます。
            一括登録はPlus加入後に利用できます。
        </p>
        <a href="{{ route('writer.billing.index') }}"
           class="mt-5 inline-flex rounded-2xl bg-[#D95F82] px-5 py-3 font-bold text-white">
            Plusの詳細を見る
        </a>
    </section>
@endif

<section class="mb-8 rounded-3xl border border-amber-200 bg-amber-50 p-6">
    <h2 class="font-bold text-amber-950">注意事項</h2>
    <ul class="mt-3 list-disc space-y-2 pl-5 text-sm font-bold leading-7 text-amber-950">
        <li>インポートは新規登録として処理されます。</li>
        <li>キャラクター画像はCSVに含まれません。</li>
        <li>関係性CSVはオリジナルキャラクター同士のみ対応します。</li>
        <li>関係性を取り込む前にキャラクターを取り込んでください。</li>
        <li>1回2,000行、10MBまでです。</li>
    </ul>
</section>

<div class="grid gap-6 lg:grid-cols-2">
    @foreach ($types as $type => $label)
        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <h2 class="text-xl font-bold text-[#2D3748]">
                {{ $label }}
            </h2>

            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('writer.csv.export', $type) }}"
                   class="rounded-2xl bg-[#2D3748] px-5 py-3 text-sm font-bold text-white">
                    CSVエクスポート
                </a>

                <a href="{{ route('writer.csv.sample', $type) }}"
                   class="rounded-2xl border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-bold text-[#2D3748]">
                    サンプルCSV
                </a>
            </div>

            <form method="POST"
                  action="{{ route('writer.csv.import', $type) }}"
                  enctype="multipart/form-data"
                  class="mt-6 border-t border-[#E2E8F0] pt-6">
                @csrf

                <input type="file"
                       name="csv_file"
                       accept=".csv,text/csv,text/plain"
                       class="block w-full rounded-2xl border border-[#CBD5E0] bg-white p-3 text-sm font-bold"
                       {{ $hasPlus ? '' : 'disabled' }}>

                <button type="submit"
                        class="mt-4 w-full rounded-2xl px-5 py-3 font-bold
                            {{ $hasPlus
                                ? 'bg-[#FED7E2] text-[#2D3748]'
                                : 'cursor-not-allowed bg-[#EDF2F7] text-[#A0AEC0]' }}"
                        {{ $hasPlus ? '' : 'disabled' }}>
                    CSVから新規登録
                </button>
            </form>
        </section>
    @endforeach
</div>

<section class="mt-8 rounded-3xl border border-[#E2E8F0] bg-white p-6">
    <p class="text-sm font-bold leading-7 text-[#718096]">
        Plus利用期間終了後もCSVエクスポートは利用できます。
        CSVインポートはPlus契約中のみ利用できます。
    </p>
</section>

@include('writer.original_characters._layout_end')
