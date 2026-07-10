<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('saved_prompts')) {
            return;
        }

        Schema::table('saved_prompts', function (Blueprint $table) {
            if (! Schema::hasColumn('saved_prompts', 'used_count')) {
                $table->unsignedInteger('used_count')->default(0)->after('status');
            }

            if (! Schema::hasColumn('saved_prompts', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable()->after('used_count');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('saved_prompts')) {
            return;
        }

        Schema::table('saved_prompts', function (Blueprint $table) {
            if (Schema::hasColumn('saved_prompts', 'last_used_at')) {
                $table->dropColumn('last_used_at');
            }

            if (Schema::hasColumn('saved_prompts', 'used_count')) {
                $table->dropColumn('used_count');
            }
        });
    }
};
