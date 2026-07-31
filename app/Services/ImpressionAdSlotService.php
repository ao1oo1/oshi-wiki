<?php

namespace App\Services;

use App\Models\ImpressionAdSlot;
use App\Models\MonetizationService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ImpressionAdSlotService
{
    public const PAGE_SCOPES = [
        'all_public' => '公開ページすべて',
        'home' => 'トップページ',
        'works_index' => '作品一覧',
        'work_show' => '作品詳細',
        'character_show' => 'キャラクター詳細',
        'tags_index' => 'タグ一覧',
        'writing_tool' => '執筆ツール紹介',
        'static_pages' => 'About・お問い合わせ・規約・スタッフ',
        'writer_all' => 'Writer画面すべて',
    ];

    public const POSITIONS = [
        'page_top' => 'ページ上部（ヘッダー直後）',
        'page_middle' => 'ページ中部',
        'page_bottom' => 'ページ下部（フッター直前）',
        'writer_sidebar_1' => 'Writer左メニュー下部・1枠目',
        'writer_sidebar_2' => 'Writer左メニュー下部・2枠目',
        'writer_page_bottom' => 'Writer各画面・最下部',
    ];

    public const DEVICE_TYPES = [
        'all' => 'PC・スマートフォン',
        'desktop' => 'PCのみ',
        'mobile' => 'スマートフォンのみ',
    ];

    public function create(array $data, ?int $userId): ImpressionAdSlot
    {
        $this->validateService($data);

        return ImpressionAdSlot::query()->create([
            ...$data,
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function update(
        ImpressionAdSlot $slot,
        array $data,
        ?int $userId
    ): bool {
        $this->validateService($data);

        return $slot->update([
            ...$data,
            'updated_by' => $userId,
        ]);
    }

    public function delete(ImpressionAdSlot $slot): bool
    {
        return (bool) $slot->delete();
    }

    public function displayableFor(
        Request $request,
        string $position
    ): Collection {
        if (! array_key_exists($position, self::POSITIONS)) {
            return new Collection();
        }

        $scope = $this->scopeForRoute($request->route()?->getName());

        if ($scope === null) {
            return new Collection();
        }

        return ImpressionAdSlot::query()
            ->with('service')
            ->currentlyDisplayable()
            ->where('position', $position)
            ->whereIn('page_scope', ['all_public', $scope])
            ->whereHas('service', function ($query): void {
                $query
                    ->where('revenue_model', 'impression')
                    ->where('is_active', true)
                    ->whereNotNull('impression_script');
            })
            ->orderBy('priority')
            ->orderBy('id')
            ->get();
    }

    public function displayableForWriter(
        Request $request,
        string $position
    ): Collection {
        $writerPositions = [
            'writer_sidebar_1',
            'writer_sidebar_2',
            'writer_page_bottom',
        ];

        if (
            ! in_array($position, $writerPositions, true)
            || ! str_starts_with(
                (string) $request->route()?->getName(),
                'writer.'
            )
        ) {
            return new Collection();
        }

        return ImpressionAdSlot::query()
            ->with('service')
            ->currentlyDisplayable()
            ->where('position', $position)
            ->where('page_scope', 'writer_all')
            ->whereHas('service', function ($query): void {
                $query
                    ->where('revenue_model', 'impression')
                    ->where('is_active', true)
                    ->whereNotNull('impression_script');
            })
            ->orderBy('priority')
            ->orderBy('id')
            ->get();
    }

    public function scopeForRoute(?string $routeName): ?string
    {
        return match ($routeName) {
            'public.home' => 'home',
            'public.works.index',
            'public.works.search' => 'works_index',
            'public.works.show' => 'work_show',
            'public.characters.show' => 'character_show',
            'public.tags.index' => 'tags_index',
            'public.writing-tool.show' => 'writing_tool',
            'public.about.show',
            'public.contact.create',
            'public.contact.store',
            'public.staff.show',
            'public.privacy',
            'public.terms',
            'public.legal',
            'public.billing-policy',
            'public.pricing' => 'static_pages',
            default => null,
        };
    }

    private function validateService(array $data): void
    {
        $service = MonetizationService::query()
            ->find($data['monetization_service_id'] ?? null);

        if (
            ! $service
            || $service->revenue_model !== 'impression'
            || ! $service->is_active
            || blank($service->impression_script)
        ) {
            throw ValidationException::withMessages([
                'monetization_service_id' =>
                    '有効なインプレッション課金型サービスを選択してください。',
            ]);
        }
    }
}
