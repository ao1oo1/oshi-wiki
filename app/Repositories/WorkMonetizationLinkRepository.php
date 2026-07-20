<?php

namespace App\Repositories;

use App\Models\Work;
use App\Models\WorkMonetizationLink;
use Illuminate\Database\Eloquent\Collection;

class WorkMonetizationLinkRepository
{
    public function forWork(Work $work): Collection
    {
        return $work->monetizationLinks()
            ->with(['service', 'affiliateProgram'])
            ->get();
    }

    public function create(array $data): WorkMonetizationLink
    {
        return WorkMonetizationLink::create($data);
    }

    public function update(
        WorkMonetizationLink $link,
        array $data
    ): bool {
        return $link->update($data);
    }

    public function delete(WorkMonetizationLink $link): bool
    {
        return $link->delete();
    }
}
