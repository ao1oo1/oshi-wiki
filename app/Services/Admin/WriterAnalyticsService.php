<?php

namespace App\Services\Admin;

use App\Models\BillingWebhookEvent;
use App\Models\Role;
use App\Models\User;
use App\Models\UserBillingProfile;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class WriterAnalyticsService
{
    private const ACTIVE_BILLING_STATUSES = [
        'active',
        'trialing',
        'past_due_grace',
        'canceling',
    ];

    private const DATASETS = [
        'original_characters' => [
            'label' => 'キャラクター',
            'table' => 'original_characters',
            'text_columns' => [
                'name',
                'profile',
                'personality',
                'background',
                'speech_style',
                'notes',
            ],
        ],
        'original_character_relationships' => [
            'label' => '関係性',
            'table' => 'original_character_relationships',
            'text_columns' => [
                'relationship_type',
                'description',
                'notes',
            ],
        ],
        'saved_prompts' => [
            'label' => '保存プロンプト',
            'table' => 'saved_prompts',
            'text_columns' => [
                'title',
                'prompt',
                'content',
                'memo',
            ],
        ],
        'writer_stories' => [
            'label' => 'ストーリー',
            'table' => 'writer_stories',
            'text_columns' => [
                'title',
                'body',
                'memo',
            ],
        ],
        'writer_story_analyses' => [
            'label' => '文体分析',
            'table' => 'writer_story_analyses',
            'text_columns' => [
                'title',
                'source_text',
                'analysis_result',
                'result',
                'memo',
            ],
        ],
    ];

    public function build(
        CarbonImmutable $start,
        CarbonImmutable $end
    ): array {
        $writerRoleId = Role::query()
            ->where('name', User::ROLE_WRITER)
            ->value('id');

        $writers = User::query()
            ->when(
                $writerRoleId,
                fn (Builder $query) => $query->where(
                    'role_id',
                    $writerRoleId
                ),
                fn (Builder $query) => $query->whereRaw('1 = 0')
            );

        $activeWriters = (clone $writers)
            ->where('status', 'active');

        $totalWriterCount = (clone $writers)->count();
        $activeWriterCount = (clone $activeWriters)->count();
        $newWriterCount = (clone $writers)
            ->whereBetween('created_at', [
                $start->startOfDay(),
                $end->endOfDay(),
            ])
            ->count();

        $activeProfiles = UserBillingProfile::query()
            ->with('plan')
            ->whereIn('status', self::ACTIVE_BILLING_STATUSES)
            ->get()
            ->filter(fn (UserBillingProfile $profile) =>
                $profile->hasPaidAccess()
            );

        $plusUserIds = $activeProfiles
            ->pluck('user_id')
            ->unique()
            ->values();

        $plusCount = $plusUserIds->count();
        $freeCount = max(0, $activeWriterCount - $plusCount);
        $plusRate = $activeWriterCount > 0
            ? round(($plusCount / $activeWriterCount) * 100, 1)
            : 0.0;

        $dataUsage = $this->dataUsage();
        $totalDataCount = collect($dataUsage)->sum('count');
        $totalCharacters = collect($dataUsage)->sum('characters');
        $estimatedBytes = collect($dataUsage)->sum('estimated_bytes');

        $comparison = $this->planComparison(
            $activeWriters,
            $plusUserIds->all()
        );

        return [
            'period' => [
                'start' => $start,
                'end' => $end,
                'days' => $start->diffInDays($end) + 1,
            ],
            'cards' => [
                'total_writers' => $totalWriterCount,
                'active_writers' => $activeWriterCount,
                'new_writers' => $newWriterCount,
                'plus_members' => $plusCount,
                'free_members' => $freeCount,
                'plus_rate' => $plusRate,
                'canceling' => UserBillingProfile::query()
                    ->where('status', 'canceling')
                    ->count(),
                'past_due' => UserBillingProfile::query()
                    ->where('status', 'past_due_grace')
                    ->count(),
                'estimated_mrr' => $plusCount * (int) config(
                    'billing.plans.plus.monthly_price',
                    480
                ),
                'total_data_count' => $totalDataCount,
                'total_characters' => $totalCharacters,
                'estimated_bytes' => $estimatedBytes,
            ],
            'writer_trend' => $this->writerTrend(
                $writers,
                $start,
                $end
            ),
            'plus_trend' => $this->plusTrend($start, $end),
            'data_usage' => $dataUsage,
            'plan_comparison' => $comparison,
            'limit_pressure' => $this->limitPressure(
                $activeWriters,
                $plusUserIds->all()
            ),
            'billing_alerts' => $this->billingAlerts(),
        ];
    }

    private function writerTrend(
        Builder $writers,
        CarbonImmutable $start,
        CarbonImmutable $end
    ): array {
        $daily = (clone $writers)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->whereBetween('created_at', [
                $start->startOfDay(),
                $end->endOfDay(),
            ])
            ->groupBy('day')
            ->pluck('count', 'day');

        $cumulative = (clone $writers)
            ->where('created_at', '<', $start->startOfDay())
            ->count();

        return collect(
            CarbonPeriod::create($start, $end)
        )->map(function ($date) use ($daily, &$cumulative): array {
            $day = $date->format('Y-m-d');
            $newCount = (int) ($daily[$day] ?? 0);
            $cumulative += $newCount;

            return [
                'date' => $day,
                'label' => $date->format('n/j'),
                'new' => $newCount,
                'cumulative' => $cumulative,
            ];
        })->values()->all();
    }

    private function plusTrend(
        CarbonImmutable $start,
        CarbonImmutable $end
    ): array {
        $daily = UserBillingProfile::query()
            ->whereHas('plan', fn (Builder $query) =>
                $query->where('slug', 'plus')
            )
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->whereBetween('created_at', [
                $start->startOfDay(),
                $end->endOfDay(),
            ])
            ->groupBy('day')
            ->pluck('count', 'day');

        $cumulative = UserBillingProfile::query()
            ->whereHas('plan', fn (Builder $query) =>
                $query->where('slug', 'plus')
            )
            ->where('created_at', '<', $start->startOfDay())
            ->count();

        return collect(
            CarbonPeriod::create($start, $end)
        )->map(function ($date) use ($daily, &$cumulative): array {
            $day = $date->format('Y-m-d');
            $newCount = (int) ($daily[$day] ?? 0);
            $cumulative += $newCount;

            return [
                'date' => $day,
                'label' => $date->format('n/j'),
                'new' => $newCount,
                'cumulative' => $cumulative,
            ];
        })->values()->all();
    }

    private function dataUsage(): array
    {
        return collect(self::DATASETS)
            ->map(function (array $definition): array {
                $table = $definition['table'];

                if (! Schema::hasTable($table)) {
                    return [
                        'key' => $table,
                        'label' => $definition['label'],
                        'count' => 0,
                        'characters' => 0,
                        'estimated_bytes' => 0,
                        'share' => 0,
                    ];
                }

                $count = DB::table($table)->count();
                $columns = collect(
                    $definition['text_columns']
                )->filter(fn (string $column) =>
                    Schema::hasColumn($table, $column)
                )->values();

                $characters = 0;

                foreach ($columns as $column) {
                    $characters += (int) DB::table($table)
                        ->sum(DB::raw(
                            'CHAR_LENGTH(COALESCE(`'
                            .str_replace('`', '``', $column)
                            .'`, \'\'))'
                        ));
                }

                return [
                    'key' => $table,
                    'label' => $definition['label'],
                    'count' => $count,
                    'characters' => $characters,
                    'estimated_bytes' => $characters * 3,
                    'share' => 0,
                ];
            })
            ->values()
            ->pipe(function ($rows) {
                $total = max(1, $rows->sum('count'));

                return $rows->map(function (array $row) use ($total) {
                    $row['share'] = round(
                        ($row['count'] / $total) * 100,
                        1
                    );

                    return $row;
                })->all();
            });
    }

    private function planComparison(
        Builder $activeWriters,
        array $plusUserIds
    ): array {
        $allWriterIds = (clone $activeWriters)->pluck('id');
        $plusIds = collect($plusUserIds);
        $freeIds = $allWriterIds->diff($plusIds)->values();

        return collect(self::DATASETS)
            ->map(function (array $definition) use (
                $freeIds,
                $plusIds
            ): array {
                $table = $definition['table'];

                if (
                    ! Schema::hasTable($table)
                    || ! Schema::hasColumn($table, 'user_id')
                ) {
                    return [
                        'label' => $definition['label'],
                        'free_average' => 0,
                        'plus_average' => 0,
                    ];
                }

                $freeCount = $freeIds->isEmpty()
                    ? 0
                    : DB::table($table)
                        ->whereIn('user_id', $freeIds)
                        ->count();

                $plusCount = $plusIds->isEmpty()
                    ? 0
                    : DB::table($table)
                        ->whereIn('user_id', $plusIds)
                        ->count();

                return [
                    'label' => $definition['label'],
                    'free_average' => $freeIds->count() > 0
                        ? round($freeCount / $freeIds->count(), 1)
                        : 0,
                    'plus_average' => $plusIds->count() > 0
                        ? round($plusCount / $plusIds->count(), 1)
                        : 0,
                ];
            })
            ->values()
            ->all();
    }

    private function limitPressure(
        Builder $activeWriters,
        array $plusUserIds
    ): array {
        $freeIds = (clone $activeWriters)
            ->whereNotIn('id', $plusUserIds ?: [0])
            ->pluck('id');

        $limitMap = [
            'original_characters' => [
                'label' => 'キャラクター',
                'limit' => (int) config(
                    'billing.plans.free.limits.original_characters',
                    30
                ),
            ],
            'original_character_relationships' => [
                'label' => '関係性',
                'limit' => (int) config(
                    'billing.plans.free.limits.relationships',
                    100
                ),
            ],
            'saved_prompts' => [
                'label' => '保存プロンプト',
                'limit' => (int) config(
                    'billing.plans.free.limits.prompts',
                    50
                ),
            ],
            'writer_stories' => [
                'label' => 'ストーリー',
                'limit' => (int) config(
                    'billing.plans.free.limits.stories',
                    10
                ),
            ],
        ];

        return collect($limitMap)
            ->map(function (array $definition, string $table) use (
                $freeIds
            ): array {
                if (
                    $freeIds->isEmpty()
                    || ! Schema::hasTable($table)
                    || ! Schema::hasColumn($table, 'user_id')
                ) {
                    return [
                        'label' => $definition['label'],
                        'limit' => $definition['limit'],
                        'over_80' => 0,
                        'reached' => 0,
                    ];
                }

                $counts = DB::table($table)
                    ->selectRaw('user_id, COUNT(*) as count')
                    ->whereIn('user_id', $freeIds)
                    ->groupBy('user_id')
                    ->pluck('count');

                $limit = max(1, $definition['limit']);

                return [
                    'label' => $definition['label'],
                    'limit' => $limit,
                    'over_80' => $counts
                        ->filter(fn ($count) =>
                            $count >= (int) ceil($limit * 0.8)
                        )->count(),
                    'reached' => $counts
                        ->filter(fn ($count) => $count >= $limit)
                        ->count(),
                ];
            })
            ->values()
            ->all();
    }

    private function billingAlerts(): array
    {
        $profiles = UserBillingProfile::query()
            ->with('plan')
            ->get();

        $subscriptionWithoutPlus = $profiles
            ->filter(fn (UserBillingProfile $profile) =>
                filled($profile->stripe_subscription_id)
                && ! $profile->hasPaidAccess()
                && in_array(
                    $profile->status,
                    self::ACTIVE_BILLING_STATUSES,
                    true
                )
            )
            ->count();

        $plusWithoutSubscription = $profiles
            ->filter(fn (UserBillingProfile $profile) =>
                $profile->plan?->slug === 'plus'
                && $profile->hasPaidAccess()
                && blank($profile->stripe_subscription_id)
            )
            ->count();

        $failedWebhooks = Schema::hasTable(
            'billing_webhook_events'
        )
            ? BillingWebhookEvent::query()
                ->where('status', 'failed')
                ->count()
            : 0;

        return [
            'subscription_without_plus' => $subscriptionWithoutPlus,
            'plus_without_subscription' => $plusWithoutSubscription,
            'failed_webhooks' => $failedWebhooks,
            'total' => $subscriptionWithoutPlus
                + $plusWithoutSubscription
                + $failedWebhooks,
        ];
    }
}
