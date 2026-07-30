<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monetization_services', function (Blueprint $table): void {
            $table->string('revenue_model', 30)
                ->default('affiliate_link')
                ->after('category');
            $table->text('impression_script')
                ->nullable()
                ->after('description');
            $table->json('allowed_script_hosts')
                ->nullable()
                ->after('impression_script');
            $table->string('ad_identifier', 255)
                ->nullable()
                ->after('allowed_script_hosts');
        });
    }

    public function down(): void
    {
        Schema::table('monetization_services', function (Blueprint $table): void {
            $table->dropColumn([
                'revenue_model',
                'impression_script',
                'allowed_script_hosts',
                'ad_identifier',
            ]);
        });
    }
};
