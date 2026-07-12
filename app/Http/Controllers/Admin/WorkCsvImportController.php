<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Work\ImportWorkCsvRequest;
use App\Services\WorkCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class WorkCsvImportController extends Controller
{
    public function create(): View
    {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, '作品管理のこの操作は最高管理者のみ可能です。');
        return view('admin.works.csv-import');
    }

    public function store(
        ImportWorkCsvRequest $request,
        WorkCsvImportService $importService
    ): RedirectResponse {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, '作品管理のこの操作は最高管理者のみ可能です。');
        $result = $importService->import(
            $request->file('csv_file')->getRealPath(),
            $request->input('default_status', 'draft')
        );

        return redirect()
            ->route('admin.works.csv-import.create')
            ->with('success', "CSVから{$result['imported']}件の作品を登録しました。")
            ->with('csv_errors', $result['errors']);
    }

    public function sample(): Response
    {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, '作品管理のこの操作は最高管理者のみ可能です。');
        $rows = [
            [
                'title',
                'title_kana',
                'genre',
                'original_media',
                'official_url',
                'guideline_url',
                'description',
                'status',
            ],
            [
                '作品タイトル',
                'サクヒンタイトル',
                'ファンタジー',
                '漫画',
                'https://example.com',
                'https://example.com/guideline',
                "作品の概要です。世界観や注意事項を記載します。",
                'published',
            ],
            [
                'サンプル作品',
                'サンプルサクヒン',
                '学園',
                '小説',
                '',
                '',
                'サンプル説明です。',
                'draft',
            ],
        ];

        return response($this->toCsv($rows), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="oshi-wiki-work-sample.csv"',
        ]);
    }

    private function toCsv(array $rows): string
    {
        $handle = fopen('php://temp', 'r+b');

        // Excel文字化け対策
        fwrite($handle, "\xEF\xBB\xBF");

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }
}
