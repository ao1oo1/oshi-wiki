<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CharacterRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class CharacterRelationshipCsvExportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '関係性管理のこの操作は最高管理者のみ可能です。'
        );

        $csv = $this->buildCsv($request);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="oshi-wiki-character-relationships-export.csv"',
        ]);
    }

    private function buildCsv(Request $request): string
    {
        $handle = fopen('php://temp', 'r+b');

        // Excelで文字化けしにくいようにBOMを付ける
        fwrite($handle, "\xEF\xBB\xBF");

        $headers = $this->headers();

        fputcsv($handle, $headers);

        $query = CharacterRelationship::query()
            ->with(['work', 'fromCharacter', 'toCharacter'])
            ->orderBy('id');

        $this->applyFilters($query, $request);

        $query->chunk(500, function ($relationships) use ($handle, $headers) {
            foreach ($relationships as $relationship) {
                fputcsv($handle, $this->row($relationship, $headers));
            }
        });

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }

    private function headers(): array
    {
        $headers = [
            'relationship_id',
            'work_id',
            'from_character_id',
            'to_character_id',
            'called_name',
            'relationship',
            'impression',
            'notes',
            'status',
            'review_status',
            'reviewed_at',
            'reviewed_by',
            'created_at',
            'updated_at',
        ];

        return array_values(array_filter($headers, function (string $header): bool {
            return match ($header) {
                'relationship_id' => true,
                default => Schema::hasColumn('character_relationships', $header),
            };
        }));
    }

    private function row(CharacterRelationship $relationship, array $headers): array
    {
        $row = [];

        foreach ($headers as $header) {
            $row[] = match ($header) {
                'relationship_id' => $relationship->id,
                'created_at', 'updated_at', 'reviewed_at' => optional($relationship->{$header})->format('Y-m-d H:i:s'),
                default => $relationship->{$header} ?? '',
            };
        }

        return $row;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('work_id')) {
            $query->where('work_id', $request->integer('work_id'));
        }

        if ($request->filled('status') && Schema::hasColumn('character_relationships', 'status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));

            $query->where(function (Builder $keywordQuery) use ($keyword) {
                $columns = [
                    'called_name',
                    'relationship',
                    'impression',
                    'notes',
                    'status',
                    'review_status',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('character_relationships', $column)) {
                        $keywordQuery->orWhere($column, 'like', '%' . $keyword . '%');
                    }
                }

                $keywordQuery->orWhereHas('work', function (Builder $workQuery) use ($keyword) {
                    $workQuery->where('title', 'like', '%' . $keyword . '%');
                });

                $keywordQuery->orWhereHas('fromCharacter', function (Builder $characterQuery) use ($keyword) {
                    $characterQuery->where('name', 'like', '%' . $keyword . '%');
                });

                $keywordQuery->orWhereHas('toCharacter', function (Builder $characterQuery) use ($keyword) {
                    $characterQuery->where('name', 'like', '%' . $keyword . '%');
                });
            });
        }
    }
}
