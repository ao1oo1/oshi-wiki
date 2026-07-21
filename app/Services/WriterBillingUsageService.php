<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WriterBillingUsageService
{
    private const DEFINITIONS = [
        'original_characters' => ['label' => 'オリジナルキャラクター', 'table' => 'original_characters', 'limit_key' => 'original_characters'],
        'relationships' => ['label' => '関係性', 'table' => 'original_character_relationships', 'limit_key' => 'relationships'],
        'prompts' => ['label' => '保存プロンプト', 'table' => 'saved_prompts', 'limit_key' => 'prompts'],
        'stories' => ['label' => 'ストーリー', 'table' => 'writer_stories', 'limit_key' => 'stories'],
    ];

    public function forUser(User $user): array
    {
        $freeLimits = (array) config('billing.plans.free.limits', []);

        return collect(self::DEFINITIONS)->map(function (array $definition, string $key) use ($user, $freeLimits): array {
            $count = 0;
            if (Schema::hasTable($definition['table']) && Schema::hasColumn($definition['table'], 'user_id')) {
                $count = DB::table($definition['table'])->where('user_id', $user->id)->count();
            }

            $limit = (int) ($freeLimits[$definition['limit_key']] ?? 0);

            return [
                'key' => $key,
                'label' => $definition['label'],
                'count' => $count,
                'free_limit' => $limit,
                'overage' => max(0, $count - $limit),
                'is_over' => $limit > 0 && $count > $limit,
            ];
        })->values()->all();
    }

    public function hasOverage(User $user): bool
    {
        return collect($this->forUser($user))->contains(fn (array $row): bool => $row['is_over']);
    }
}
