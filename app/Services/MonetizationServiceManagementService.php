<?php
namespace App\Services;

use App\Models\MonetizationService;
use App\Repositories\MonetizationServiceRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MonetizationServiceManagementService
{
    public const CATEGORIES = [
        'vod' => '動画配信',
        'ebook' => '電子書籍',
        'goods' => '関連商品',
        'app' => 'アプリ・ゲーム',
        'official' => '公式リンク',
        'other' => 'その他',
    ];

    public function __construct(
        private readonly MonetizationServiceRepository $repository
    ) {
    }

    public function paginate(
        int $perPage = 20,
        ?string $keyword = null,
        ?string $category = null,
        ?string $activeStatus = null
    ): LengthAwarePaginator {
        return $this->repository->paginate(
            $perPage,
            $keyword,
            $category,
            $activeStatus
        );
    }

    public function create(array $data): MonetizationService
    {
        $data['slug'] = $this->makeUniqueSlug(
            $data['slug'] ?? null,
            $data['name']
        );

        return $this->repository->create($data);
    }

    public function update(MonetizationService $service, array $data): bool
    {
        $data['slug'] = $this->makeUniqueSlug(
            $data['slug'] ?? null,
            $data['name'],
            $service->id
        );

        return $this->repository->update($service, $data);
    }

    public function delete(MonetizationService $service): bool
    {
        if ($service->affiliatePrograms()->exists()) {
            throw ValidationException::withMessages([
                'service' => '提携プログラムが登録されているため削除できません。',
            ]);
        }

        if ($service->workLinks()->exists()) {
            throw ValidationException::withMessages([
                'service' => '作品の商品リンクが登録されているため削除できません。',
            ]);
        }

        return $this->repository->delete($service);
    }

    private function makeUniqueSlug(
        ?string $requestedSlug,
        string $name,
        ?int $ignoreId = null
    ): string {
        $base = Str::slug((string) $requestedSlug);

        if ($base === '') {
            $base = Str::slug($name);
        }

        if ($base === '') {
            $base = 'service-' . Str::lower(Str::random(8));
        }

        $slug = $base;
        $count = 2;

        while (
            MonetizationService::withTrashed()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
