<?php

namespace App\Services;

use App\Models\Work;
use App\Models\WorkMonetizationLink;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MonetizationDisplayService
{
    public function __construct(
        private readonly AffiliateUrlBuilderService $urlBuilder
    ) {
    }

    public function forWork(Work $work): array
    {
        if (
            ! $work->monetization_enabled
            || $work->monetization_inheritance === 'disabled'
        ) {
            return $this->emptyResult($work);
        }

        $sources = $this->resolveSourceWorks($work);

        $items = collect();

        foreach ($sources as $sourceWork) {
            $sourceWork->loadMissing([
                'displayableMonetizationLinks.service',
                'displayableMonetizationLinks.affiliateProgram',
            ]);

            foreach ($sourceWork->displayableMonetizationLinks as $link) {
                $item = $this->toDisplayItem($link, $sourceWork);

                if ($item !== null) {
                    $items->push($item);
                }
            }
        }

        $items = $items
            ->unique(fn (array $item): string => implode(':', [
                $item['service_id'],
                $item['affiliate_program_id'],
                $item['product_code'],
                $item['product_type'],
            ]))
            ->sortBy([
                ['source_priority', 'asc'],
                ['service_priority', 'asc'],
                ['link_priority', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        return [
            'items' => $items,
            'official_store_url' => $work->official_store_url,
            'has_affiliate' => $items->contains(
                fn (array $item): bool => $item['is_affiliate']
            ),
        ];
    }

    private function resolveSourceWorks(Work $work): Collection
    {
        $work->loadMissing('parentWork');

        return match ($work->monetization_inheritance) {
            'parent' => $this->parentSource($work),
            'self_then_parent' => collect([$work])
                ->merge($this->parentSource($work)),
            default => collect([$work]),
        };
    }

    private function parentSource(Work $work): Collection
    {
        $parent = $work->parentWork;

        if (
            ! $parent
            || ! $parent->isPublished()
            || ! $parent->monetization_enabled
            || $parent->monetization_inheritance === 'disabled'
        ) {
            return collect();
        }

        return collect([$parent]);
    }

    private function toDisplayItem(
        WorkMonetizationLink $link,
        Work $sourceWork
    ): ?array {
        $service = $link->service;
        $program = $link->affiliateProgram;

        if (
            ! $service
            || ! $service->is_active
            || ! $program
            || ! $program->is_active
            || ! $this->programIsInPeriod($program)
        ) {
            return null;
        }

        try {
            $url = $this->urlBuilder->build($link);
        } catch (ValidationException) {
            return null;
        }

        return [
            'id' => $link->id,
            'service_id' => $service->id,
            'affiliate_program_id' => $program->id,
            'product_code' => $link->product_code,
            'product_type' => $link->product_type,
            'service_name' => $service->name,
            'title' => $link->title,
            'button_label' => $link->button_label
                ?: $service->default_button_label
                ?: $service->name . 'で見る',
            'url' => route(
                'public.monetization.redirect',
                $link->public_key
            ),
            'is_affiliate' => $program->is_affiliate,
            'source_work_id' => $sourceWork->id,
            'source_work_title' => $sourceWork->title,
            'is_inherited' => (int) $sourceWork->id
                !== (int) $link->work_id
                || (int) $sourceWork->id
                !== (int) request()->route('work')?->id,
            'source_priority' => (int) $sourceWork->id
                === (int) request()->route('work')?->id
                ? 0
                : 1,
            'service_priority' => $service->priority,
            'link_priority' => $link->priority,
        ];
    }

    private function programIsInPeriod($program): bool
    {
        if ($program->starts_at && $program->starts_at->isFuture()) {
            return false;
        }

        if ($program->ends_at && $program->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    private function emptyResult(Work $work): array
    {
        return [
            'items' => collect(),
            'official_store_url' => $work->official_store_url,
            'has_affiliate' => false,
        ];
    }
}
