<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('works', function (Blueprint $table): void {
            if (! Schema::hasColumn('works', 'seo_title')) {
                $table->string('seo_title', 255)
                    ->nullable()
                    ->after('title');
            }

            if (! Schema::hasColumn('works', 'seo_description')) {
                $table->string('seo_description', 320)
                    ->nullable()
                    ->after('seo_title');
            }
        });

        Schema::table('characters', function (Blueprint $table): void {
            if (! Schema::hasColumn('characters', 'seo_title')) {
                $table->string('seo_title', 255)
                    ->nullable()
                    ->after('name');
            }

            if (! Schema::hasColumn(
                'characters',
                'seo_description'
            )) {
                $table->string('seo_description', 320)
                    ->nullable()
                    ->after('seo_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('works', function (Blueprint $table): void {
            if (Schema::hasColumn('works', 'seo_description')) {
                $table->dropColumn('seo_description');
            }

            if (Schema::hasColumn('works', 'seo_title')) {
                $table->dropColumn('seo_title');
            }
        });

        Schema::table('characters', function (Blueprint $table): void {
            if (
                Schema::hasColumn(
                    'characters',
                    'seo_description'
                )
            ) {
                $table->dropColumn('seo_description');
            }

            if (Schema::hasColumn('characters', 'seo_title')) {
                $table->dropColumn('seo_title');
            }
        });
    }
};
