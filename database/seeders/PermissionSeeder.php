<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 現在の権限仕様では、最高管理者は users.is_super_admin で判定する。
        // staff / writer は roles.name で判定する。
        // permissions テーブルは今後の細かい権限制御用に残すが、
        // super_admin ロール前提の権限付与は行わない。
    }
}
