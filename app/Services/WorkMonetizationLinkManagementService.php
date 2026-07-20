<?php

namespace App\Services;

use App\Models\AffiliateProgram;
use App\Models\Work;
use App\Models\WorkMonetizationLink;
use App\Repositories\WorkMonetizationLinkRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkMonetizationLinkManagementService
{
    public const PRODUCT_TYPES = [
        'series' => 'シリーズ・作品全体',
        'volume' => '巻・単行本',
        'episode' => '話・エピソード',
        'season' => 'シーズン',
        'movie' => '映画',
        'game' => 'ゲーム',
        'goods' => '関連商品',
        'other' => 'その他',
    ];

    public const AVAILABILITY_STATUSES = [
        'available' => '利用可能',
        'checking' => '確認中',
        'unknown' => '未確認',
        'ended' => '終了',
    ];

    public const INHERITANCE_OPTIONS = [
        'self' => 'この作品のリンクのみ',
        'parent' => '親作品のリンクのみ',
        'self_then_parent' => 'この作品を優先し、親作品も表示',
        'disabled' => '収益リンクを表示しない',
    ];

    public function __construct(
        private readonly WorkMonetizationLinkRepository $repository,
        private readonly AffiliateUrlBuilderService $urlBuilder
    ) {
    }

    public function forWork(Work $work): Collection
    {
        return $this->repository->forWork($work);
    }

    public function create(
        Work $work,
        array $data
    ): WorkMonetizationLink {
        $data = $this->prepareAndValidate($work, $data);
        $data['work_id'] = $work->id;
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return DB::transaction(
            fn (): WorkMonetizationLink =>
                $this->repository->create($data)
        );
    }

    public function update(
        Work $work,
        WorkMonetizationLink $link,
        array $data
    ): bool {
        $this->ensureBelongsToWork($work, $link);

        $data = $this->prepareAndValidate($work, $data);
        $data['updated_by'] = auth()->id();

        return DB::transaction(
            fn (): bool =>
                $this->repository->update($link, $data)
        );
    }

    public function delete(
        Work $work,
        WorkMonetizationLink $link
    ): bool {
        $this->ensureBelongsToWork($work, $link);

        return $this->repository->delete($link);
    }

    public function updateWorkSettings(
        Work $work,
        array $data
    ): bool {
        if (
            $data['monetization_inheritance'] === 'parent'
            && ! $work->parent_work_id
        ) {
            throw ValidationException::withMessages([
                'monetization_inheritance' =>
                    '親作品がないため「親作品のリンクのみ」は選択できません。',
            ]);
        }

        return $work->update([
            'monetization_enabled' =>
                (bool) $data['monetization_enabled'],
            'monetization_inheritance' =>
                $data['monetization_inheritance'],
            'isbn' => filled($data['isbn'] ?? null)
                ? trim((string) $data['isbn'])
                : null,
            'official_store_url' =>
                filled($data['official_store_url'] ?? null)
                    ? trim((string) $data['official_store_url'])
                    : null,
            'updated_by' => auth()->id(),
        ]);
    }

    private function prepareAndValidate(
        Work $work,
        array $data
    ): array {
        $program = AffiliateProgram::query()
            ->with('service')
            ->findOrFail($data['affiliate_program_id']);

        if ((int) $program->service_id !== (int) $data['service_id']) {
            throw ValidationException::withMessages([
                'affiliate_program_id' =>
                    '選択した提携プログラムは、このサービスに属していません。',
            ]);
        }

        if (! $program->is_active || ! $program->service?->is_active) {
            throw ValidationException::withMessages([
                'affiliate_program_id' =>
                    '無効なサービスまたは提携プログラムは選択できません。',
            ]);
        }

        if (
            filled($data['starts_at'] ?? null)
            && filled($data['ends_at'] ?? null)
            && $data['starts_at'] > $data['ends_at']
        ) {
            throw ValidationException::withMessages([
                'ends_at' => '終了日時は開始日時以降を指定してください。',
            ]);
        }

        $temporary = new WorkMonetizationLink([
            ...$data,
            'work_id' => $work->id,
        ]);
        $temporary->setRelation('affiliateProgram', $program);

        $this->urlBuilder->build($temporary);

        return $data;
    }

    private function ensureBelongsToWork(
        Work $work,
        WorkMonetizationLink $link
    ): void {
        abort_unless(
            (int) $link->work_id === (int) $work->id,
            404
        );
    }
}
