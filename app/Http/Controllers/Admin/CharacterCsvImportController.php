<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Character\ImportCharacterCsvRequest;
use App\Models\Work;
use App\Services\CharacterCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CharacterCsvImportController extends Controller
{
    public function create(): View
    {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, 'キャラクター管理のこの操作は最高管理者のみ可能です。');
        return view('admin.characters.csv-import', [
            'works' => Work::query()->latest()->get(),
        ]);
    }

    public function store(
        ImportCharacterCsvRequest $request,
        CharacterCsvImportService $importService
    ): RedirectResponse {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, 'キャラクター管理のこの操作は最高管理者のみ可能です。');
        $result = $importService->import(
            $request->file('csv_file')->getRealPath(),
            $request->filled('work_id') ? $request->integer('work_id') : null,
            $request->input('default_status', 'draft')
        );

        $created = $result['created'] ?? $result['imported'];
        $updated = $result['updated'] ?? 0;

        $message = "CSVから{$created}件のキャラクターを新規登録し、{$updated}件を更新しました。";

        if ($result['skipped'] > 0) {
            $message .= " 空行{$result['skipped']}件をスキップしました。";
        }

        return redirect()
            ->route('admin.characters.csv-import.create')
            ->with('success', $message)
            ->with('csv_errors', $result['errors']);
    }

    public function sample(): Response
    {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, 'キャラクター管理のこの操作は最高管理者のみ可能です。');
        $csv = $this->sampleCsv();

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="oshi-wiki-character-sample.csv"',
        ]);
    }

    private function sampleCsv(): string
    {
        $rows = [
            [
                'character_id',
                'work_id',
                'name',
                'name_kana',
                'age',
                'affiliation',
                'grade_class',
                'first_person',
                'tone',
                'tone_examples',
                'personality',
                'appearance',
                'background',
                'status',
            ],
            [
                '',
                '',
                '苗字　名前',
                'ミョウジ　ナマエ',
                '20歳',
                '〇〇学園',
                '',
                'わたし',
                "〜ですね。〜だと思います。",
                "「...はい。わかりました。」「あなた、それでも学者？」",
                '真面目な優等生。眼鏡を外すと美人。',
                '長い黒髪。表情は薄い。',
                "幼い頃、〇〇家の養子になる。勉強して一流企業に入るのが夢。",
                'published',
            ],
            [
                '',
                '',
                '山田　太郎',
                'ヤマダ　タロウ',
                '16歳',
                '〇〇学園',
                '1年A組',
                '俺',
                '元気で少し砕けた口調。',
                '「任せとけって！」',
                '明るく行動力がある。',
                '短い黒髪。よく笑う。',
                '地方から進学してきた。',
                'draft',
            ],
        ];

        $handle = fopen('php://temp', 'r+b');

        // Excelで文字化けしにくいようにBOMを付ける
        fwrite($handle, "\xEF\xBB\xBF");

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }
}
