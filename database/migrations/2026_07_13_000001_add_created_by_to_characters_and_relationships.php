<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('characters') && ! Schema::hasColumn('characters', 'created_by')) {
            Schema::table('characters', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->after('reviewed_by')->index();
            });
        }

        if (Schema::hasTable('character_relationships') && ! Schema::hasColumn('character_relationships', 'created_by')) {
            Schema::table('character_relationships', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->after('reviewed_by')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('character_relationships') && Schema::hasColumn('character_relationships', 'created_by')) {
            Schema::table('character_relationships', function (Blueprint $table) {
                $table->dropColumn('created_by');
            });
        }

        if (Schema::hasTable('characters') && Schema::hasColumn('characters', 'created_by')) {
            Schema::table('characters', function (Blueprint $table) {
                $table->dropColumn('created_by');
            });
        }
    }
};
