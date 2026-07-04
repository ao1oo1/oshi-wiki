<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('name_kana')->nullable();
            $table->string('age')->nullable();
            $table->string('affiliation')->nullable();
            $table->string('grade_class')->nullable();
            $table->string('first_person')->nullable();

            $table->text('tone')->nullable();
            $table->text('tone_examples')->nullable();
            $table->text('impression_of_heroine')->nullable();
            $table->text('personality')->nullable();
            $table->text('appearance')->nullable();
            $table->text('background')->nullable();

            $table->string('status')->default('draft');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['work_id', 'name']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
