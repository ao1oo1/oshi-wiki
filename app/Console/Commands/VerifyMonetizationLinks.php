<?php

namespace App\Console\Commands;

use App\Services\LinkVerificationService;
use Illuminate\Console\Command;

class VerifyMonetizationLinks extends Command
{
    protected $signature = 'monetization:verify-links
        {--limit= : 1回に検証する最大件数}';

    protected $description =
        '有効な作品商品リンクの到達可否を検証します。';

    public function handle(
        LinkVerificationService $verificationService
    ): int {
        $limit = $this->option('limit');

        if (
            $limit !== null
            && (! ctype_digit((string) $limit) || (int) $limit < 1)
        ) {
            $this->error('--limitには1以上の整数を指定してください。');

            return self::INVALID;
        }

        $summary = $verificationService->verifyActiveLinks(
            'scheduled',
            $limit !== null ? (int) $limit : null
        );

        $this->table(
            ['対象', '利用可能', '確認中', '未確認', '終了'],
            [[
                $summary['total'],
                $summary['available'],
                $summary['checking'],
                $summary['unknown'],
                $summary['ended'],
            ]]
        );

        return self::SUCCESS;
    }
}
