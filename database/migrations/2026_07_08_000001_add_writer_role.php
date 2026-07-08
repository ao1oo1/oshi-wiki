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

        DB::table('roles')->updateOrInsert(
            ['name' => 'writer'],
            [
                'label' => '一般ユーザー',
                'description' => 'AI執筆補助機能を利用する一般ユーザー',
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'writer')->delete();
        }
    }
};
