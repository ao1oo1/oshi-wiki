<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saved_prompts', function (Blueprint $table): void {
            $table->boolean('include_work_worldbuilding')
                ->default(false)
                ->after('include_relationship_timeline');

            $table->json('selected_work_worldbuilding_categories')
                ->nullable()
                ->after('include_work_worldbuilding');
        });
    }

    public function down(): void
    {
        Schema::table('saved_prompts', function (Blueprint $table): void {
            $table->dropColumn([
                'include_work_worldbuilding',
                'selected_work_worldbuilding_categories',
            ]);
        });
    }
};
