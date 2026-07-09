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
            if (! Schema::hasColumn('original_character_relationships', 'from_character_source')) {
                $table->string('from_character_source', 30)->default('original')->after('user_id');
            }

            if (! Schema::hasColumn('original_character_relationships', 'to_character_source')) {
                $table->string('to_character_source', 30)->default('original')->after('from_character_source');
            }

            if (! Schema::hasColumn('original_character_relationships', 'from_character_id')) {
                $table->unsignedBigInteger('from_character_id')->nullable()->after('from_original_character_id');
                $table->foreign('from_character_id', 'ocr_from_v1_fk')
                    ->references('id')
                    ->on('characters')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('original_character_relationships', 'to_character_id')) {
                $table->unsignedBigInteger('to_character_id')->nullable()->after('to_original_character_id');
                $table->foreign('to_character_id', 'ocr_to_v1_fk')
                    ->references('id')
                    ->on('characters')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('original_character_relationships')) {
            return;
        }

        Schema::table('original_character_relationships', function (Blueprint $table) {
            if (Schema::hasColumn('original_character_relationships', 'from_character_id')) {
                $table->dropForeign('ocr_from_v1_fk');
                $table->dropColumn('from_character_id');
            }

            if (Schema::hasColumn('original_character_relationships', 'to_character_id')) {
                $table->dropForeign('ocr_to_v1_fk');
                $table->dropColumn('to_character_id');
            }

            if (Schema::hasColumn('original_character_relationships', 'from_character_source')) {
                $table->dropColumn('from_character_source');
            }

            if (Schema::hasColumn('original_character_relationships', 'to_character_source')) {
                $table->dropColumn('to_character_source');
            }
        });
    }
};
