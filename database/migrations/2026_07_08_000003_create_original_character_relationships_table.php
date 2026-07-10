<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $alreadyMigrated = DB::table('migrations')
            ->where('migration', '2026_07_08_000003_create_original_character_relationships_table')
            ->exists();

        if (! $alreadyMigrated && Schema::hasTable('original_character_relationships')) {
            Schema::dropIfExists('original_character_relationships');
        }

        if (Schema::hasTable('original_character_relationships')) {
            return;
        }

        Schema::create('original_character_relationships', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->string('from_character_source', 30)->default('original');
            $table->string('to_character_source', 30)->default('original');

            $table->unsignedBigInteger('from_original_character_id')->nullable();
            $table->unsignedBigInteger('to_original_character_id')->nullable();

            $table->unsignedBigInteger('from_character_id')->nullable();
            $table->unsignedBigInteger('to_character_id')->nullable();

            $table->string('called_name')->nullable();
            $table->string('relationship_type')->nullable();
            $table->text('impression')->nullable();
            $table->text('notes')->nullable();

            $table->string('status')->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status'], 'ocr_user_status_idx');
            $table->index(['from_original_character_id', 'to_original_character_id'], 'ocr_original_from_to_idx');
            $table->index(['from_character_id', 'to_character_id'], 'ocr_v1_from_to_idx');

            $table->foreign('user_id', 'ocr_user_fk')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('from_original_character_id', 'ocr_from_original_fk')
                ->references('id')
                ->on('original_characters')
                ->nullOnDelete();

            $table->foreign('to_original_character_id', 'ocr_to_original_fk')
                ->references('id')
                ->on('original_characters')
                ->nullOnDelete();

            $table->foreign('from_character_id', 'ocr_from_v1_fk')
                ->references('id')
                ->on('characters')
                ->nullOnDelete();

            $table->foreign('to_character_id', 'ocr_to_v1_fk')
                ->references('id')
                ->on('characters')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('original_character_relationships');
    }
};
