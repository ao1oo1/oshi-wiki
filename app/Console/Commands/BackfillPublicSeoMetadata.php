<?php

namespace App\Console\Commands;

use App\Models\Character;
use App\Models\Work;
use App\Services\PublicSeoContentBuilder;
use Illuminate\Console\Command;

class BackfillPublicSeoMetadata extends Command
{
    protected $signature = 'seo:backfill
        {--force : Existing SEO values are overwritten}
        {--all : Unpublished records are also included}
        {--dry-run : Values are displayed without saving}';

    protected $description =
        '作品・キャラクターのSEOタイトルとdescriptionを一括設定';

    public function handle(
        PublicSeoContentBuilder $builder
    ): int {
        $force = (bool) $this->option('force');
        $all = (bool) $this->option('all');
        $dryRun = (bool) $this->option('dry-run');

        $workCount = 0;
        $characterCount = 0;

        Work::query()
            ->when(
                ! $all,
                fn ($query) => $query
                    ->where('status', 'published')
            )
            ->orderBy('id')
            ->chunkById(
                100,
                function ($works) use (
                    $builder,
                    $force,
                    $dryRun,
                    &$workCount
                ): void {
                    foreach ($works as $work) {
                        $changes = [];

                        if ($force || blank($work->seo_title)) {
                            $changes['seo_title'] =
                                $builder->workTitle($work);
                        }

                        if (
                            $force
                            || blank($work->seo_description)
                        ) {
                            $changes['seo_description'] =
                                $builder->workDescription($work);
                        }

                        if ($changes === []) {
                            continue;
                        }

                        $workCount++;

                        if (! $dryRun) {
                            $work->forceFill($changes)->saveQuietly();
                        }
                    }
                }
            );

        Character::query()
            ->with(['linkedWorks', 'work'])
            ->when(
                ! $all,
                fn ($query) => $query
                    ->where('status', 'published')
                    ->where(function ($characterQuery): void {
                        $characterQuery
                            ->whereHas(
                                'linkedWorks',
                                fn ($workQuery) => $workQuery
                                    ->where(
                                        'works.status',
                                        'published'
                                    )
                            )
                            ->orWhereHas(
                                'work',
                                fn ($workQuery) => $workQuery
                                    ->where(
                                        'status',
                                        'published'
                                    )
                            );
                    })
            )
            ->orderBy('id')
            ->chunkById(
                100,
                function ($characters) use (
                    $builder,
                    $force,
                    $dryRun,
                    &$characterCount
                ): void {
                    foreach ($characters as $character) {
                        $changes = [];

                        if (
                            $force
                            || blank($character->seo_title)
                        ) {
                            $changes['seo_title'] =
                                $builder->characterTitle(
                                    $character
                                );
                        }

                        if (
                            $force
                            || blank(
                                $character->seo_description
                            )
                        ) {
                            $changes['seo_description'] =
                                $builder->characterDescription(
                                    $character
                                );
                        }

                        if ($changes === []) {
                            continue;
                        }

                        $characterCount++;

                        if (! $dryRun) {
                            $character
                                ->forceFill($changes)
                                ->saveQuietly();
                        }
                    }
                }
            );

        $mode = $dryRun ? 'DRY RUN' : 'UPDATED';

        $this->info(
            "{$mode}: works={$workCount}, "
            . "characters={$characterCount}"
        );

        return self::SUCCESS;
    }
}
