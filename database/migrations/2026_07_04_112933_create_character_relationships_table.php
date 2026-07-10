<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('character_relationships')) {
            return;
        }

        Schema::create('character_relationships', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('from_character_id')
                ->constrained('characters')
                ->cascadeOnDelete();

            $table->foreignId('to_character_id')
                ->constrained('characters')
                ->cascadeOnDelete();

            $table->string('called_name')->nullable();
            $table->string('relationship')->nullable();
            $table->text('impression')->nullable();
            $table->text('notes')->nullable();

            $table->string('status')->default('draft');

            $table->timestamps();
            $table->softDeletes();

            $table->index('work_id');
            $table->index('from_character_id');
            $table->index('to_character_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_relationships');
    }
};
