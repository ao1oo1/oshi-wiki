<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SiteMaintenanceCommand extends Command
{
    protected $signature = 'site:maintenance
        {state : on / off / status}
        {--refresh=15 : ブラウザの再試行秒数}
        {--retry=60 : Retry-After秒数}';

    protected $description =
        'Oshi-Wiki全体のメンテナンスモードを切り替えます。';

    public function handle(): int
    {
        $state = strtolower(
            trim((string) $this->argument('state'))
        );

        return match ($state) {
            'on' => $this->enable(),
            'off' => $this->disable(),
            'status' => $this->showStatus(),
            default => $this->invalidState(),
        };
    }

    private function enable(): int
    {
        if (app()->isDownForMaintenance()) {
            $this->warn(
                'すでにメンテナンスモードです。'
            );

            return self::SUCCESS;
        }

        Artisan::call('down', [
            '--refresh' => (int) $this->option('refresh'),
            '--retry' => (int) $this->option('retry'),
        ]);

        $this->newLine();
        $this->warn(
            'メンテナンスモードを有効にしました。'
        );
        $this->line(
            'URL直打ち・ページ更新を含むすべての画面が'
            .'メンテナンスページになります。'
        );
        $this->line(
            '保存されていない入力内容は保持されません。'
        );

        return self::SUCCESS;
    }

    private function disable(): int
    {
        Artisan::call('up');

        $this->info(
            'メンテナンスモードを解除しました。'
        );

        return self::SUCCESS;
    }

    private function showStatus(): int
    {
        if (app()->isDownForMaintenance()) {
            $this->warn(
                '現在：メンテナンスモード ON'
            );
        } else {
            $this->info(
                '現在：メンテナンスモード OFF'
            );
        }

        return self::SUCCESS;
    }

    private function invalidState(): int
    {
        $this->error(
            'stateには on / off / status を指定してください。'
        );

        return self::FAILURE;
    }
}
