<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'staff',
                'label' => '管理スタッフ',
                'description' => 'Oshi-Wikiの情報入力を行う管理スタッフ',
            ],
            [
                'name' => 'writer',
                'label' => '一般ユーザー',
                'description' => 'AI執筆補助機能を利用する一般ユーザー',
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                [
                    'label' => $role['label'],
                    'description' => $role['description'],
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, NOW())'),
                ]
            );
        }
    }
}
