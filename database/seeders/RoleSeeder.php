<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'super_admin', 'label' => '最高管理者'],
            ['name' => 'admin', 'label' => '管理者'],
            ['name' => 'curator', 'label' => 'キュレーター'],
            ['name' => 'reviewer', 'label' => 'レビュアー'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
