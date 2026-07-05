<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'must_change_password')) {
                    $table->boolean('must_change_password')->default(false)->after('password');
                }

                if (! Schema::hasColumn('users', 'contributor_application_id')) {
                    $table->unsignedBigInteger('contributor_application_id')->nullable()->after('is_super_admin')->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (Schema::hasColumn('users', 'contributor_application_id')) {
                    $table->dropColumn('contributor_application_id');
                }

                if (Schema::hasColumn('users', 'must_change_password')) {
                    $table->dropColumn('must_change_password');
                }
            });
        }
    }
};
