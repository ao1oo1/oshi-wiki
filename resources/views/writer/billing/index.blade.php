@include('writer.original_characters._layout_start', ['title' => '料金・契約'])

<div class="mb-8 rounded-2xl bg-[#FED7E2] px-6 py-5">
    <h1 class="text-2xl font-bold text-[#2D3748]">料金・契約</h1>
    <p class="mt-2 text-sm font-bold text-[#4A5568]">
        現在のプラン、登録上限、次回更新予定を確認できます。
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

<div class="grid gap-6 lg:grid-cols-2">
    <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">現在のプラン</p>
        <h2 class="mt-2 text-2xl font-bold text-[#2D3748]">
            {{ $hasPlus ? 'Oshi-Wiki Plus' : '無料プラン' }}
        </h2>

        @if ($profile?->status === 'canceling')
            <p class="mt-4 rounded-xl bg-amber-50 p-3 text-sm font-bold text-amber-900">
                解約予約済みです。{{ $profile->current_period_end?->format('Y年n月j日') }}まではPlusを利用できます。
            </p>
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

        <div class="mt-6 flex flex-wrap gap-3">
            @if ($hasPlus && $profile?->stripe_customer_id)
                <form method="POST" action="{{ route('writer.billing.portal') }}">
                    @csrf
                    <button class="rounded-2xl bg-[#2D3748] px-5 py-3 font-bold text-white">
                        契約・支払い方法を管理
                    </button>
                </form>
            @elseif (! $hasPlus)
                <form method="POST" action="{{ route('writer.billing.checkout') }}">
                    @csrf
                    <button
                        class="rounded-2xl px-5 py-3 font-bold {{ $stripeConfigured ? 'bg-[#FED7E2] text-[#2D3748]' : 'cursor-not-allowed bg-[#EDF2F7] text-[#A0AEC0]' }}"
                        {{ $stripeConfigured ? '' : 'disabled' }}>
                        月額480円でPlusを始める
                    </button>
                </form>
            @endif
        </div>

        @unless ($stripeConfigured)
            <p class="mt-4 text-sm font-bold text-amber-700">
                現在は決済テストの準備中です。Stripe設定後に申込みできます。
            </p>
        @endunless
    </section>

    <section class="rounded-3xl border border-[#FED7E2] bg-[#FFF7FA] p-6 shadow-sm">
        <p class="text-sm font-bold text-[#A0AEC0]">Oshi-Wiki Plus</p>
        <p class="mt-3 text-3xl font-bold text-[#2D3748]">
            月額{{ number_format($plusPlan['monthly_price']) }}円
            <span class="text-sm">（税込）</span>
        </p>
        <dl class="mt-6 space-y-3 text-sm">
            <div class="flex justify-between"><dt>オリジナルキャラクター</dt><dd class="font-bold">{{ number_format($plusPlan['limits']['original_characters']) }}件</dd></div>
            <div class="flex justify-between"><dt>関係性</dt><dd class="font-bold">{{ number_format($plusPlan['limits']['relationships']) }}件</dd></div>
            <div class="flex justify-between"><dt>保存プロンプト</dt><dd class="font-bold">{{ number_format($plusPlan['limits']['prompts']) }}件</dd></div>
            <div class="flex justify-between"><dt>ストーリー</dt><dd class="font-bold">{{ number_format($plusPlan['limits']['stories']) }}件</dd></div>
        </dl>
    </section>
</div>

<div class="mt-6 rounded-2xl border border-[#E2E8F0] bg-white p-5 text-sm leading-7 text-[#4A5568]">
    <p>有料プランは自動更新です。いつでも解約でき、現在の支払期間終了まではPlusを利用できます。</p>
    <p>申込みにより、<a class="font-bold text-blue-600 underline" href="{{ route('public.terms') }}">利用規約</a>、
        <a class="font-bold text-blue-600 underline" href="{{ route('public.privacy') }}">プライバシーポリシー</a>、
        <a class="font-bold text-blue-600 underline" href="{{ route('public.billing-policy') }}">解約・返金ポリシー</a>に同意したものとします。</p>
</div>

@include('writer.original_characters._layout_end')
