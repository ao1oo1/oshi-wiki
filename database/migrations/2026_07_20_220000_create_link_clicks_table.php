<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('link_clicks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('work_monetization_link_id')
                ->constrained('work_monetization_links')
                ->cascadeOnDelete();
            $table->foreignId('work_id')
                ->constrained('works')
                ->cascadeOnDelete();
            $table->foreignId('service_id')
                ->constrained('monetization_services')
                ->cascadeOnDelete();
            $table->foreignId('affiliate_program_id')
                ->constrained('affiliate_programs')
                ->cascadeOnDelete();
            $table->char('visitor_hash', 64);
            $table->char('user_agent_hash', 64)->nullable();
            $table->string('referer_host', 255)->nullable();
            $table->string('referer_path', 1000)->nullable();
            $table->dateTime('clicked_at');
            $table->timestamps();

            $table->index(
                ['work_monetization_link_id', 'clicked_at'],
                'link_clicks_link_clicked_index'
            );
            $table->index(
                ['work_id', 'clicked_at'],
                'link_clicks_work_clicked_index'
            );
            $table->index(
                ['visitor_hash', 'clicked_at'],
                'link_clicks_visitor_clicked_index'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_clicks');
    }
};
