<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\WriterAnalyticsService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WriterAnalyticsController extends Controller
{
    public function index(
        Request $request,
        WriterAnalyticsService $analytics
    ): View {
        $this->authorizeSuperAdmin($request);

        [$start, $end] = $this->period($request);

        return view('admin.analytics.index', [
            'analytics' => $analytics->build($start, $end),
            'startDate' => $start->format('Y-m-d'),
            'endDate' => $end->format('Y-m-d'),
        ]);
    }

    public function export(
        Request $request,
        WriterAnalyticsService $analytics
    ): StreamedResponse {
        $this->authorizeSuperAdmin($request);

        [$start, $end] = $this->period($request);
        $data = $analytics->build($start, $end);
        $filename = 'writer-analytics-'
            .$start->format('Ymd')
            .'-'
            .$end->format('Ymd')
            .'.csv';

        return response()->streamDownload(
            function () use ($data): void {
                $handle = fopen('php://output', 'wb');

                fwrite($handle, "\xEF\xBB\xBF");
                fputcsv($handle, ['カテゴリ', '項目', '値']);

                foreach ($data['cards'] as $key => $value) {
                    fputcsv($handle, [
                        '主要指標',
                        $key,
                        $value,
                    ]);
                }

                foreach ($data['data_usage'] as $row) {
                    fputcsv($handle, [
                        'データ利用',
                        $row['label'].' 件数',
                        $row['count'],
                    ]);
                    fputcsv($handle, [
                        'データ利用',
                        $row['label'].' 文字数',
                        $row['characters'],
                    ]);
                    fputcsv($handle, [
                        'データ利用',
                        $row['label'].' 構成比',
                        $row['share'].'%',
                    ]);
                }

                foreach ($data['billing_alerts'] as $key => $value) {
                    fputcsv($handle, [
                        '課金監視',
                        $key,
                        $value,
                    ]);
                }

                fclose($handle);
            },
            $filename,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    private function authorizeSuperAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->isSuperAdmin(),
            Response::HTTP_FORBIDDEN
        );
    }

    private function period(
        Request $request
    ): array {
        $validated = $request->validate([
            'start' => ['nullable', 'date'],
            'end' => ['nullable', 'date', 'after_or_equal:start'],
        ]);

        $end = isset($validated['end'])
            ? CarbonImmutable::parse($validated['end'])
            : CarbonImmutable::today();

        $start = isset($validated['start'])
            ? CarbonImmutable::parse($validated['start'])
            : $end->subDays(29);

        if ($start->diffInDays($end) > 366) {
            $start = $end->subDays(366);
        }

        return [$start, $end];
    }
}
