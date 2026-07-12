<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Work;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class WorkCsvExportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '作品管理のこの操作は最高管理者のみ可能です。'
        );

        $csv = $this->buildCsv($request);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="oshi-wiki-works-export.csv"',
        ]);
    }

    private function buildCsv(Request $request): string
    {
        $handle = fopen('php://temp', 'r+b');

        // Excelで文字化けしにくいようにBOMを付ける
        fwrite($handle, "\xEF\xBB\xBF");

        $headers = $this->headers();

        fputcsv($handle, $headers);

        $query = Work::query()
            ->orderBy('id');

        if (method_exists(Work::class, 'tags')) {
            $query->with('tags');
        }

        $this->applyFilters($query, $request);

        $query->chunk(500, function ($works) use ($handle, $headers) {
            foreach ($works as $work) {
                fputcsv($handle, $this->row($work, $headers));
            }
        });

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }

    private function headers(): array
    {
        $columns = Schema::getColumnListing('works');

        $headers = [];

        // 作品IDを先頭に固定
        $headers[] = 'work_id';

        foreach ($columns as $column) {
            if ($column === 'id') {
                continue;
            }

            $headers[] = $column;
        }

        if (method_exists(Work::class, 'tags')) {
            $headers[] = 'tag_ids';
            $headers[] = 'tag_names';
        }

        return $headers;
    }

    private function row(Work $work, array $headers): array
    {
        $row = [];

        foreach ($headers as $header) {
            $row[] = match ($header) {
                'work_id' => $work->id,
                'tag_ids' => $this->tagIds($work),
                'tag_names' => $this->tagNames($work),
                'created_at', 'updated_at', 'published_at', 'deleted_at' => optional($work->{$header})->format('Y-m-d H:i:s'),
                default => $work->{$header} ?? '',
            };
        }

        return $row;
    }

    private function tagIds(Work $work): string
    {
        if (! method_exists($work, 'tags') || ! $work->relationLoaded('tags')) {
            return '';
        }

        return $work->tags
            ->pluck('id')
            ->implode(',');
    }

    private function tagNames(Work $work): string
    {
        if (! method_exists($work, 'tags') || ! $work->relationLoaded('tags')) {
            return '';
        }

        return $work->tags
            ->pluck('name')
            ->implode(',');
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('work_id')) {
            $query->where('id', $request->integer('work_id'));
        }

        if ($request->filled('id')) {
            $query->where('id', $request->integer('id'));
        }

        if ($request->filled('status') && Schema::hasColumn('works', 'status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('tag_id') && method_exists(Work::class, 'tags')) {
            $query->whereHas('tags', function (Builder $tagQuery) use ($request) {
                $tagQuery->where('tags.id', $request->integer('tag_id'));
            });
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));

            $query->where(function (Builder $keywordQuery) use ($keyword) {
                foreach (Schema::getColumnListing('works') as $column) {
                    if (in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'], true)) {
                        continue;
                    }

                    $keywordQuery->orWhere($column, 'like', '%' . $keyword . '%');
                }

                if (method_exists(Work::class, 'tags')) {
                    $keywordQuery->orWhereHas('tags', function (Builder $tagQuery) use ($keyword) {
                        $tagQuery->where('tags.name', 'like', '%' . $keyword . '%');
                    });
                }
            });
        }
    }
}
