<?php

namespace App\Console\Commands;

use App\Models\UserBillingProfile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PurgeExpiredWriterData extends Command
{
    protected $signature = 'billing:purge-expired-writer-data
        {--dry-run : 削除せず対象だけ表示する}';

    protected $description =
        'Plus解約後3か月を過ぎたWriter創作データを削除します';

    public function handle(): int
    {
        $profiles = UserBillingProfile::query()
            ->whereNotNull('retention_ends_at')
            ->whereNull('writer_data_deleted_at')
            ->where('retention_ends_at', '<=', now())
            ->orderBy('id')
            ->get();

        if ($profiles->isEmpty()) {
            $this->info('削除対象はありません。');

            return self::SUCCESS;
        }

        foreach ($profiles as $profile) {
            if ($this->option('dry-run')) {
                $this->line(
                    "対象 user_id={$profile->user_id}"
                    ." 削除期限={$profile->retention_ends_at}"
                );

                continue;
            }

            try {
                $this->purgeProfile($profile);
                $this->info(
                    "削除完了 user_id={$profile->user_id}"
                );
            } catch (Throwable $exception) {
                report($exception);

                $this->error(
                    "削除失敗 user_id={$profile->user_id}: "
                    .$exception->getMessage()
                );

                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    private function purgeProfile(
        UserBillingProfile $profile
    ): void {
        $userId = (int) $profile->user_id;

        $imagePaths = Schema::hasTable('original_characters')
            ? DB::table('original_characters')
                ->where('user_id', $userId)
                ->whereNotNull('image_path')
                ->pluck('image_path')
                ->filter()
                ->values()
                ->all()
            : [];

        DB::transaction(function () use (
            $profile,
            $userId
        ): void {
            $promptIds = Schema::hasTable('saved_prompts')
                ? DB::table('saved_prompts')
                    ->where('user_id', $userId)
                    ->pluck('id')
                : collect();

            if (
                Schema::hasTable('saved_prompt_ai_results')
                && $promptIds->isNotEmpty()
            ) {
                DB::table('saved_prompt_ai_results')
                    ->whereIn('saved_prompt_id', $promptIds)
                    ->delete();
            }

            $this->deleteUserRows(
                'original_character_relationships',
                $userId
            );
            $this->deleteUserRows(
                'writer_story_analyses',
                $userId
            );
            $this->deleteUserRows('writer_stories', $userId);
            $this->deleteUserRows('saved_prompts', $userId);
            $this->deleteUserRows('original_characters', $userId);

            $profile->writer_data_deleted_at = now();
            $profile->save();
        });

        foreach ($imagePaths as $imagePath) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    private function deleteUserRows(
        string $table,
        int $userId
    ): void {
        if (
            ! Schema::hasTable($table)
            || ! Schema::hasColumn($table, 'user_id')
        ) {
            return;
        }

        DB::table($table)
            ->where('user_id', $userId)
            ->delete();
    }
}
