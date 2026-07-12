<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CharacterRelationship\ImportCharacterRelationshipCsvRequest;
use App\Models\Work;
use App\Services\CharacterRelationshipCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CharacterRelationshipCsvImportController extends Controller
{
    public function create(): View
    {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, '関係性管理のこの操作は最高管理者のみ可能です。');

        return view('admin.character_relationships.csv-import', [
            'works' => Work::query()->latest()->get(),
        ]);
    }

    public function store(
        ImportCharacterRelationshipCsvRequest $request,
        CharacterRelationshipCsvImportService $importService
    ): RedirectResponse {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, '関係性管理のこの操作は最高管理者のみ可能です。');

        $result = $importService->import(
            $request->file('csv_file')->getRealPath(),
            $request->filled('work_id') ? $request->integer('work_id') : null,
            $request->input('default_status', 'draft')
        );

        $message = "CSVから{$result['imported']}件の関係性を登録しました。";

        if ($result['skipped'] > 0) {
            $message .= " 空行{$result['skipped']}件をスキップしました。";
        }

        return redirect()
            ->route('admin.character-relationships.csv-import.create')
            ->with('success', $message)
            ->with('csv_errors', $result['errors']);
    }

    public function sample(): Response
    {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403, '関係性管理のこの操作は最高管理者のみ可能です。');

        $csv = $this->sampleCsv();

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="oshi-wiki-character-relationship-sample.csv"',
        ]);
    }

    private function sampleCsv(): string
    {
        $rows = [
            [
                'work_id',
                'from_character_id',
                'to_character_id',
                'called_name',
                'relationship',
                'impression',
                'notes',
                'status',
            ],
            [
                '',
                '1',
                '2',
                '〇〇さん',
                '幼なじみ',
                '信頼しているが、素直に態度へ出せない。',
                '同じ作品に登録済みのキャラクターIDを指定してください。',
                'published',
            ],
            [
                '',
                '2',
                '1',
                '君',
                'ライバル',
                '実力を認めており、競い合う相手として意識している。',
                '',
                'draft',
            ],
        ];

        $handle = fopen('php://temp', 'r+b');

        fwrite($handle, "\xEF\xBB\xBF");

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }
}
