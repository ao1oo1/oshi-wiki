<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'user_billing_profiles',
            function (Blueprint $table): void {
                $table->timestamp('retention_started_at')
                    ->nullable()
                    ->after('canceled_at');

                $table->timestamp('retention_ends_at')
                    ->nullable()
                    ->after('retention_started_at');

                $table->timestamp('writer_data_deleted_at')
                    ->nullable()
                    ->after('retention_ends_at');

                $table->index(
                    ['status', 'retention_ends_at'],
                    'billing_retention_expiry_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::table(
            'user_billing_profiles',
            function (Blueprint $table): void {
                $table->dropIndex('billing_retention_expiry_index');
                $table->dropColumn([
                    'retention_started_at',
                    'retention_ends_at',
                    'writer_data_deleted_at',
                ]);
            }
        );
    }
};
