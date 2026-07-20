@include('public.legal._layout_start', ['title' => '特定商取引法に基づく表記'])

@php($legal = config('billing.legal'))
<div class="overflow-x-auto">
<table class="w-full border-collapse text-left">
<tbody>
<tr class="border-b"><th class="w-1/3 py-4 pr-4">販売事業者</th><td class="py-4">{{ $legal['operator_name'] }}</td></tr>
<tr class="border-b"><th class="py-4 pr-4">所在地</th><td class="py-4">{{ $legal['address_disclosure'] }}</td></tr>
<tr class="border-b"><th class="py-4 pr-4">電話番号</th><td class="py-4">{{ $legal['phone_disclosure'] }}</td></tr>
<tr class="border-b"><th class="py-4 pr-4">問い合わせ</th><td class="py-4">{{ $legal['contact_email'] ?: 'お問い合わせフォームからご連絡ください。' }}</td></tr>
<tr class="border-b"><th class="py-4 pr-4">販売価格</th><td class="py-4">料金プランページに税込価格を表示します。</td></tr>
<tr class="border-b"><th class="py-4 pr-4">追加料金</th><td class="py-4">インターネット接続料金・通信料金は利用者負担です。</td></tr>
<tr class="border-b"><th class="py-4 pr-4">支払方法・時期</th><td class="py-4">クレジットカード。申込み時に決済し、以後は選択した請求周期で自動更新します。</td></tr>
<tr class="border-b"><th class="py-4 pr-4">提供時期</th><td class="py-4">決済確認後、原則として直ちに利用できます。</td></tr>
<tr class="border-b"><th class="py-4 pr-4">解約</th><td class="py-4">いつでも解約可能です。現在の支払期間終了時に停止します。</td></tr>
<tr><th class="py-4 pr-4">返金</th><td class="py-4">利用者都合による返金は原則行いません。詳細は解約・返金ポリシーをご確認ください。</td></tr>
</tbody>
</table>
</div>

@include('public.legal._layout_end')
