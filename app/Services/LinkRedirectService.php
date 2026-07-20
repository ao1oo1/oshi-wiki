<?php

namespace App\Services;

use App\Models\WorkMonetizationLink;
use Illuminate\Validation\ValidationException;

class LinkRedirectService
{
    public function __construct(
        private readonly AffiliateUrlBuilderService $urlBuilder
    ) {
    }

    public function resolve(WorkMonetizationLink $link): string
    {
        $link->loadMissing([
            'work.parentWork',
            'service',
            'affiliateProgram',
        ]);

        $work = $link->work;
        $service = $link->service;
        $program = $link->affiliateProgram;

        abort_unless($work && $work->isPublished(), 404);

        if (
            $work->parentWork
            && ! $work->parentWork->isPublished()
        ) {
            abort(404);
        }

        abort_unless(
            $work->monetization_enabled
            && $work->monetization_inheritance !== 'disabled',
            404
        );

        abort_unless(
            $link->is_active
            && $link->availability_status === 'available'
            && (! $link->starts_at || $link->starts_at->lte(now()))
            && (! $link->ends_at || $link->ends_at->gte(now())),
            404
        );

        abort_unless(
            $service
            && $service->is_active
            && $program
            && $program->is_active
            && (! $program->starts_at || $program->starts_at->lte(now()))
            && (! $program->ends_at || $program->ends_at->gte(now())),
            404
        );

        try {
            return $this->urlBuilder->build($link);
        } catch (ValidationException) {
            abort(404);
        }
    }
}
