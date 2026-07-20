<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">収益管理：クリック集計</h2>
    </x-slot>

    <div class="p-6">
        @include('admin.partials.flash')

        <div class="mb-5 flex flex-wrap gap-3">
            <a href="{{ route('admin.monetization.services.index') }}" class="oshi-btn oshi-btn-sub">
                配信・販売サービス
            </a>
            <a href="{{ route('admin.monetization.programs.index') }}" class="oshi-btn oshi-btn-sub">
                提携プログラム
            </a>
            <a href="{{ route('admin.monetization.analytics.index') }}" class="oshi-btn">
                クリック集計
            </a>
        </div>

        <div class="oshi-card admin-index-shell">
            <div class="mb-6 flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-[#2D3748]">
                        クリック集計
                    </h1>
                    <p class="mt-2 text-sm leading-7 text-[#718096]">
                        生のIPアドレスやユーザーエージェントは保存していません。
                        短時間の同一クリックは重複として集計から除外します。
                    </p>
                </div>
                <form
                    method="POST"
                    action="{{ route('admin.monetization.links.verify-all') }}"
                    onsubmit="return confirm('有効な商品リンクをすべて検証しますか？');"
                >
                    @csrf
                    <button type="submit" class="oshi-btn">
                        商品リンクを全件確認
                    </button>
                </form>
            </div>


            <form method="GET" action="{{ route('admin.monetization.analytics.index') }}" class="mb-6 rounded-3xl border border-[#E2E8F0] bg-[#F7FAFC] p-5">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <label for="work_id" class="oshi-label">作品</label>
                        <select id="work_id" name="work_id" class="oshi-input">
                            <option value="">すべて</option>
                            @foreach ($works as $work)
                                <option value="{{ $work->id }}" @selected((int) $selectedWorkId === (int) $work->id)>
                                    {{ $work->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="service_id" class="oshi-label">サービス</label>
                        <select id="service_id" name="service_id" class="oshi-input">
                            <option value="">すべて</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected((int) $selectedServiceId === (int) $service->id)>
                                    {{ $service->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date_from" class="oshi-label">開始日</label>
                        <input id="date_from" type="date" name="date_from" value="{{ $dateFrom }}" class="oshi-input">
                    </div>
                    <div>
                        <label for="date_to" class="oshi-label">終了日</label>
                        <input id="date_to" type="date" name="date_to" value="{{ $dateTo }}" class="oshi-input">
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <button type="submit" class="oshi-btn">絞り込む</button>
                    <a href="{{ route('admin.monetization.analytics.index') }}" class="oshi-btn oshi-btn-sub">クリア</a>
                </div>
            </form>

            <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="rounded-2xl bg-[#FFF7FA] p-5">
                    <p class="text-sm text-[#718096]">クリック数</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($totals['clicks']) }}</p>
                </div>
                <div class="rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="text-sm text-[#718096]">推定訪問者数</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($totals['visitors']) }}</p>
                </div>
                <div class="rounded-2xl bg-[#F7FAFC] p-5">
                    <p class="text-sm text-[#718096]">クリックされたリンク数</p>
                    <p class="mt-2 text-3xl font-bold">{{ number_format($totals['links']) }}</p>
                </div>
            </div>

            <h2 class="mb-4 text-xl font-bold">クリック上位リンク</h2>
            <div class="mb-8 overflow-x-auto rounded-3xl border border-[#E2E8F0]">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-[#FFF5F7]">
                        <tr>
                            <th class="px-4 py-3">作品</th>
                            <th class="px-4 py-3">サービス</th>
                            <th class="px-4 py-3">商品</th>
                            <th class="px-4 py-3 text-center">クリック</th>
                            <th class="px-4 py-3 text-center">訪問者</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topLinks as $row)
                            <tr class="border-t border-[#E2E8F0]">
                                <td class="px-4 py-4">{{ $row->link?->work?->title ?: '削除済み' }}</td>
                                <td class="px-4 py-4">{{ $row->link?->service?->name ?: '削除済み' }}</td>
                                <td class="px-4 py-4">{{ $row->link?->title ?: $row->link?->product_code ?: '—' }}</td>
                                <td class="px-4 py-4 text-center">{{ number_format($row->click_count) }}</td>
                                <td class="px-4 py-4 text-center">{{ number_format($row->visitor_count) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-8 text-center text-[#718096]">クリックデータはありません。</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <h2 class="mb-4 text-xl font-bold">クリック履歴</h2>
            <div class="overflow-x-auto rounded-3xl border border-[#E2E8F0]">
                <table class="w-full min-w-[980px] text-left text-sm">
                    <thead class="bg-[#FFF5F7]">
                        <tr>
                            <th class="px-4 py-3">日時</th>
                            <th class="px-4 py-3">作品</th>
                            <th class="px-4 py-3">サービス</th>
                            <th class="px-4 py-3">商品</th>
                            <th class="px-4 py-3">参照元</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($clicks as $click)
                            <tr class="border-t border-[#E2E8F0]">
                                <td class="px-4 py-4 whitespace-nowrap">{{ $click->clicked_at?->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-4">{{ $click->work?->title ?: '削除済み' }}</td>
                                <td class="px-4 py-4">{{ $click->service?->name ?: '削除済み' }}</td>
                                <td class="px-4 py-4">{{ $click->link?->title ?: $click->link?->product_code ?: '—' }}</td>
                                <td class="px-4 py-4 font-mono text-xs">
                                    {{ $click->referer_host ?: '—' }}{{ $click->referer_path ?: '' }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-8 text-center text-[#718096]">クリック履歴はありません。</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $clicks->links() }}</div>
        </div>
    </div>
</x-app-layout>
