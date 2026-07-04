<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('tag_work')) {
            Schema::create('tag_work', function (Blueprint $table) {
                $table->id();

                $table->foreignId('work_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->foreignId('tag_id')
                    ->constrained()
                    ->cascadeOnDelete();

                $table->timestamps();

                $table->unique(['work_id', 'tag_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tag_work');
    }
};
