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

    <a href="{{ route('writer.csv.guide') }}"
       class="mt-5 inline-flex items-center rounded-2xl border-2 border-[#FED7E2] bg-white px-5 py-3 text-sm font-bold text-[#2D3748] shadow-sm hover:bg-[#FFF1F5]">
        はじめての方向け：CSV機能の使い方
    </a>
</div>

@if (! $hasPlus)
    <section class="mb-8 rounded-3xl border-2 border-[#FED7E2] bg-[#FFF1F5] p-6">
        <h2 class="text-xl font-bold text-[#2D3748]">
            CSVインポート/エクスポートはPlus限定です
        </h2>
        <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
            無料会員はCSVインポート・エクスポートを利用できません。
            Oshi-Wiki Plusへ登録すると、一括登録とCSVバックアップ機能を利用できます。
        </p>
        <a href="{{ route('writer.billing.index') }}"
           class="mt-5 inline-flex rounded-2xl bg-[#D95F82] px-5 py-3 font-bold text-white">
            Plusプランを見る
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

@if ($hasPlus)
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
                           class="block w-full rounded-2xl border border-[#CBD5E0] bg-white p-3 text-sm font-bold">

                    <button type="submit"
                            class="mt-4 w-full rounded-2xl bg-[#FED7E2] px-5 py-3 font-bold text-[#2D3748]">
                        CSVから新規登録
                    </button>
                </form>
            </section>
        @endforeach
    </div>
@else
    <div class="relative overflow-hidden rounded-3xl border border-[#CBD5E0] bg-white shadow-sm">
        <div class="pointer-events-none select-none opacity-35">
            <div class="grid gap-6 p-6 lg:grid-cols-2">
                @foreach ($types as $type => $label)
                    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6">
                        <h2 class="text-xl font-bold text-[#2D3748]">
                            {{ $label }}
                        </h2>

                        <div class="mt-5 flex flex-wrap gap-3">
                            <span class="rounded-2xl bg-[#2D3748] px-5 py-3 text-sm font-bold text-white">
                                CSVエクスポート
                            </span>

                            <span class="rounded-2xl border border-[#CBD5E0] bg-white px-5 py-3 text-sm font-bold text-[#2D3748]">
                                サンプルCSV
                            </span>
                        </div>

                        <div class="mt-6 border-t border-[#E2E8F0] pt-6">
                            <div class="h-12 rounded-2xl border border-[#CBD5E0] bg-[#EDF2F7]"></div>
                            <div class="mt-4 h-12 rounded-2xl bg-[#FED7E2]"></div>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>

        <div class="absolute inset-0 z-10 flex min-h-[420px] flex-col items-center justify-center bg-[#2D3748]/45 px-6 py-10 text-center backdrop-blur-[1px]">
            <div class="flex h-20 w-20 items-center justify-center rounded-full border-4 border-white bg-white/95 shadow-xl">
                <svg xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 24 24"
                     class="h-10 w-10 text-[#2D3748]"
                     fill="none"
                     stroke="currentColor"
                     stroke-width="2.2"
                     stroke-linecap="round"
                     stroke-linejoin="round"
                     aria-hidden="true">
                    <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                    <path d="M8 11V8a4 4 0 1 1 8 0v3"></path>
                </svg>
            </div>

            <div class="mt-5 max-w-xl rounded-3xl bg-white/95 px-6 py-5 shadow-xl">
                <p class="text-xl font-bold text-[#2D3748]">
                    CSV機能はOshi-Wiki Plus限定です
                </p>
                <p class="mt-3 text-sm font-bold leading-7 text-[#718096]">
                    無料会員はCSVインポート・エクスポートを利用できません。
                    Plusへ登録すると、一括登録とCSVバックアップ機能を利用できます。
                </p>
                <a href="{{ route('writer.billing.index') }}"
                   class="mt-5 inline-flex min-h-11 items-center justify-center rounded-2xl bg-[#D95F82] px-5 py-3 text-sm font-bold text-white">
                    Plusプランを見る
                </a>
            </div>
        </div>
    </div>
@endif

@include('writer.original_characters._layout_end')
