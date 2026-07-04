<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE works MODIFY status VARCHAR(50) NOT NULL DEFAULT 'draft'");

        Schema::table('works', function (Blueprint $table) {
            $table->string('title_kana')->nullable()->after('title');
            $table->string('genre', 100)->nullable()->after('slug');
            $table->string('original_media', 100)->nullable()->after('genre');
            $table->string('official_url', 500)->nullable()->after('original_media');
            $table->string('guideline_url', 500)->nullable()->after('official_url');
            $table->string('review_status', 50)->default('unreviewed')->after('status');
            $table->foreignId('created_by')->nullable()->after('review_status')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable()->after('updated_by');

            $table->index('status');
            $table->index('review_status');
            $table->index('title');
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);

            $table->dropIndex(['status']);
            $table->dropIndex(['review_status']);
            $table->dropIndex(['title']);

            $table->dropColumn([
                'title_kana',
                'genre',
                'original_media',
                'official_url',
                'guideline_url',
                'review_status',
                'created_by',
                'updated_by',
                'published_at',
            ]);
        });
    }
};
