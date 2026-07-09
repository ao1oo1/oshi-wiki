<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('original_character_relationships')) {
            return;
        }

        Schema::create('original_character_relationships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('from_original_character_id')
                ->constrained('original_characters')
                ->cascadeOnDelete();

            $table->foreignId('to_original_character_id')
                ->constrained('original_characters')
                ->cascadeOnDelete();

            $table->string('called_name')->nullable();
            $table->string('relationship_type')->nullable();
            $table->text('impression')->nullable();
            $table->text('notes')->nullable();

            $table->string('status')->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['from_original_character_id', 'to_original_character_id'], 'ocr_from_to_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('original_character_relationships');
    }
};
