<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('original_characters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('name_kana')->nullable();
            $table->string('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('school_grade')->nullable();
            $table->string('first_person')->nullable();

            $table->text('speech_style')->nullable();
            $table->text('speech_examples')->nullable();
            $table->text('personality')->nullable();
            $table->text('appearance')->nullable();
            $table->text('background')->nullable();

            $table->boolean('is_main_character')->default(false);
            $table->text('important_points')->nullable();
            $table->text('ng_points')->nullable();
            $table->text('notes')->nullable();

            $table->string('status')->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('original_characters');
    }
};
