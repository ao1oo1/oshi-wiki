<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_canon_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->string('timing', 255)->nullable();
            $table->string('event_name', 255);
            $table->string('event_status', 50)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['work_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_canon_events');
    }
};
