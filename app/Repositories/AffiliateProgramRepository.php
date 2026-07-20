<?php

namespace App\Repositories;

use App\Models\AffiliateProgram;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AffiliateProgramRepository
{
    public function paginate(
        int $perPage = 20,
        ?string $keyword = null,
        ?int $serviceId = null,
        ?string $activeStatus = null
    ): LengthAwarePaginator {
        return AffiliateProgram::query()
            ->with('service')
            ->when($keyword, function ($query) use ($keyword): void {
                $query->where(function ($query) use ($keyword): void {
                    $query
                        ->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('provider_name', 'like', '%' . $keyword . '%')
                        ->orWhere('affiliate_identifier', 'like', '%' . $keyword . '%')
                        ->orWhere('url_template', 'like', '%' . $keyword . '%');
                });
            })
            ->when($serviceId, fn ($query) => $query->where('service_id', $serviceId))
            ->when($activeStatus === 'active', fn ($query) => $query->where('is_active', true))
            ->when($activeStatus === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('service_id')
            ->orderBy('priority')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): AffiliateProgram
    {
        return AffiliateProgram::create($data);
    }

    public function update(AffiliateProgram $program, array $data): bool
    {
        return $program->update($data);
    }

    public function delete(AffiliateProgram $program): bool
    {
        return $program->delete();
    }
}
