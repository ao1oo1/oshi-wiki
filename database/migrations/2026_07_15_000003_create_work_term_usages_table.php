<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_term_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->string('term', 255);
            $table->text('meaning')->nullable();
            $table->text('usage_example')->nullable();
            $table->timestamps();

            $table->index(['work_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_term_usages');
    }
};
