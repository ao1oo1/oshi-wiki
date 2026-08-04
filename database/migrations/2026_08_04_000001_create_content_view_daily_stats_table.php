<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('content_view_daily_stats')) return;
        Schema::create('content_view_daily_stats', function (Blueprint $table): void {
            $table->id();
            $table->morphs('viewable');
            $table->date('viewed_on');
            $table->unsignedBigInteger('view_count')->default(0);
            $table->timestamps();
            $table->unique(
                ['viewable_type','viewable_id','viewed_on'],
                'content_view_daily_unique'
            );
            $table->index(
                ['viewable_type','viewed_on','view_count'],
                'content_view_daily_ranking'
            );
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('content_view_daily_stats');
    }
};
