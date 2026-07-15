<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Work;
use App\Models\WorkCanonEvent;
use App\Models\WorkTermUsage;
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

        $filename = $request->filled('work_id')
            ? 'oshi-wiki-work-' . $request->integer('work_id') . '-export.csv'
            : 'oshi-wiki-works-export.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function headers(): array
    {
        $headers = ['work_id'];

        foreach (Schema::getColumnListing('works') as $column) {
            if ($column !== 'id') {
                $headers[] = $column;
            }
        }

        return array_merge($headers, [
            'tag_ids',
            'tag_names',
            'canon_events_json',
            'term_usages_json',
        ]);
    }

    private function buildCsv(Request $request): string
    {
        $handle = fopen('php://temp', 'r+b');
        fwrite($handle, "\xEF\xBB\xBF");

        $headers = $this->headers();
        fputcsv($handle, $headers, ',', '"', '');

        $query = Work::query()
            ->with(['tags', 'canonEvents', 'termUsages'])
            ->orderBy('id');

        $this->applyFilters($query, $request);

        $query->chunk(500, function ($works) use ($handle, $headers): void {
            foreach ($works as $work) {
                fputcsv($handle, $this->row($work, $headers), ',', '"', '');
            }
        });

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }

    private function row(Work $work, array $headers): array
    {
        return array_map(function (string $header) use ($work) {
            return match ($header) {
                'work_id' => $work->id,
                'tag_ids' => $work->tags->pluck('id')->implode(','),
                'tag_names' => $work->tags->pluck('name')->implode(','),
                'canon_events_json' => $this->relationJson($work->canonEvents, WorkCanonEvent::class),
                'term_usages_json' => $this->relationJson($work->termUsages, WorkTermUsage::class),
                'created_at', 'updated_at', 'published_at', 'deleted_at' =>
                    optional($work->{$header})->format('Y-m-d H:i:s'),
                default => $work->{$header} ?? '',
            };
        }, $headers);
    }

    private function relationJson($models, string $modelClass): string
    {
        $fillable = (new $modelClass())->getFillable();
        $fillable = array_values(array_diff($fillable, ['work_id']));

        $rows = $models
            ->sortBy('sort_order')
            ->values()
            ->map(fn ($model) => collect($model->only($fillable))
                ->reject(fn ($value) => $value === null || $value === '')
                ->all())
            ->all();

        return json_encode(
            $rows,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        ) ?: '[]';
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('work_id')) {
            $query->whereKey($request->integer('work_id'));
        }

        if ($request->filled('id')) {
            $query->whereKey($request->integer('id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('tag_id')) {
            $query->whereHas('tags', fn (Builder $tagQuery) =>
                $tagQuery->where('tags.id', $request->integer('tag_id'))
            );
        }

        if ($request->filled('exact_keyword')) {
            $exactKeyword = trim((string) $request->input('exact_keyword'));

            $query->where(function (Builder $exactQuery) use ($exactKeyword): void {
                $exactQuery->where('title', $exactKeyword)
                    ->orWhere('title_kana', $exactKeyword)
                    ->orWhere('genre', $exactKeyword)
                    ->orWhere('original_media', $exactKeyword);
            });
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));

            $query->where(function (Builder $keywordQuery) use ($keyword): void {
                foreach (Schema::getColumnListing('works') as $column) {
                    if (in_array($column, [
                        'id', 'created_by', 'updated_by',
                        'created_at', 'updated_at', 'deleted_at',
                    ], true)) {
                        continue;
                    }

                    $keywordQuery->orWhere($column, 'like', '%' . $keyword . '%');
                }

                $keywordQuery->orWhereHas('tags', fn (Builder $tagQuery) =>
                    $tagQuery->where('tags.name', 'like', '%' . $keyword . '%')
                );
            });
        }
    }
}
