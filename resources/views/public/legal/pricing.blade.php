@include('public.legal._layout_start', ['title' => '料金プラン'])

<div class="grid gap-6 md:grid-cols-2">
@foreach ($plans as $slug => $plan)
<section class="rounded-3xl border {{ $slug === 'plus' ? 'border-[#FED7E2] bg-[#FFF7FA]' : 'border-[#E2E8F0]' }} p-6">
    <p class="text-sm font-bold text-[#A0AEC0]">{{ strtoupper($slug) }}</p>
    <h2 class="mt-2 text-2xl font-bold text-[#2D3748]">{{ $plan['name'] }}</h2>
    @if ($slug === 'plus')
        <p class="mt-4 text-3xl font-bold">月額{{ number_format($plan['monthly_price']) }}円<span class="text-sm">（税込）</span></p>
        <p class="mt-2 text-sm">v6.1で年額{{ number_format($plan['yearly_price']) }}円を追加予定</p>
    @else
        <p class="mt-4 text-3xl font-bold">0円</p>
    @endif
    <dl class="mt-6 space-y-3">
        <div class="flex justify-between gap-4"><dt>オリジナルキャラクター</dt><dd class="font-bold">{{ number_format($plan['limits']['original_characters']) }}件</dd></div>
        <div class="flex justify-between gap-4"><dt>関係性</dt><dd class="font-bold">{{ number_format($plan['limits']['relationships']) }}件</dd></div>
        <div class="flex justify-between gap-4"><dt>保存プロンプト</dt><dd class="font-bold">{{ number_format($plan['limits']['prompts']) }}件</dd></div>
        <div class="flex justify-between gap-4"><dt>ストーリー</dt><dd class="font-bold">{{ number_format($plan['limits']['stories']) }}件</dd></div>
    </dl>
</section>
@endforeach
</div>

<div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-amber-900">
    Plusの申込み機能はStripe接続後に公開します。現在は料金・規約・データ取扱いの基盤を準備中です。
</div>

@include('public.legal._layout_end')
