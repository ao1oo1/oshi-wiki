<?php

namespace App\Console\Commands;

use App\Services\ScopedMaintenanceService;
use Illuminate\Console\Command;
use InvalidArgumentException;

class SiteMaintenanceCommand extends Command
{
    protected $signature = 'site:maintenance
        {state : on / off / status}
        {scopes?* : public / writer / contributor / all}';

    protected $description =
        'Oshi-Wikiの範囲別メンテナンス状態を切り替えます。';

    public function __construct(
        private readonly ScopedMaintenanceService $maintenance
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $state = strtolower(
            trim((string) $this->argument('state'))
        );

        try {
            return match ($state) {
                'on' => $this->enable(),
                'off' => $this->disable(),
                'status' => $this->status(),
                default => $this->invalidState(),
            };
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function enable(): int
    {
        $active = $this->maintenance->enable(
            $this->scopes()
        );

        $this->warn(
            'メンテナンス対象を更新しました。'
        );
        $this->showActive($active);

        return self::SUCCESS;
    }

    private function disable(): int
    {
        $active = $this->maintenance->disable(
            $this->scopes()
        );

        $this->info(
            'メンテナンス対象を解除しました。'
        );
        $this->showActive($active);

        return self::SUCCESS;
    }

    private function status(): int
    {
        $this->showActive(
            $this->maintenance->activeScopes()
        );

        return self::SUCCESS;
    }

    private function showActive(array $active): void
    {
        if ($active === []) {
            $this->info(
                '現在：すべて通常公開'
            );

            return;
        }

        $this->line(
            '現在のメンテナンス対象：'
        );

        foreach (
            $this->maintenance->labels($active)
            as $label
        ) {
            $this->warn('・'.$label);
        }

        $this->newLine();
        $this->line(
            '最高管理者のadmin画面は常に対象外です。'
        );
    }

    private function scopes(): array
    {
        return $this->argument('scopes') ?: ['all'];
    }

    private function invalidState(): int
    {
        $this->error(
            'stateには on / off / status を指定してください。'
        );

        return self::FAILURE;
    }
}
