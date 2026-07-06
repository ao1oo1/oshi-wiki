<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Tag\ImportTagCsvRequest;
use App\Services\TagCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class TagCsvImportController extends Controller
{
    public function create(): View
    {
        return view('admin.tags.csv-import');
    }

    public function store(
        ImportTagCsvRequest $request,
        TagCsvImportService $importService
    ): RedirectResponse {
        $result = $importService->import(
            $request->file('csv_file')->getRealPath(),
            $request->input('default_status', 'draft')
        );

        return redirect()
            ->route('admin.tags.csv-import.create')
            ->with('success', "CSVから{$result['imported']}件のタグを登録しました。")
            ->with('csv_errors', $result['errors']);
    }

    public function sample(): Response
    {
        $rows = [
            [
                'name',
                'type',
                'description',
                'status',
            ],
            [
                '学園',
                'genre',
                '学校や学園を舞台にした作品・キャラクター用タグ',
                'published',
            ],
            [
                '忍者',
                'genre',
                '忍者・忍術に関するタグ',
                'published',
            ],
            [
                '一人称あり',
                'meta',
                '一人称情報が登録されているキャラクター用タグ',
                'draft',
            ],
        ];

        return response($this->toCsv($rows), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="oshi-wiki-tag-sample.csv"',
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
