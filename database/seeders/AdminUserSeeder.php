<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'super_admin')->firstOrFail();

        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'role_id' => $role->id,
                'name' => '最高管理者',
                'password' => Hash::make('oshiwiki-admin'),
                'status' => 'active',
            ]
        );
    }
}
