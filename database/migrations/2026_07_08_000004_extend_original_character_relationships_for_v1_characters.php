<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
                $table->string('from_character_source', 30)
                    ->default('original')
                    ->after('user_id');
            }

            if (! Schema::hasColumn('original_character_relationships', 'to_character_source')) {
                $table->string('to_character_source', 30)
                    ->default('original')
                    ->after('from_character_source');
            }

            if (! Schema::hasColumn('original_character_relationships', 'from_character_id')) {
                $table->foreignId('from_character_id')
                    ->nullable()
                    ->after('from_original_character_id')
                    ->constrained('characters')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('original_character_relationships', 'to_character_id')) {
                $table->foreignId('to_character_id')
                    ->nullable()
                    ->after('to_original_character_id')
                    ->constrained('characters')
                    ->nullOnDelete();
            }
        });

        // 既存の original_character_id カラムを nullable にする
        // v1キャラクターを使う場合は original_character_id が null になるため
        try {
            DB::statement('ALTER TABLE original_character_relationships MODIFY from_original_character_id BIGINT UNSIGNED NULL');
        } catch (Throwable $e) {
            // 既にnullable等の場合は無視
        }

        try {
            DB::statement('ALTER TABLE original_character_relationships MODIFY to_original_character_id BIGINT UNSIGNED NULL');
        } catch (Throwable $e) {
            // 既にnullable等の場合は無視
        }

        DB::table('original_character_relationships')
            ->whereNull('from_character_source')
            ->update(['from_character_source' => 'original']);

        DB::table('original_character_relationships')
            ->whereNull('to_character_source')
            ->update(['to_character_source' => 'original']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('original_character_relationships')) {
            return;
        }

        Schema::table('original_character_relationships', function (Blueprint $table) {
            if (Schema::hasColumn('original_character_relationships', 'from_character_id')) {
                $table->dropConstrainedForeignId('from_character_id');
            }

            if (Schema::hasColumn('original_character_relationships', 'to_character_id')) {
                $table->dropConstrainedForeignId('to_character_id');
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
