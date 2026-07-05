<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('contributor_applications')) {
            Schema::table('contributor_applications', function (Blueprint $table) {
                if (! Schema::hasColumn('contributor_applications', 'paused_at')) {
                    $table->timestamp('paused_at')->nullable()->after('started_at');
                }

                if (! Schema::hasColumn('contributor_applications', 'admin_notes')) {
                    $table->text('admin_notes')->nullable()->after('registered_characters_count');
                }
            });
        }

        if (Schema::hasTable('works')) {
            Schema::table('works', function (Blueprint $table) {
                if (! Schema::hasColumn('works', 'contributor_application_id')) {
                    $table->unsignedBigInteger('contributor_application_id')->nullable()->after('id')->index();
                }
            });
        }

        if (Schema::hasTable('characters')) {
            Schema::table('characters', function (Blueprint $table) {
                if (! Schema::hasColumn('characters', 'contributor_application_id')) {
                    $table->unsignedBigInteger('contributor_application_id')->nullable()->after('id')->index();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('characters') && Schema::hasColumn('characters', 'contributor_application_id')) {
            Schema::table('characters', function (Blueprint $table) {
                $table->dropColumn('contributor_application_id');
            });
        }

        if (Schema::hasTable('works') && Schema::hasColumn('works', 'contributor_application_id')) {
            Schema::table('works', function (Blueprint $table) {
                $table->dropColumn('contributor_application_id');
            });
        }

        if (Schema::hasTable('contributor_applications')) {
            Schema::table('contributor_applications', function (Blueprint $table) {
                if (Schema::hasColumn('contributor_applications', 'paused_at')) {
                    $table->dropColumn('paused_at');
                }

                if (Schema::hasColumn('contributor_applications', 'admin_notes')) {
                    $table->dropColumn('admin_notes');
                }
            });
        }
    }
};
