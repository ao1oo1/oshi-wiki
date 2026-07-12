<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class CharacterCsvExportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            'キャラクター管理のこの操作は最高管理者のみ可能です。'
        );

        $csv = $this->buildCsv($request);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="oshi-wiki-characters-export.csv"',
        ]);
    }

    private function buildCsv(Request $request): string
    {
        $handle = fopen('php://temp', 'r+b');

        // Excelで文字化けしにくいようにBOMを付ける
        fwrite($handle, "\xEF\xBB\xBF");

        fputcsv($handle, [
            'character_id',
            'character_name',
            'work_id',
        ]);

        $query = Character::query()
            ->with('work')
            ->orderBy('id');

        $this->applyFilters($query, $request);

        $query->chunk(500, function ($characters) use ($handle) {
            foreach ($characters as $character) {
                fputcsv($handle, [
                    $character->id,
                    $character->name,
                    $character->work_id,
                ]);
            }
        });

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('work_id')) {
            $query->where('work_id', $request->integer('work_id'));
        }

        if ($request->filled('status') && Schema::hasColumn('characters', 'status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('tag_id') && method_exists(Character::class, 'tags')) {
            $query->whereHas('tags', function (Builder $tagQuery) use ($request) {
                $tagQuery->where('tags.id', $request->integer('tag_id'));
            });
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));

            $query->where(function (Builder $keywordQuery) use ($keyword) {
                $columns = [
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
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('characters', $column)) {
                        $keywordQuery->orWhere($column, 'like', '%' . $keyword . '%');
                    }
                }

                $keywordQuery->orWhereHas('work', function (Builder $workQuery) use ($keyword) {
                    $workQuery->where('title', 'like', '%' . $keyword . '%');
                });
            });
        }
    }
}
