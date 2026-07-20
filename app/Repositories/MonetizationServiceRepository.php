<?php
namespace App\Repositories;

use App\Models\MonetizationService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MonetizationServiceRepository
{
    public function paginate(
        int $perPage = 20,
        ?string $keyword = null,
        ?string $category = null,
        ?string $activeStatus = null
    ): LengthAwarePaginator {
        return MonetizationService::query()
            ->when($keyword, function ($query) use ($keyword): void {
                $query->where(function ($query) use ($keyword): void {
                    $query->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('slug', 'like', '%' . $keyword . '%')
                        ->orWhere('description', 'like', '%' . $keyword . '%')
                        ->orWhere('default_button_label', 'like', '%' . $keyword . '%');
                });
            })
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($activeStatus === 'active', fn ($query) => $query->where('is_active', true))
            ->when($activeStatus === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('priority')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): MonetizationService
    {
        return MonetizationService::create($data);
    }

    public function update(MonetizationService $service, array $data): bool
    {
        return $service->update($data);
    }

    public function delete(MonetizationService $service): bool
    {
        return $service->delete();
    }
}
