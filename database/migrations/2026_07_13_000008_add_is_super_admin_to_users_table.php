<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            ! Schema::hasTable('users')
            || Schema::hasColumn('users', 'is_super_admin')
        ) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_super_admin')
                ->default(false)
                ->after('role_id')
                ->index();
        });
    }

    public function down(): void
    {
        if (
            ! Schema::hasTable('users')
            || ! Schema::hasColumn('users', 'is_super_admin')
        ) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['is_super_admin']);
            $table->dropColumn('is_super_admin');
        });
    }
};
