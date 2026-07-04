<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            if (Schema::hasColumn('characters', 'impression_of_heroine')) {
                $table->dropColumn('impression_of_heroine');
            }
        });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            if (! Schema::hasColumn('characters', 'impression_of_heroine')) {
                $table->text('impression_of_heroine')->nullable()->after('tone_examples');
            }
        });
    }
};
