<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view' => 'ダッシュボード閲覧',
            'works.manage' => '作品管理',
            'characters.manage' => 'キャラクター管理',
            'sources.manage' => '出典管理',
            'submissions.review' => '投稿レビュー',
            'users.manage' => 'ユーザー管理',
            'roles.manage' => '権限管理',
        ];

        foreach ($permissions as $key => $label) {
            Permission::updateOrCreate(
                ['permission_key' => $key],
                ['label' => $label]
            );
        }

        $superAdmin = Role::where('name', 'super_admin')->first();
        $admin = Role::where('name', 'admin')->first();
        $curator = Role::where('name', 'curator')->first();
        $reviewer = Role::where('name', 'reviewer')->first();

        $all = Permission::all();
        $adminPermissions = Permission::whereIn('permission_key', [
            'dashboard.view',
            'works.manage',
            'characters.manage',
            'sources.manage',
            'submissions.review',
        ])->get();

        $curatorPermissions = Permission::whereIn('permission_key', [
            'dashboard.view',
            'works.manage',
            'characters.manage',
            'sources.manage',
        ])->get();

        $reviewerPermissions = Permission::whereIn('permission_key', [
            'dashboard.view',
            'submissions.review',
        ])->get();

        $superAdmin?->permissions()->sync($all->pluck('id'));
        $admin?->permissions()->sync($adminPermissions->pluck('id'));
        $curator?->permissions()->sync($curatorPermissions->pluck('id'));
        $reviewer?->permissions()->sync($reviewerPermissions->pluck('id'));
    }
}
