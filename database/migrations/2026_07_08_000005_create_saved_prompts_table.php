<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('saved_prompts')) {
            return;
        }

        Schema::create('saved_prompts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('title');
            $table->string('category')->default('other');
            $table->string('purpose')->nullable();

            $table->text('synopsis')->nullable();
            $table->longText('prompt_body');
            $table->text('notes')->nullable();

            $table->string('status')->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('category');
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_prompts');
    }
};
