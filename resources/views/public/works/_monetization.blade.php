@if (
    $monetization['items']->isNotEmpty()
    || filled($monetization['official_store_url'])
)
    <section class="oshi-card public-monetization-card">
        <div class="mb-5">
            <p class="text-xs font-bold tracking-wide text-[#E879A0]">
                配信・購入情報
            </p>
            <h2 class="mt-2 text-2xl font-bold text-[#2D3748]">
                この作品を楽しむ
            </h2>
        </div>

        @if ($monetization['has_affiliate'])
            <div class="mb-5 rounded-2xl border border-[#FED7E2] bg-[#FFF7FA] px-4 py-3 text-sm leading-7 text-[#4A5568]">
                このエリアには広告・アフィリエイトリンクが含まれます。
                リンク経由で申込み・購入された場合、運営者に報酬が発生することがあります。
            </div>
        @endif

        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($monetization['items'] as $item)
                <article class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-bold text-[#2D3748]">
                            {{ $item['service_name'] }}
                        </span>

                        @if ($item['is_affiliate'])
                            <span class="rounded-full bg-[#FFF0F5] px-3 py-1 text-xs font-bold text-[#C6537A]">
                                広告
                            </span>
                        @else
                            <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#718096]">
                                公式リンク
                            </span>
                        @endif
                    </div>

                    @if ($item['title'])
                        <p class="mt-3 font-bold text-[#2D3748]">
                            {{ $item['title'] }}
                        </p>
                    @endif

                    @if ($item['is_inherited'])
                        <p class="mt-2 text-xs text-[#718096]">
                            {{ $item['source_work_title'] }}の登録情報
                        </p>
                    @endif

                    <a
                        href="{{ $item['url'] }}"
                        target="_blank"
                        rel="{{ $item['is_affiliate'] ? 'sponsored noopener noreferrer' : 'noopener noreferrer' }}"
                        class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-[#2D3748] px-5 py-3 font-bold text-white transition hover:opacity-90"
                    >
                        {{ $item['button_label'] }}
                    </a>
                </article>
            @endforeach

            @if (filled($monetization['official_store_url']))
                <article class="rounded-2xl border border-[#E2E8F0] bg-white p-5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-bold text-[#2D3748]">
                            公式販売ページ
                        </span>
                        <span class="rounded-full bg-[#F7FAFC] px-3 py-1 text-xs font-bold text-[#718096]">
                            公式リンク
                        </span>
                    </div>

                    <a
                        href="{{ $monetization['official_store_url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="mt-4 inline-flex w-full items-center justify-center rounded-xl border border-[#2D3748] bg-white px-5 py-3 font-bold text-[#2D3748] transition hover:bg-[#F7FAFC]"
                    >
                        公式販売ページを見る
                    </a>
                </article>
            @endif
        </div>

        <p class="mt-5 text-xs leading-6 text-[#718096]">
            配信・販売状況は変更される場合があります。最新情報はリンク先でご確認ください。
        </p>
    </section>
@endif
