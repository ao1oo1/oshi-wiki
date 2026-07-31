<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table): void {
            $table->text('search_keywords')->nullable()->after('title_kana');
        });

        Schema::table('characters', function (Blueprint $table): void {
            $table->text('search_keywords')->nullable()->after('name_english');
        });

        Schema::create('seo_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('site_title')->nullable();
            $table->text('site_description')->nullable();
            $table->text('site_keywords')->nullable();
            $table->string('google_site_verification')->nullable();
            $table->text('default_og_image_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_settings');
        Schema::table('characters', fn (Blueprint $table) => $table->dropColumn('search_keywords'));
        Schema::table('works', fn (Blueprint $table) => $table->dropColumn('search_keywords'));
    }
};
