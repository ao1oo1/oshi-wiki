<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monetization_services', function (Blueprint $table): void {
            $table->string('impression_ad_format', 20)
                ->nullable()
                ->after('revenue_model');
        });

        DB::table('monetization_services')
            ->where('revenue_model', 'impression')
            ->whereNull('impression_ad_format')
            ->update(['impression_ad_format' => 'script']);
    }

    public function down(): void
    {
        Schema::table('monetization_services', function (Blueprint $table): void {
            $table->dropColumn('impression_ad_format');
        });
    }
};
