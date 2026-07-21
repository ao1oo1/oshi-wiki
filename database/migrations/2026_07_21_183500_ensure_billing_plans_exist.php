<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('billing_plans')) {
            return;
        }

        $now = now();

        DB::table('billing_plans')->updateOrInsert(
            ['slug' => 'free'],
            [
                'name' => config(
                    'billing.plans.free.name',
                    '無料プラン'
                ),
                'monthly_price' => (int) config(
                    'billing.plans.free.monthly_price',
                    0
                ),
                'yearly_price' => (int) config(
                    'billing.plans.free.yearly_price',
                    0
                ),
                'limits' => json_encode(
                    config('billing.plans.free.limits', []),
                    JSON_UNESCAPED_UNICODE
                ),
                'priority' => 10,
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );

        DB::table('billing_plans')->updateOrInsert(
            ['slug' => 'plus'],
            [
                'name' => config(
                    'billing.plans.plus.name',
                    'Oshi-Wiki Plus'
                ),
                'monthly_price' => (int) config(
                    'billing.plans.plus.monthly_price',
                    480
                ),
                'yearly_price' => (int) config(
                    'billing.plans.plus.yearly_price',
                    4800
                ),
                'limits' => json_encode(
                    config('billing.plans.plus.limits', []),
                    JSON_UNESCAPED_UNICODE
                ),
                'stripe_monthly_price_id' => config(
                    'services.stripe.monthly_price_id'
                ),
                'priority' => 20,
                'is_active' => true,
                'updated_at' => $now,
                'created_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        // 課金履歴との関連を保つため削除しません。
    }
};
