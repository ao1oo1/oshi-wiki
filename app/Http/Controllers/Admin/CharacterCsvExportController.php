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

        $headers = $this->headers();

        fputcsv($handle, $headers, ',', '"', '');

        $query = Character::query()
            ->with(['work'])
            ->orderBy('id');

        if (method_exists(Character::class, 'tags')) {
            $query->with('tags');
        }

        $this->applyFilters($query, $request);

        $query->chunk(500, function ($characters) use ($handle, $headers) {
            foreach ($characters as $character) {
                fputcsv($handle, $this->row($character, $headers), ',', '"', '');
            }
        });

        rewind($handle);

        return stream_get_contents($handle) ?: '';
    }

    private function headers(): array
    {
        $headers = [
            'character_id',
            'work_id',
            'character_name',
            'name_kana',
            'real_name',
            'aliases',
            'name_english',
            'gender',
            'age',
            'birthday',
            'height',
            'weight',
            'blood_type',
            'birthplace',
            'species',
            'affiliation',
            'school_grade_class',
            'occupation_position',
            'family_structure',
            'appearance',
            'personality',
            'first_person',
            'second_person',
            'basic_tone',
            'catchphrases',
            'distinctive_speech',
            'tone_by_relationship',
            'short_quote_examples',
            'abilities',
            'background',
            'story_activities',
            'source_title',
            'source_url',
            'source_type',
            'source_reliability',
            'source_checked_at',
            'spoiler_level',
            'status',
            'review_status',
            'reviewed_at',
            'reviewed_by',
            'created_at',
            'updated_at',
            'tag_ids',
            'tag_names',
        ];

        return array_values(array_filter($headers, function (string $header): bool {
            return match ($header) {
                'character_id', 'character_name', 'tag_ids', 'tag_names' => true,
                default => Schema::hasColumn('characters', $header),
            };
        }));
    }

    private function row(Character $character, array $headers): array
    {
        $row = [];

        foreach ($headers as $header) {
            $row[] = match ($header) {
                'character_id' => $character->id,
                'character_name' => $character->name,
                'tag_ids' => $this->tagIds($character),
                'tag_names' => $this->tagNames($character),
                'source_checked_at' => optional($character->source_checked_at)->format('Y-m-d'),
                'created_at', 'updated_at', 'reviewed_at' => optional($character->{$header})->format('Y-m-d H:i:s'),
                default => $character->{$header} ?? '',
            };
        }

        return $row;
    }

    private function tagIds(Character $character): string
    {
        if (! method_exists($character, 'tags') || ! $character->relationLoaded('tags')) {
            return '';
        }

        return $character->tags
            ->pluck('id')
            ->implode(',');
    }

    private function tagNames(Character $character): string
    {
        if (! method_exists($character, 'tags') || ! $character->relationLoaded('tags')) {
            return '';
        }

        return $character->tags
            ->pluck('name')
            ->implode(',');
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
                    'real_name',
                    'aliases',
                    'name_english',
                    'gender',
                    'age',
                    'birthday',
                    'height',
                    'weight',
                    'blood_type',
                    'birthplace',
                    'species',
                    'affiliation',
                    'school_grade_class',
                    'occupation_position',
                    'family_structure',
                    'appearance',
                    'personality',
                    'first_person',
                    'second_person',
                    'basic_tone',
                    'catchphrases',
                    'distinctive_speech',
                    'tone_by_relationship',
                    'short_quote_examples',
                    'abilities',
                    'background',
                    'story_activities',
                    'source_title',
                    'source_url',
                    'status',
                    'review_status',
                ];

                foreach ($columns as $column) {
                    if (Schema::hasColumn('characters', $column)) {
                        $keywordQuery->orWhere($column, 'like', '%' . $keyword . '%');
                    }
                }

                $keywordQuery->orWhereHas('work', function (Builder $workQuery) use ($keyword) {
                    $workQuery->where('title', 'like', '%' . $keyword . '%');
                });

                if (method_exists(Character::class, 'tags')) {
                    $keywordQuery->orWhereHas('tags', function (Builder $tagQuery) use ($keyword) {
                        $tagQuery->where('tags.name', 'like', '%' . $keyword . '%');
                    });
                }
            });
        }
    }
}
