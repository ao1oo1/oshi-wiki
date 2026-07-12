<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class TagCsvExportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            'タグ管理のこの操作は最高管理者のみ可能です。'
        );

        $csv = $this->buildCsv($request);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="oshi-wiki-tags-export.csv"',
        ]);
    }

    private function buildCsv(Request $request): string
    {
        $handle = fopen('php://temp', 'r+b');

        // Excelで文字化けしにくいようにBOMを付ける
        fwrite($handle, "\xEF\xBB\xBF");

        $headers = $this->headers();

        fputcsv($handle, $headers);

        $query = Tag::query()->orderBy('id');

        $this->applyFilters($query, $request);

        $query->chunk(500, function ($tags) use ($handle, $headers) {
            foreach ($tags as $tag) {
                fputcsv($handle, $this->row($tag, $headers));
            }
        });

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }

    private function headers(): array
    {
        $columns = Schema::getColumnListing('tags');

        $headers = [];

        // IDを先頭に固定
        $headers[] = 'tag_id';

        foreach ($columns as $column) {
            if ($column === 'id') {
                continue;
            }

            $headers[] = $column;
        }

        return $headers;
    }

    private function row(Tag $tag, array $headers): array
    {
        $row = [];

        foreach ($headers as $header) {
            $row[] = match ($header) {
                'tag_id' => $tag->id,
                'created_at', 'updated_at', 'deleted_at' => optional($tag->{$header})->format('Y-m-d H:i:s'),
                default => $tag->{$header} ?? '',
            };
        }

        return $row;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('id')) {
            $query->where('id', $request->integer('id'));
        }

        if ($request->filled('tag_id')) {
            $query->where('id', $request->integer('tag_id'));
        }

        if ($request->filled('type') && Schema::hasColumn('tags', 'type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status') && Schema::hasColumn('tags', 'status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));

            $query->where(function (Builder $keywordQuery) use ($keyword) {
                foreach (Schema::getColumnListing('tags') as $column) {
                    if (in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'], true)) {
                        continue;
                    }

                    $keywordQuery->orWhere($column, 'like', '%' . $keyword . '%');
                }
            });
        }

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));

            $query->where(function (Builder $keywordQuery) use ($keyword) {
                foreach (Schema::getColumnListing('tags') as $column) {
                    if (in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'], true)) {
                        continue;
                    }

                    $keywordQuery->orWhere($column, 'like', '%' . $keyword . '%');
                }
            });
        }
    }
}
