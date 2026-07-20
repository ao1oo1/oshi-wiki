<?php

namespace App\Repositories;

use App\Models\LinkClick;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LinkClickRepository
{
    public function paginate(
        int $perPage = 30,
        ?int $workId = null,
        ?int $serviceId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): LengthAwarePaginator {
        return LinkClick::query()
            ->with(['work', 'service', 'link'])
            ->when($workId, fn ($query) =>
                $query->where('work_id', $workId))
            ->when($serviceId, fn ($query) =>
                $query->where('service_id', $serviceId))
            ->when($dateFrom, fn ($query) =>
                $query->whereDate('clicked_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) =>
                $query->whereDate('clicked_at', '<=', $dateTo))
            ->latest('clicked_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function totals(
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $base = LinkClick::query()
            ->when($dateFrom, fn ($query) =>
                $query->whereDate('clicked_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) =>
                $query->whereDate('clicked_at', '<=', $dateTo));

        return [
            'clicks' => (clone $base)->count(),
            'visitors' => (clone $base)
                ->distinct()
                ->count('visitor_hash'),
            'links' => (clone $base)
                ->distinct()
                ->count('work_monetization_link_id'),
        ];
    }

    public function topLinks(
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): Collection {
        return LinkClick::query()
            ->selectRaw(
                'work_monetization_link_id, '
                . 'COUNT(*) AS click_count, '
                . 'COUNT(DISTINCT visitor_hash) AS visitor_count'
            )
            ->with(['link.work', 'link.service'])
            ->when($dateFrom, fn ($query) =>
                $query->whereDate('clicked_at', '>=', $dateFrom))
            ->when($dateTo, fn ($query) =>
                $query->whereDate('clicked_at', '<=', $dateTo))
            ->groupBy('work_monetization_link_id')
            ->orderByDesc('click_count')
            ->limit(10)
            ->get();
    }
}
