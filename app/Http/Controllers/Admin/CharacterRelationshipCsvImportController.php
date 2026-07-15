<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CharacterRelationship\ImportCharacterRelationshipCsvRequest;
use App\Services\CharacterRelationshipCsvImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class CharacterRelationshipCsvImportController extends Controller
{
    public function create(): View
    {
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403);

        return view('admin.character_relationships.csv-import');
    }

    public function store(
        ImportCharacterRelationshipCsvRequest $request,
        CharacterRelationshipCsvImportService $service
    ): RedirectResponse {
        $result = $service->import(
            $request->file('csv_file')->getRealPath(),
            $request->input('default_status', 'draft')
        );

        $message = "CSVから{$result['created']}件を新規登録し、{$result['updated']}件を更新しました。";

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
        abort_unless(auth()->user()?->canManageAllAdminFeatures(), 403);

        $handle = fopen('php://temp', 'r+b');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, [
            'relationship_id',
            'work_id',
            'from_character_id',
            'to_character_id',
            'called_name',
            'relationship',
            'impression',
            'notes',
            'status',
        ], ',', '"', '');
        fputcsv($handle, [
            '',
            '1',
            '10',
            '11',
            '相手への呼び方',
            '友人',
            '信頼している',
            '補足事項',
            'draft',
        ], ',', '"', '');
        rewind($handle);

        return response(stream_get_contents($handle) ?: '', 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="oshi-wiki-character-relationship-sample.csv"',
        ]);
    }
}
