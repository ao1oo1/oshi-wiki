<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'staff_public_id')) {
                    $table->string('staff_public_id')->nullable()->unique()->after('id');
                }

                if (! Schema::hasColumn('users', 'public_username')) {
                    $table->string('public_username')->nullable()->after('name');
                }

                if (! Schema::hasColumn('users', 'profile_icon_path')) {
                    $table->string('profile_icon_path')->nullable()->after('public_username');
                }

                if (! Schema::hasColumn('users', 'profile_comment')) {
                    $table->text('profile_comment')->nullable()->after('profile_icon_path');
                }

                if (! Schema::hasColumn('users', 'contributor_application_id')) {
                    $table->unsignedBigInteger('contributor_application_id')->nullable()->after('email');
                }

                if (! Schema::hasColumn('users', 'must_change_password')) {
                    $table->boolean('must_change_password')->default(false)->after('password');
                }
            });

            DB::table('users')
                ->whereNull('staff_public_id')
                ->orderBy('id')
                ->get()
                ->each(function ($user) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'staff_public_id' => 'STAFF-' . str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
                            'public_username' => $user->public_username ?: $user->name,
                        ]);
                });
        }

        if (Schema::hasTable('works')) {
            Schema::table('works', function (Blueprint $table) {
                if (! Schema::hasColumn('works', 'helpful_count')) {
                    $table->unsignedInteger('helpful_count')->default(0)->after('status');
                }

                if (! Schema::hasColumn('works', 'contributor_application_id')) {
                    $table->unsignedBigInteger('contributor_application_id')->nullable()->index()->after('id');
                }
            });
        }

        if (Schema::hasTable('characters')) {
            Schema::table('characters', function (Blueprint $table) {
                if (! Schema::hasColumn('characters', 'helpful_count')) {
                    $table->unsignedInteger('helpful_count')->default(0)->after('status');
                }

                if (! Schema::hasColumn('characters', 'contributor_application_id')) {
                    $table->unsignedBigInteger('contributor_application_id')->nullable()->index()->after('id');
                }
            });
        }

        if (! Schema::hasTable('helpful_votes')) {
            Schema::create('helpful_votes', function (Blueprint $table) {
                $table->id();
                $table->string('target_type');
                $table->unsignedBigInteger('target_id');
                $table->string('session_id')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();

                $table->index(['target_type', 'target_id']);
                $table->index('session_id');
                $table->index('ip_address');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('helpful_votes');

        if (Schema::hasTable('characters')) {
            Schema::table('characters', function (Blueprint $table) {
                if (Schema::hasColumn('characters', 'helpful_count')) {
                    $table->dropColumn('helpful_count');
                }

                if (Schema::hasColumn('characters', 'contributor_application_id')) {
                    $table->dropColumn('contributor_application_id');
                }
            });
        }

        if (Schema::hasTable('works')) {
            Schema::table('works', function (Blueprint $table) {
                if (Schema::hasColumn('works', 'helpful_count')) {
                    $table->dropColumn('helpful_count');
                }

                if (Schema::hasColumn('works', 'contributor_application_id')) {
                    $table->dropColumn('contributor_application_id');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                foreach ([
                    'staff_public_id',
                    'public_username',
                    'profile_icon_path',
                    'profile_comment',
                    'contributor_application_id',
                    'must_change_password',
                ] as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
