<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Character;
use App\Models\CharacterRelationship;
use App\Models\Tag;
use App\Models\Work;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class TrashController extends Controller
{
    private const TYPES = [
        'works' => [
            'label' => '作品',
            'model' => Work::class,
            'route_key' => 'works',
        ],
        'characters' => [
            'label' => 'キャラクター',
            'model' => Character::class,
            'route_key' => 'characters',
        ],
        'relationships' => [
            'label' => '関係性',
            'model' => CharacterRelationship::class,
            'route_key' => 'relationships',
        ],
        'tags' => [
            'label' => 'タグ',
            'model' => Tag::class,
            'route_key' => 'tags',
        ],
    ];

    public function index(Request $request): View
    {
        $this->authorizeSuperAdmin();

        $type = $request->input('type', 'works');

        if (! array_key_exists($type, self::TYPES)) {
            $type = 'works';
        }

        $keyword = trim((string) $request->input('keyword', ''));

        $counts = [];

        foreach (self::TYPES as $key => $config) {
            $counts[$key] = $this->deletedQuery($config['model'])->count();
        }

        $items = $this->deletedQuery(self::TYPES[$type]['model']);

        $this->applyKeyword($items, self::TYPES[$type]['model'], $keyword);

        $items = $items
            ->latest('deleted_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.trash.index', [
            'types' => self::TYPES,
            'type' => $type,
            'keyword' => $keyword,
            'items' => $items,
            'counts' => $counts,
        ]);
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        abort_unless(array_key_exists($type, self::TYPES), 404);

        $modelClass = self::TYPES[$type]['model'];

        $record = $this->deletedQuery($modelClass)
            ->whereKey($id)
            ->firstOrFail();

        try {
            $this->forceDeleteRecord($record);

            return redirect()
                ->route('admin.trash.index', ['type' => $type])
                ->with('success', self::TYPES[$type]['label'] . 'をデータベースから完全削除しました。');
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.trash.index', ['type' => $type])
                ->with('error', '完全削除できませんでした。関連データが残っている可能性があります。');
        }
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $this->authorizeSuperAdmin();

        $validated = $request->validate([
            'type' => ['required', 'string'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ]);

        $type = $validated['type'];

        abort_unless(array_key_exists($type, self::TYPES), 404);

        $modelClass = self::TYPES[$type]['model'];
        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));

        $deleted = 0;
        $failed = 0;

        $records = $this->deletedQuery($modelClass)
            ->whereIn((new $modelClass())->getKeyName(), $ids)
            ->get();

        foreach ($records as $record) {
            try {
                $this->forceDeleteRecord($record);
                $deleted++;
            } catch (Throwable $e) {
                report($e);
                $failed++;
            }
        }

        $message = "{$deleted}件をデータベースから完全削除しました。";

        if ($failed > 0) {
            $message .= " {$failed}件は関連データ等の理由で削除できませんでした。";
        }

        return redirect()
            ->route('admin.trash.index', ['type' => $type])
            ->with($failed > 0 ? 'error' : 'success', $message);
    }

    private function authorizeSuperAdmin(): void
    {
        abort_unless(
            auth()->user()?->canManageAllAdminFeatures(),
            403,
            'ゴミ箱機能は最高管理者のみ利用できます。'
        );
    }

    private function deletedQuery(string $modelClass): Builder
    {
        if ($this->usesSoftDeletes($modelClass)) {
            return $modelClass::onlyTrashed();
        }

        $query = $modelClass::query();

        if (Schema::hasColumn((new $modelClass())->getTable(), 'deleted_at')) {
            $query->whereNotNull('deleted_at');
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    private function forceDeleteRecord($record): void
    {
        if ($this->usesSoftDeletes($record::class) && method_exists($record, 'forceDelete')) {
            $record->forceDelete();

            return;
        }

        $record->delete();
    }

    private function usesSoftDeletes(string $modelClass): bool
    {
        return in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive($modelClass),
            true
        );
    }

    private function applyKeyword(Builder $query, string $modelClass, string $keyword): void
    {
        if ($keyword === '') {
            return;
        }

        $table = (new $modelClass())->getTable();

        $query->where(function (Builder $keywordQuery) use ($table, $keyword) {
            foreach (Schema::getColumnListing($table) as $column) {
                if (in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'], true)) {
                    continue;
                }

                $keywordQuery->orWhere($column, 'like', '%' . $keyword . '%');
            }
        });
    }

    public static function displayName($item, string $type): string
    {
        return match ($type) {
            'works' => (string) ($item->title ?? '名称未設定'),
            'characters' => (string) ($item->name ?? '名称未設定'),
            'relationships' => '関係性ID: ' . $item->id,
            'tags' => (string) ($item->name ?? '名称未設定'),
            default => 'ID: ' . $item->id,
        };
    }

    public static function summary($item, string $type): string
    {
        return match ($type) {
            'works' => trim(implode(' / ', array_filter([
                $item->genre ?? null,
                $item->original_media ?? null,
                $item->status ?? null,
            ]))),
            'characters' => trim(implode(' / ', array_filter([
                '作品ID: ' . ($item->work_id ?? '-'),
                $item->affiliation ?? null,
                $item->status ?? null,
            ]))),
            'relationships' => trim(implode(' / ', array_filter([
                '作品ID: ' . ($item->work_id ?? '-'),
                'From: ' . ($item->from_character_id ?? '-'),
                'To: ' . ($item->to_character_id ?? '-'),
                $item->relationship ?? null,
                $item->status ?? null,
            ]))),
            'tags' => trim(implode(' / ', array_filter([
                $item->type ?? null,
                $item->status ?? null,
            ]))),
            default => '',
        };
    }
}
