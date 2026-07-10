<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AccessControlSeeder extends Seeder
{
    public function run(): void
    {
        $staffRoleId = $this->upsertRole(
            name: 'staff',
            label: '管理スタッフ',
            description: 'Oshi-Wikiの情報入力を行う管理スタッフ'
        );

        $writerRoleId = $this->upsertRole(
            name: 'writer',
            label: '一般ユーザー',
            description: 'AI執筆補助機能を利用する一般ユーザー'
        );

        $this->upsertOptionalSuperAdmin();
        $this->upsertOptionalStaff($staffRoleId);
        $this->upsertOptionalWriter($writerRoleId);
    }

    private function upsertRole(string $name, string $label, string $description): int
    {
        DB::table('roles')->updateOrInsert(
            ['name' => $name],
            [
                'label' => $label,
                'description' => $description,
                'updated_at' => now(),
                'created_at' => DB::raw('COALESCE(created_at, NOW())'),
            ]
        );

        return (int) DB::table('roles')->where('name', $name)->value('id');
    }

    private function upsertOptionalSuperAdmin(): void
    {
        $email = env('OSHI_SUPER_ADMIN_EMAIL');
        $password = env('OSHI_SUPER_ADMIN_PASSWORD');
        $name = env('OSHI_SUPER_ADMIN_NAME', '最高管理者');

        if (! $email || ! $password) {
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
    }

    private function upsertOptionalStaff(int $staffRoleId): void
    {
        $email = env('OSHI_STAFF_EMAIL');
        $password = env('OSHI_STAFF_PASSWORD');
        $name = env('OSHI_STAFF_NAME', '管理スタッフ');

        if (! $email || ! $password) {
            return;
        }

        $user = User::firstOrNew(['email' => $email]);

        $user->forceFill([
            'name' => $name,
            'password' => Hash::make($password),
            'role_id' => $staffRoleId,
            'status' => 'active',
            'is_super_admin' => false,
            'email_verified_at' => now(),
        ])->save();
    }

    private function upsertOptionalWriter(int $writerRoleId): void
    {
        $email = env('OSHI_WRITER_EMAIL');
        $password = env('OSHI_WRITER_PASSWORD');
        $name = env('OSHI_WRITER_NAME', '一般ユーザー');

        if (! $email || ! $password) {
            return;
        }

        $user = User::firstOrNew(['email' => $email]);

        $user->forceFill([
            'name' => $name,
            'password' => Hash::make($password),
            'role_id' => $writerRoleId,
            'status' => 'active',
            'is_super_admin' => false,
            'email_verified_at' => now(),
        ])->save();
    }
}
