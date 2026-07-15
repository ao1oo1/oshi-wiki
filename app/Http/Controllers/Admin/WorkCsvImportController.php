<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Work\ImportWorkCsvRequest;
use App\Models\Work;
use App\Models\WorkCanonEvent;
use App\Models\WorkTermUsage;
use App\Services\WorkCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class WorkCsvImportController extends Controller
{
    public function create(): View
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '作品管理のこの操作は最高管理者のみ可能です。'
        );

        return view('admin.works.csv-import', [
            'workColumns' => $this->workColumns(),
            'canonEventFields' => $this->relationFields(WorkCanonEvent::class),
            'termUsageFields' => $this->relationFields(WorkTermUsage::class),
        ]);
    }

    public function store(
        ImportWorkCsvRequest $request,
        WorkCsvImportService $importService
    ): RedirectResponse {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '作品管理のこの操作は最高管理者のみ可能です。'
        );

        $result = $importService->import(
            $request->file('csv_file')->getRealPath(),
            $request->input('default_status', 'draft')
        );

        $message = "CSVから{$result['created']}件を新規登録し、{$result['updated']}件を更新しました。";

        if ($result['skipped'] > 0) {
            $message .= " 空行{$result['skipped']}件をスキップしました。";
        }

        return redirect()
            ->route('admin.works.csv-import.create')
            ->with('success', $message)
            ->with('csv_errors', $result['errors']);
    }

    public function sample(): Response
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '作品管理のこの操作は最高管理者のみ可能です。'
        );

        $headers = array_merge(
            ['work_id'],
            $this->workColumns(),
            ['tag_ids', 'tag_names', 'canon_events_json', 'term_usages_json']
        );

        $sample = array_fill_keys($headers, '');

        $sample['title'] = '作品タイトル';
        $sample['title_kana'] = 'サクヒンタイトル';
        $sample['genre'] = '学園ファンタジー';
        $sample['original_media'] = '漫画';
        $sample['official_url'] = 'https://example.com';
        $sample['guideline_url'] = 'https://example.com/guideline';
        $sample['description'] = '作品の概要です。';
        $sample['status'] = 'draft';
        $sample['tag_names'] = '学園,ファンタジー';
        $sample['canon_events_json'] = json_encode(
            [$this->sampleRelationRow(WorkCanonEvent::class, '重要イベント')],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        $sample['term_usages_json'] = json_encode(
            [$this->sampleRelationRow(WorkTermUsage::class, '用語')],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $handle = fopen('php://temp', 'r+b');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $headers, ',', '"', '');
        fputcsv($handle, array_values($sample), ',', '"', '');
        rewind($handle);

        return response(stream_get_contents($handle) ?: '', 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="oshi-wiki-work-sample.csv"',
        ]);
    }

    private function workColumns(): array
    {
        return array_values(array_diff(
            Schema::getColumnListing('works'),
            [
                'id', 'slug', 'review_status', 'created_by', 'updated_by',
                'published_at', 'created_at', 'updated_at', 'deleted_at',
                'helpful_count', 'contributor_application_id',
            ]
        ));
    }

    private function relationFields(string $modelClass): array
    {
        return array_values(array_diff(
            (new $modelClass())->getFillable(),
            ['work_id']
        ));
    }

    private function sampleRelationRow(string $modelClass, string $prefix): array
    {
        $row = [];

        foreach ($this->relationFields($modelClass) as $index => $field) {
            $row[$field] = $field === 'sort_order'
                ? 0
                : "{$prefix}サンプル" . ($index + 1);
        }

        return $row;
    }
}
