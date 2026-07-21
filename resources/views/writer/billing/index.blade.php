@include('writer.original_characters._layout_start', ['title' => '料金・契約'])

<div class="mb-8 rounded-3xl bg-gradient-to-r from-[#FED7E2] via-[#FFF1F5] to-white px-6 py-7 md:px-8">
    <p class="text-sm font-bold tracking-wide text-[#A05A70]">Oshi-Wiki MEMBERSHIP</p>
    <h1 class="mt-2 text-3xl font-bold text-[#2D3748]">料金・契約</h1>
    <p class="mt-3 max-w-3xl text-sm font-bold leading-7 text-[#4A5568]">
        登録できるキャラクターや関係性、ストーリーを増やして、
        長編の創作もひとつの場所で整理できます。
    </p>
</div>

@if (session('status'))
    <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 p-4 font-bold text-green-800">
        {{ session('status') }}
    </div>
@endif

@if (request('checkout') === 'success')
    <div class="mb-6 rounded-2xl border border-green-200 bg-green-50 p-4 text-green-800">
        決済手続きが完了しました。Stripeからの通知を確認後、Plusが有効になります。
    </div>
@endif

@if ($errors->has('billing'))
    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 font-bold text-red-800">
        {{ $errors->first('billing') }}
    </div>
@endif

<section class="mb-8 rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
    <div class="flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <div>
            <p class="text-sm font-bold text-[#A0AEC0]">現在のプラン</p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h2 class="text-2xl font-bold text-[#2D3748]">
                    {{ $hasPlus ? 'Oshi-Wiki Plus' : '無料プラン' }}
                </h2>
                <span class="rounded-full px-3 py-1 text-xs font-bold {{ $hasPlus ? 'bg-[#FED7E2] text-[#2D3748]' : 'bg-[#EDF2F7] text-[#4A5568]' }}">
                    {{ $hasPlus ? '利用中' : 'FREE' }}
                </span>
            </div>
        </div>

        @if ($hasPlus && $profile?->stripe_customer_id)
            <form method="POST" action="{{ route('writer.billing.portal') }}">
                @csrf
                <button class="rounded-2xl bg-[#2D3748] px-5 py-3 font-bold text-white hover:opacity-90">
                    契約・支払い方法を管理
                </button>
            </form>
        @endif
    </div>

    @if ($profile?->status === 'canceling')
        <div class="mt-5 rounded-2xl border-2 border-amber-300 bg-amber-50 p-5 text-amber-950">
            <p class="text-base font-bold">Plusは解約予約済みです。</p>
            <p class="mt-2 text-sm font-bold leading-7">
                {{ $profile->current_period_end?->format('Y年n月j日') }}まではPlusを利用できます。期日後は無料プランへ切り替わります。
            </p>
            @if ($hasFreePlanOverage)
                <p class="mt-3 rounded-xl bg-white p-4 text-sm font-bold leading-7">
                    無料プランの上限を超えているデータは自動削除されません。上限超過中は新規登録・複製ができませんが、既存データの閲覧・編集・削除・CSVエクスポートは利用できます。
                </p>
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($usageRows as $usage)
                        @if ($usage['is_over'])
                            <div class="rounded-xl border border-amber-200 bg-white px-4 py-3">
                                <p class="text-sm font-bold">{{ $usage['label'] }}</p>
                                <p class="mt-1 text-sm">現在 {{ number_format($usage['count']) }}件 / 無料上限 {{ number_format($usage['free_limit']) }}件</p>
                                <p class="mt-1 font-bold text-red-700">{{ number_format($usage['overage']) }}件超過</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    @elseif ($profile?->isInRetentionPeriod())
        <div class="mt-5 rounded-2xl border-2 border-amber-300 bg-amber-50 p-5 text-amber-950">
            <p class="text-base font-bold">
                Plus終了後のデータ保管期間です。
            </p>
            <p class="mt-2 text-sm font-bold leading-7">
                創作データは
                {{ $profile->retention_ends_at->format('Y年n月j日') }}
                まで保管されます。現在は閲覧とCSVエクスポートのみ利用できます。
            </p>
            <p class="mt-2 text-sm font-bold leading-7 text-red-700">
                保管期限を過ぎると創作データは自動削除され、復元できません。
            </p>
        </div>
    @elseif ($profile?->status === 'past_due_grace')
        <p class="mt-4 rounded-xl bg-red-50 p-3 text-sm font-bold text-red-800">
            お支払いを確認できません。猶予期限は{{ $profile->grace_period_ends_at?->format('Y年n月j日') }}です。
        </p>
    @endif

    @if ($profile?->current_period_end && $hasPlus)
        <p class="mt-4 text-sm text-[#4A5568]">
            現在の利用期間：{{ $profile->current_period_end->format('Y年n月j日') }}まで
        </p>
    @endif
</section>

<div class="mx-auto grid max-w-5xl items-stretch gap-6 lg:grid-cols-[0.88fr_1.12fr]">
    <section class="order-2 rounded-3xl border border-[#CBD5E0] bg-white p-6 shadow-sm lg:order-1 lg:my-8">
        <div class="text-center">
            <p class="text-sm font-bold tracking-wider text-[#A0AEC0]">FREE</p>
            <h2 class="mt-2 text-2xl font-bold text-[#2D3748]">無料プラン</h2>
            <p class="mt-4 text-4xl font-bold text-[#718096]">
                0<span class="ml-1 text-base">円／月</span>
            </p>
            <p class="mt-3 text-sm font-bold text-[#718096]">
                まずは気軽に創作を始めたい方へ
            </p>
        </div>

        <dl class="mt-8 divide-y divide-[#E2E8F0] rounded-2xl border border-[#E2E8F0] bg-[#F8FAFC] px-5">
            <div class="flex items-center justify-between py-4">
                <dt class="font-bold text-[#4A5568]">オリジナルキャラクター</dt>
                <dd class="font-bold text-[#718096]">{{ number_format($freePlan['limits']['original_characters']) }}件</dd>
            </div>
            <div class="flex items-center justify-between py-4">
                <dt class="font-bold text-[#4A5568]">関係性</dt>
                <dd class="font-bold text-[#718096]">{{ number_format($freePlan['limits']['relationships']) }}件</dd>
            </div>
            <div class="flex items-center justify-between py-4">
                <dt class="font-bold text-[#4A5568]">保存プロンプト</dt>
                <dd class="font-bold text-[#718096]">{{ number_format($freePlan['limits']['prompts']) }}件</dd>
            </div>
            <div class="flex items-center justify-between py-4">
                <dt class="font-bold text-[#4A5568]">ストーリー</dt>
                <dd class="font-bold text-[#718096]">{{ number_format($freePlan['limits']['stories']) }}件</dd>
            </div>
        </dl>

        <div class="mt-6 space-y-3">
            <div class="rounded-2xl bg-[#F7FAFC] px-5 py-4 text-center text-sm font-bold text-[#718096]">
                現在の基本機能をそのまま利用できます
            </div>

            <div class="rounded-2xl border border-[#E2E8F0] bg-white px-5 py-4">
                <div class="flex items-center justify-between gap-3">
                    <p class="font-bold text-[#4A5568]">
                        CSVエクスポート
                    </p>
                    <span class="rounded-full bg-[#EDF2F7] px-3 py-1 text-xs font-bold text-[#4A5568]">
                        利用可能
                    </span>
                </div>
                <p class="mt-2 text-sm font-bold leading-6 text-[#718096]">
                    登録データをCSVで保存し、バックアップできます。
                </p>
            </div>
        </div>
    </section>

    <section class="relative order-1 overflow-hidden rounded-[2rem] border-4 border-[#F2A7BC] bg-white shadow-xl shadow-pink-100/80 lg:order-2">
        <div class="bg-gradient-to-r from-[#E98FA9] to-[#F5B7C8] px-5 py-3 text-center">
            <p class="text-sm font-bold tracking-[0.18em] text-white">★ おすすめ ★</p>
        </div>

        <div class="p-7 md:p-9">
            <div class="text-center">
                <p class="text-sm font-bold tracking-wider text-[#C45E7D]">PREMIUM</p>
                <h2 class="mt-2 text-3xl font-bold text-[#2D3748]">Oshi-Wiki Plus</h2>
                <p class="mt-4 text-5xl font-bold text-[#D95F82]">
                    {{ number_format($plusPlan['monthly_price']) }}
                    <span class="ml-1 text-base text-[#2D3748]">円／月（税込）</span>
                </p>
                <p class="mt-3 text-sm font-bold leading-6 text-[#4A5568]">
                    1日あたり約16円で、創作データの登録上限を大幅に拡張
                </p>
            </div>

            <div class="mt-7 rounded-2xl bg-[#FFF1F5] px-5 py-4 text-center">
                <p class="text-sm font-bold text-[#A05A70]">無料プランと比べて</p>
                <p class="mt-1 text-xl font-bold text-[#2D3748]">
                    最大20倍の登録容量
                </p>
            </div>

            <dl class="mt-7 divide-y divide-[#F3D6DE] rounded-2xl border border-[#F3D6DE] bg-white px-5">
                <div class="flex items-center justify-between py-4">
                    <dt class="font-bold text-[#2D3748]">オリジナルキャラクター</dt>
                    <dd class="text-right">
                        <span class="text-xl font-bold text-[#D95F82]">{{ number_format($plusPlan['limits']['original_characters']) }}件</span>
                        <span class="ml-2 rounded-full bg-[#FED7E2] px-2 py-1 text-xs font-bold text-[#A05A70]">5倍</span>
                    </dd>
                </div>
                <div class="flex items-center justify-between py-4">
                    <dt class="font-bold text-[#2D3748]">関係性</dt>
                    <dd class="text-right">
                        <span class="text-xl font-bold text-[#D95F82]">{{ number_format($plusPlan['limits']['relationships']) }}件</span>
                        <span class="ml-2 rounded-full bg-[#FED7E2] px-2 py-1 text-xs font-bold text-[#A05A70]">10倍</span>
                    </dd>
                </div>
                <div class="flex items-center justify-between py-4">
                    <dt class="font-bold text-[#2D3748]">保存プロンプト</dt>
                    <dd class="text-right">
                        <span class="text-xl font-bold text-[#D95F82]">{{ number_format($plusPlan['limits']['prompts']) }}件</span>
                        <span class="ml-2 rounded-full bg-[#FED7E2] px-2 py-1 text-xs font-bold text-[#A05A70]">10倍</span>
                    </dd>
                </div>
                <div class="flex items-center justify-between py-4">
                    <dt class="font-bold text-[#2D3748]">ストーリー</dt>
                    <dd class="text-right">
                        <span class="text-xl font-bold text-[#D95F82]">{{ number_format($plusPlan['limits']['stories']) }}件</span>
                        <span class="ml-2 rounded-full bg-[#FED7E2] px-2 py-1 text-xs font-bold text-[#A05A70]">20倍</span>
                    </dd>
                </div>
            </dl>

            <div class="mt-7 overflow-hidden rounded-2xl border-2 border-[#F2A7BC] bg-gradient-to-r from-[#FFF1F5] to-white">
                <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-lg font-bold text-[#2D3748]">
                                CSV一括インポート
                            </p>
                            <span class="rounded-full bg-[#D95F82] px-3 py-1 text-xs font-bold text-white">
                                Plus限定
                            </span>
                        </div>
                        <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                            キャラクター・関係性・保存プロンプト・ストーリーを
                            CSVからまとめて新規登録できます。
                        </p>
                        <p class="mt-1 text-xs font-bold leading-6 text-[#A05A70]">
                            CSVエクスポートにも対応。大量データの登録とバックアップがかんたんです。
                        </p>
                    </div>

                    <div class="shrink-0 rounded-2xl bg-white px-4 py-3 text-center shadow-sm">
                        <p class="text-xs font-bold text-[#A05A70]">
                            まとめて登録
                        </p>
                        <p class="mt-1 text-xl font-bold text-[#D95F82]">
                            最大2,000行
                        </p>
                    </div>
                </div>
            </div>

            @if (! $hasPlus)
                <form class="mt-8" method="POST" action="{{ route('writer.billing.checkout') }}">
                    @csrf
                    <button
                        class="w-full rounded-2xl px-6 py-4 text-lg font-bold shadow-lg transition hover:-translate-y-0.5 hover:opacity-95 {{ $stripeConfigured ? 'bg-[#D95F82] text-white shadow-pink-200' : 'cursor-not-allowed bg-[#EDF2F7] text-[#A0AEC0] shadow-none' }}"
                        {{ $stripeConfigured ? '' : 'disabled' }}>
                        月額480円でPlusを始める
                    </button>
                </form>

                <p class="mt-3 text-center text-xs font-bold text-[#718096]">
                    いつでも解約可能・解約金なし
                </p>
            @else
                <div class="mt-8 rounded-2xl bg-green-50 px-5 py-4 text-center font-bold text-green-700">
                    Oshi-Wiki Plusを利用中です
                </div>
            @endif

            @unless ($stripeConfigured)
                <p class="mt-4 text-center text-sm font-bold text-amber-700">
                    現在は決済テストの準備中です。Stripe設定後に申込みできます。
                </p>
            @endunless
        </div>
    </section>
</div>

<section class="mt-10 rounded-3xl border border-[#E2E8F0] bg-white p-6 md:p-8">
    <h2 class="text-xl font-bold text-[#2D3748]">Plusがおすすめの方</h2>
    <div class="mt-5 grid gap-4 md:grid-cols-3">
        <div class="rounded-2xl bg-[#FFF7FA] p-5">
            <p class="font-bold text-[#2D3748]">長編作品を作りたい</p>
            <p class="mt-2 text-sm font-bold leading-6 text-[#718096]">
                登場人物やストーリーが増えても、上限を気にせず整理できます。
            </p>
        </div>
        <div class="rounded-2xl bg-[#FFF7FA] p-5">
            <p class="font-bold text-[#2D3748]">複数作品を管理したい</p>
            <p class="mt-2 text-sm font-bold leading-6 text-[#718096]">
                キャラクターや関係性を作品ごとにたっぷり登録できます。
            </p>
        </div>
        <div class="rounded-2xl bg-[#FFF7FA] p-5">
            <p class="font-bold text-[#2D3748]">設定を残して使い続けたい</p>
            <p class="mt-2 text-sm font-bold leading-6 text-[#718096]">
                保存プロンプトやストーリーを多く蓄積して、執筆に活用できます。
            </p>
        </div>
    </div>
</section>

<section class="mt-8 rounded-3xl border-2 border-amber-200 bg-amber-50 p-6 md:p-8">
    <h2 class="text-xl font-bold text-amber-950">Plusを解約した後のデータについて</h2>
    <div class="mt-4 space-y-3 text-sm font-bold leading-7 text-amber-950">
        <p>解約後も現在の支払期間が終了するまではPlusを利用できます。期間終了後は無料プランへ切り替わります。</p>
        <p>Oshi-Wiki Plusの利用期間終了後も、創作データは3か月間保管されます。保管期間中はデータの閲覧とCSVエクスポートを利用できますが、新規登録・編集・複製・削除・CSVインポートは利用できません。利用期間終了から3か月を過ぎると創作データは自動的に削除され、復元できません。削除予定日までにCSVエクスポートなどで保存してください。3か月以内にPlusへ再加入した場合は、保存されているデータを引き続き利用できます。</p>
        <p>大切な創作データは定期的にCSVエクスポートし、ご自身の端末やクラウドストレージにも保管することをおすすめします。</p>
    </div>
</section>

<div class="mt-6 rounded-2xl border border-[#E2E8F0] bg-white p-5 text-sm leading-7 text-[#4A5568]">
    <p>有料プランは自動更新です。いつでも解約でき、現在の支払期間終了まではPlusを利用できます。</p>
    <p>申込みにより、<a class="font-bold text-blue-600 underline" href="{{ route('public.terms') }}">利用規約</a>、
        <a class="font-bold text-blue-600 underline" href="{{ route('public.privacy') }}">プライバシーポリシー</a>、
        <a class="font-bold text-blue-600 underline" href="{{ route('public.billing-policy') }}">解約・返金ポリシー</a>に同意したものとします。</p>
</div>

@include('writer.original_characters._layout_end')
