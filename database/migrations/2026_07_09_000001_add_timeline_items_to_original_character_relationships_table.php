<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('original_character_relationships')) {
            return;
        }

        Schema::table('original_character_relationships', function (Blueprint $table) {
            if (! Schema::hasColumn('original_character_relationships', 'timeline_items')) {
                $table->json('timeline_items')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('original_character_relationships')) {
            return;
        }

        Schema::table('original_character_relationships', function (Blueprint $table) {
            if (Schema::hasColumn('original_character_relationships', 'timeline_items')) {
                $table->dropColumn('timeline_items');
            }
        });
    }
};
