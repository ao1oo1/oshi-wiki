<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        $values = ['name' => 'writer'];

        if (Schema::hasColumn('roles', 'label')) {
            $values['label'] = '一般ユーザー';
        }

        if (Schema::hasColumn('roles', 'display_name')) {
            $values['display_name'] = '一般ユーザー';
        }

        if (Schema::hasColumn('roles', 'description')) {
            $values['description'] = 'AI執筆補助機能を利用する一般ユーザー';
        }

        if (Schema::hasColumn('roles', 'updated_at')) {
            $values['updated_at'] = now();
        }

        if (Schema::hasColumn('roles', 'created_at')) {
            $values['created_at'] = DB::raw('COALESCE(created_at, NOW())');
        }

        DB::table('roles')->updateOrInsert(
            ['name' => 'writer'],
            $values
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'writer')->delete();
        }
    }
};
