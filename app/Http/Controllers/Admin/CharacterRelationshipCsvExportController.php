<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CharacterRelationship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CharacterRelationshipCsvExportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            '関係性管理のこの操作は最高管理者のみ可能です。'
        );

        $handle = fopen('php://temp', 'r+b');
        fwrite($handle, "\xEF\xBB\xBF");

        $headers = [
            'relationship_id',
            'work_id',
            'work_title',
            'from_character_id',
            'from_character_name',
            'to_character_id',
            'to_character_name',
            'called_name',
            'relationship',
            'impression',
            'notes',
            'status',
        ];

        fputcsv($handle, $headers, ',', '"', '');

        $query = CharacterRelationship::query()
            ->with(['work', 'fromCharacter', 'toCharacter'])
            ->orderBy('id');

        if ($request->filled('work_id')) {
            $query->where('work_id', $request->integer('work_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('exact_keyword')) {
            $exact = trim((string) $request->input('exact_keyword'));

            $query->where(function (Builder $q) use ($exact): void {
                $q->where('called_name', $exact)
                    ->orWhere('relationship', $exact)
                    ->orWhere('impression', $exact)
                    ->orWhereHas('fromCharacter', fn (Builder $c) => $c->where('name', $exact))
                    ->orWhereHas('toCharacter', fn (Builder $c) => $c->where('name', $exact));
            });
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->input('keyword'));

            $query->where(function (Builder $q) use ($keyword): void {
                $q->where('called_name', 'like', "%{$keyword}%")
                    ->orWhere('relationship', 'like', "%{$keyword}%")
                    ->orWhere('impression', 'like', "%{$keyword}%")
                    ->orWhere('notes', 'like', "%{$keyword}%")
                    ->orWhereHas('fromCharacter', fn (Builder $c) => $c->where('name', 'like', "%{$keyword}%"))
                    ->orWhereHas('toCharacter', fn (Builder $c) => $c->where('name', 'like', "%{$keyword}%"));
            });
        }

        $query->chunkById(500, function ($relationships) use ($handle): void {
            foreach ($relationships as $item) {
                fputcsv($handle, [
                    $item->id,
                    $item->work_id,
                    $item->work?->title ?? '',
                    $item->from_character_id,
                    $item->fromCharacter?->name ?? '',
                    $item->to_character_id,
                    $item->toCharacter?->name ?? '',
                    $item->called_name,
                    $item->relationship,
                    $item->impression,
                    $item->notes,
                    $item->status,
                ], ',', '"', '');
            }
        });

        rewind($handle);
        $csv = stream_get_contents($handle) ?: '';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="oshi-wiki-character-relationships-export.csv"',
        ]);
    }
}
