<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-[#2D3748]">Writerアナリティクス</h1>
                <p class="mt-1 text-sm font-bold text-[#718096]">
                    会員・Plus契約・データ使用量・課金状態を集計します。個別の創作本文は表示しません。
                </p>
            </div>

            <a href="{{ route('admin.analytics.export', ['start' => $startDate, 'end' => $endDate]) }}"
               class="inline-flex items-center justify-center rounded-2xl bg-[#2D3748] px-5 py-3 text-sm font-bold text-white hover:opacity-90">
                CSVを出力
            </a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        <form method="GET"
              action="{{ route('admin.analytics.index') }}"
              class="rounded-3xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-end">
                <label class="flex-1 text-sm font-bold text-[#2D3748]">
                    開始日
                    <input type="date"
                           name="start"
                           value="{{ $startDate }}"
                           class="mt-2 w-full rounded-2xl border-[#CBD5E0]">
                </label>

                <label class="flex-1 text-sm font-bold text-[#2D3748]">
                    終了日
                    <input type="date"
                           name="end"
                           value="{{ $endDate }}"
                           class="mt-2 w-full rounded-2xl border-[#CBD5E0]">
                </label>

                <button class="rounded-2xl bg-[#FED7E2] px-6 py-3 font-bold text-[#2D3748]">
                    集計する
                </button>
            </div>

            <div class="mt-4 flex flex-wrap gap-2 text-sm font-bold">
                @foreach ([
                    7 => '過去7日',
                    30 => '過去30日',
                    90 => '過去3か月',
                    180 => '過去6か月',
                    365 => '過去1年',
                ] as $days => $label)
                    <a href="{{ route('admin.analytics.index', [
                        'start' => now()->subDays($days - 1)->format('Y-m-d'),
                        'end' => now()->format('Y-m-d'),
                    ]) }}"
                       class="rounded-full border border-[#FED7E2] px-4 py-2 text-[#2D3748] hover:bg-[#FFF1F5]">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </form>

        @if ($analytics['billing_alerts']['total'] > 0)
            <section class="rounded-3xl border-2 border-red-300 bg-red-50 p-6">
                <h2 class="text-xl font-bold text-red-800">課金状態に不整合があります</h2>
                <div class="mt-4 grid gap-3 md:grid-cols-3">
                    <div class="rounded-2xl bg-white p-4">
                        <p class="text-sm font-bold text-red-700">Stripe契約あり・Plus未反映</p>
                        <p class="mt-2 text-3xl font-bold text-red-800">
                            {{ number_format($analytics['billing_alerts']['subscription_without_plus']) }}件
                        </p>
                    </div>
                    <div class="rounded-2xl bg-white p-4">
                        <p class="text-sm font-bold text-red-700">Plus表示・Stripe契約なし</p>
                        <p class="mt-2 text-3xl font-bold text-red-800">
                            {{ number_format($analytics['billing_alerts']['plus_without_subscription']) }}件
                        </p>
                    </div>
                    <div class="rounded-2xl bg-white p-4">
                        <p class="text-sm font-bold text-red-700">Webhook処理失敗</p>
                        <p class="mt-2 text-3xl font-bold text-red-800">
                            {{ number_format($analytics['billing_alerts']['failed_webhooks']) }}件
                        </p>
                    </div>
                </div>
            </section>
        @endif

        <section>
            <h2 class="mb-4 text-xl font-bold text-[#2D3748]">主要指標</h2>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['Writer総登録者数', $analytics['cards']['total_writers'], '人'],
                    ['有効Writer会員', $analytics['cards']['active_writers'], '人'],
                    ['期間内の新規登録', $analytics['cards']['new_writers'], '人'],
                    ['Oshi-Wiki Plus', $analytics['cards']['plus_members'], '人'],
                    ['Plus加入率', $analytics['cards']['plus_rate'], '%'],
                    ['解約予約中', $analytics['cards']['canceling'], '人'],
                    ['支払い猶予中', $analytics['cards']['past_due'], '人'],
                    ['推定月間経常収益', $analytics['cards']['estimated_mrr'], '円'],
                ] as [$label, $value, $suffix])
                    <article class="rounded-3xl border border-[#E2E8F0] bg-white p-5 shadow-sm">
                        <p class="text-sm font-bold text-[#718096]">{{ $label }}</p>
                        <p class="mt-3 text-3xl font-bold text-[#2D3748]">
                            {{ number_format($value, is_float($value) ? 1 : 0) }}
                            <span class="text-sm">{{ $suffix }}</span>
                        </p>
                    </article>
                @endforeach
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
                <div class="mb-5">
                    <h2 class="text-xl font-bold text-[#2D3748]">Writer登録者数の推移</h2>
                    <p class="mt-1 text-sm font-bold text-[#A0AEC0]">新規登録と累計登録者数</p>
                </div>
                <div class="analytics-chart h-72"
                     data-chart='@json($analytics["writer_trend"])'
                     data-series="new,cumulative"
                     data-labels="新規,累計"></div>
            </section>

            <section class="rounded-3xl border-4 border-[#FED7E2] bg-white p-6 shadow-sm">
                <div class="mb-5">
                    <h2 class="text-xl font-bold text-[#2D3748]">Plus会員の推移</h2>
                    <p class="mt-1 text-sm font-bold text-[#A0AEC0]">
                        課金プロフィール作成日を基準とした新規・累計
                    </p>
                </div>
                <div class="analytics-chart h-72"
                     data-chart='@json($analytics["plus_trend"])'
                     data-series="new,cumulative"
                     data-labels="新規Plus,累計Plus"></div>
            </section>
        </div>

        <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-[#2D3748]">データ使用状況</h2>
                    <p class="mt-1 text-sm font-bold text-[#A0AEC0]">
                        件数・文字数・推定保存量のみを集計します。
                    </p>
                </div>
                <p class="text-sm font-bold text-[#718096]">
                    総件数 {{ number_format($analytics['cards']['total_data_count']) }}件 /
                    総文字数 {{ number_format($analytics['cards']['total_characters']) }}文字 /
                    推定 {{ number_format($analytics['cards']['estimated_bytes'] / 1024, 1) }}KB
                </p>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="w-full min-w-[700px] text-left">
                    <thead class="bg-[#FFF7FA] text-sm text-[#718096]">
                        <tr>
                            <th class="rounded-l-2xl px-5 py-4">項目</th>
                            <th class="px-5 py-4">登録件数</th>
                            <th class="px-5 py-4">構成比</th>
                            <th class="px-5 py-4">文字数</th>
                            <th class="rounded-r-2xl px-5 py-4">推定容量</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($analytics['data_usage'] as $row)
                            <tr class="border-b border-[#EDF2F7]">
                                <td class="px-5 py-4 font-bold text-[#2D3748]">{{ $row['label'] }}</td>
                                <td class="px-5 py-4">{{ number_format($row['count']) }}件</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-2 w-28 overflow-hidden rounded-full bg-[#EDF2F7]">
                                            <div class="h-full rounded-full bg-[#FED7E2]"
                                                 style="width: {{ min(100, $row['share']) }}%"></div>
                                        </div>
                                        <span class="font-bold">{{ $row['share'] }}%</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4">{{ number_format($row['characters']) }}</td>
                                <td class="px-5 py-4">{{ number_format($row['estimated_bytes'] / 1024, 1) }}KB</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#2D3748]">無料会員とPlus会員の平均利用数</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($analytics['plan_comparison'] as $row)
                        <div class="rounded-2xl bg-[#F8FAFC] p-4">
                            <p class="font-bold text-[#2D3748]">{{ $row['label'] }}</p>
                            <div class="mt-3 grid grid-cols-2 gap-3 text-center">
                                <div class="rounded-xl bg-white p-3">
                                    <p class="text-xs font-bold text-[#A0AEC0]">無料</p>
                                    <p class="mt-1 text-xl font-bold">{{ $row['free_average'] }}件</p>
                                </div>
                                <div class="rounded-xl bg-[#FFF1F5] p-3">
                                    <p class="text-xs font-bold text-[#A05A70]">Plus</p>
                                    <p class="mt-1 text-xl font-bold text-[#D95F82]">{{ $row['plus_average'] }}件</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="rounded-3xl border border-[#E2E8F0] bg-white p-6 shadow-sm">
                <h2 class="text-xl font-bold text-[#2D3748]">無料会員の上限到達状況</h2>
                <p class="mt-1 text-sm font-bold text-[#A0AEC0]">
                    Plusへの案内や上限設定の見直しに利用できます。
                </p>
                <div class="mt-5 space-y-4">
                    @foreach ($analytics['limit_pressure'] as $row)
                        <div class="rounded-2xl border border-[#EDF2F7] p-4">
                            <div class="flex items-center justify-between">
                                <p class="font-bold text-[#2D3748]">{{ $row['label'] }}</p>
                                <p class="text-sm font-bold text-[#718096]">上限 {{ number_format($row['limit']) }}件</p>
                            </div>
                            <div class="mt-3 grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-amber-50 p-3">
                                    <p class="text-xs font-bold text-amber-700">80%以上</p>
                                    <p class="mt-1 text-2xl font-bold text-amber-900">{{ number_format($row['over_80']) }}人</p>
                                </div>
                                <div class="rounded-xl bg-red-50 p-3">
                                    <p class="text-xs font-bold text-red-700">上限到達</p>
                                    <p class="mt-1 text-2xl font-bold text-red-900">{{ number_format($row['reached']) }}人</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        <section class="rounded-3xl border border-dashed border-[#CBD5E0] bg-[#F8FAFC] p-6">
            <h2 class="text-lg font-bold text-[#2D3748]">次の分析機能</h2>
            <p class="mt-2 text-sm font-bold leading-7 text-[#718096]">
                DAU・MAU、7日／30日継続率、料金ページ閲覧から決済までのファネル、
                正確な加入・解約推移は、Phase 2の利用イベント記録導入後に追加します。
            </p>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.analytics-chart').forEach((element) => {
                const rows = JSON.parse(element.dataset.chart || '[]');
                const keys = (element.dataset.series || '').split(',');
                const labels = (element.dataset.labels || '').split(',');

                if (!rows.length) {
                    element.innerHTML = '<div class="flex h-full items-center justify-center text-sm font-bold text-[#A0AEC0]">データがありません</div>';
                    return;
                }

                const width = 760;
                const height = 280;
                const padding = { top: 24, right: 24, bottom: 46, left: 50 };
                const maxValue = Math.max(
                    1,
                    ...rows.flatMap(row => keys.map(key => Number(row[key] || 0)))
                );
                const x = index => padding.left + (
                    (width - padding.left - padding.right)
                    * (rows.length === 1 ? 0.5 : index / (rows.length - 1))
                );
                const y = value => height - padding.bottom - (
                    (height - padding.top - padding.bottom)
                    * Number(value || 0) / maxValue
                );
                const lineColors = ['#D95F82', '#2D3748'];

                let svg = `<svg viewBox="0 0 ${width} ${height}" class="h-full w-full" role="img">`;

                for (let step = 0; step <= 4; step++) {
                    const value = Math.round(maxValue * step / 4);
                    const lineY = y(value);
                    svg += `<line x1="${padding.left}" y1="${lineY}" x2="${width - padding.right}" y2="${lineY}" stroke="#EDF2F7" stroke-width="1"/>`;
                    svg += `<text x="${padding.left - 8}" y="${lineY + 4}" text-anchor="end" font-size="11" fill="#A0AEC0">${value.toLocaleString()}</text>`;
                }

                keys.forEach((key, seriesIndex) => {
                    const points = rows.map((row, index) => `${x(index)},${y(row[key])}`).join(' ');
                    svg += `<polyline points="${points}" fill="none" stroke="${lineColors[seriesIndex]}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>`;
                });

                const labelInterval = Math.max(1, Math.ceil(rows.length / 8));
                rows.forEach((row, index) => {
                    if (index % labelInterval === 0 || index === rows.length - 1) {
                        svg += `<text x="${x(index)}" y="${height - 18}" text-anchor="middle" font-size="11" fill="#718096">${row.label}</text>`;
                    }
                });

                svg += '</svg>';
                svg += '<div class="mt-2 flex justify-center gap-5 text-xs font-bold text-[#718096]">';
                labels.forEach((label, index) => {
                    svg += `<span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full" style="background:${lineColors[index]}"></span>${label}</span>`;
                });
                svg += '</div>';

                element.innerHTML = svg;
            });
        });
    </script>
</x-app-layout>
