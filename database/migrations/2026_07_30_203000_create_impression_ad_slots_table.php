<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impression_ad_slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('monetization_service_id')
                ->constrained('monetization_services')
                ->restrictOnDelete();
            $table->string('name', 120);
            $table->string('page_scope', 40);
            $table->string('position', 30);
            $table->string('device_type', 20)->default('all');
            $table->unsignedInteger('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['page_scope', 'position', 'is_active', 'priority'],
                'impression_ad_slots_display_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impression_ad_slots');
    }
};
