<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('saved_prompts')) {
            return;
        }

        Schema::table('saved_prompts', function (Blueprint $table) {
            if (! Schema::hasColumn('saved_prompts', 'include_relationship_timeline')) {
                $table->boolean('include_relationship_timeline')
                    ->default(false)
                    ->after('selected_character_refs');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('saved_prompts')) {
            return;
        }

        Schema::table('saved_prompts', function (Blueprint $table) {
            if (Schema::hasColumn('saved_prompts', 'include_relationship_timeline')) {
                $table->dropColumn('include_relationship_timeline');
            }
        });
    }
};
