<?php

namespace App\Services;

use App\Models\AffiliateProgram;
use App\Repositories\AffiliateProgramRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AffiliateProgramManagementService
{
    private const ALLOWED_PLACEHOLDERS = [
        'product_code',
        'affiliate_identifier',
        'work_id',
        'campaign_code',
        'sub_id',
    ];

    public function __construct(
        private readonly AffiliateProgramRepository $repository
    ) {
    }

    public function paginate(
        int $perPage = 20,
        ?string $keyword = null,
        ?int $serviceId = null,
        ?string $activeStatus = null
    ): LengthAwarePaginator {
        return $this->repository->paginate(
            $perPage,
            $keyword,
            $serviceId,
            $activeStatus
        );
    }

    public function create(array $data): AffiliateProgram
    {
        $data = $this->normalizeAndValidate($data);
        $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();

        return DB::transaction(function () use ($data): AffiliateProgram {
            if ($data['is_default']) {
                AffiliateProgram::query()
                    ->where('service_id', $data['service_id'])
                    ->update(['is_default' => false]);
            }

            return $this->repository->create($data);
        });
    }

    public function update(
        AffiliateProgram $program,
        array $data
    ): bool {
        $data = $this->normalizeAndValidate($data);
        $data['updated_by'] = auth()->id();

        return DB::transaction(function () use ($program, $data): bool {
            if ($data['is_default']) {
                AffiliateProgram::query()
                    ->where('service_id', $data['service_id'])
                    ->whereKeyNot($program->id)
                    ->update(['is_default' => false]);
            }

            return $this->repository->update($program, $data);
        });
    }

    public function delete(AffiliateProgram $program): bool
    {
        if ($program->workLinks()->exists()) {
            throw ValidationException::withMessages([
                'program' => '作品の商品リンクで使用中のため削除できません。',
            ]);
        }

        return $this->repository->delete($program);
    }

    private function normalizeAndValidate(array $data): array
    {
        $data['allowed_hosts'] = $this->normalizeHosts(
            $data['allowed_hosts_text'] ?? ''
        );
        unset($data['allowed_hosts_text']);

        $data['additional_parameters'] = $this->normalizeJson(
            $data['additional_parameters_text'] ?? null
        );
        unset($data['additional_parameters_text']);

        $this->validateTemplate($data['url_template']);
        $this->validateHosts($data['allowed_hosts']);
        $this->validateRegex($data['code_validation_pattern'] ?? null);

        if (
            filled($data['starts_at'] ?? null)
            && filled($data['ends_at'] ?? null)
            && $data['starts_at'] > $data['ends_at']
        ) {
            throw ValidationException::withMessages([
                'ends_at' => '終了日時は開始日時以降を指定してください。',
            ]);
        }

        return $data;
    }

    private function normalizeHosts(string $value): array
    {
        return collect(
            preg_split('/[,、\s]+/u', $value) ?: []
        )
            ->map(fn ($host) => strtolower(trim((string) $host)))
            ->map(fn ($host) => preg_replace('#^https?://#', '', $host))
            ->map(fn ($host) => trim((string) $host, '/'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeJson(?string $value): ?array
    {
        if (! filled($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        if (
            json_last_error() !== JSON_ERROR_NONE
            || ! is_array($decoded)
            || array_is_list($decoded)
        ) {
            throw ValidationException::withMessages([
                'additional_parameters_text' =>
                    '追加パラメータはJSONオブジェクト形式で指定してください。',
            ]);
        }

        return $decoded;
    }

    private function validateTemplate(string $template): void
    {
        if (! str_contains($template, '{product_code}')) {
            throw ValidationException::withMessages([
                'url_template' =>
                    'URLテンプレートには {product_code} が必要です。',
            ]);
        }

        preg_match_all('/\{([a-z_]+)\}/', $template, $matches);

        $unknown = array_values(array_diff(
            array_unique($matches[1] ?? []),
            self::ALLOWED_PLACEHOLDERS
        ));

        if ($unknown !== []) {
            throw ValidationException::withMessages([
                'url_template' =>
                    '使用できない置換項目があります: '
                    . implode(', ', $unknown),
            ]);
        }

        $sample = str_replace(
            array_map(
                fn ($key) => '{' . $key . '}',
                self::ALLOWED_PLACEHOLDERS
            ),
            ['SAMPLE123', 'affiliate', '1', 'campaign', 'sub'],
            $template
        );

        $parts = parse_url($sample);

        if (
            strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || ! filled($parts['host'] ?? null)
        ) {
            throw ValidationException::withMessages([
                'url_template' =>
                    'URLテンプレートはhttpsで始まる有効なURLにしてください。',
            ]);
        }
    }

    private function validateHosts(array $hosts): void
    {
        if ($hosts === []) {
            throw ValidationException::withMessages([
                'allowed_hosts_text' =>
                    '許可ホストを1件以上入力してください。',
            ]);
        }

        foreach ($hosts as $host) {
            if (
                ! preg_match(
                    '/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/',
                    $host
                )
            ) {
                throw ValidationException::withMessages([
                    'allowed_hosts_text' =>
                        "許可ホスト「{$host}」の形式が正しくありません。",
                ]);
            }
        }
    }

    private function validateRegex(?string $pattern): void
    {
        if (! filled($pattern)) {
            return;
        }

        if (@preg_match($pattern, '') === false) {
            throw ValidationException::withMessages([
                'code_validation_pattern' =>
                    '商品コード検証パターンが正しい正規表現ではありません。',
            ]);
        }
    }
}
