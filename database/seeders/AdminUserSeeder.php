<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('OSHI_SUPER_ADMIN_EMAIL');
        $password = env('OSHI_SUPER_ADMIN_PASSWORD');
        $name = env('OSHI_SUPER_ADMIN_NAME', '最高管理者');

        if (! $email || ! $password) {
            $this->command?->warn('OSHI_SUPER_ADMIN_EMAIL / OSHI_SUPER_ADMIN_PASSWORD が未設定のため、最高管理者ユーザーは作成しません。');

            return;
        }

        $user = User::firstOrNew(['email' => $email]);

        $user->forceFill([
            'name' => $name,
            'password' => Hash::make($password),
            'role_id' => null,
            'status' => 'active',
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ])->save();

        $this->command?->info("最高管理者ユーザーを作成・更新しました: {$email}");
    }
}
