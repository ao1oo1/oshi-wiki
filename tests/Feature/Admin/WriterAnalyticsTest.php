<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriterAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_writer_analytics(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $this
            ->actingAs($user)
            ->get(route('admin.analytics.index'))
            ->assertOk()
            ->assertSee('Writerアナリティクス')
            ->assertSee('主要指標')
            ->assertSee('データ使用状況')
            ->assertSee('無料会員の上限到達状況');
    }

    public function test_staff_cannot_view_writer_analytics(): void
    {
        $role = Role::query()->firstOrCreate(
            ['name' => User::ROLE_STAFF],
            [
                'label' => 'スタッフ',
                'description' => '情報入力スタッフ',
            ]
        );

        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_super_admin' => false,
            'status' => 'active',
        ]);

        $this
            ->actingAs($user)
            ->get(route('admin.analytics.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_export_analytics_csv(): void
    {
        $user = User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);

        $this
            ->actingAs($user)
            ->get(route('admin.analytics.export'))
            ->assertOk()
            ->assertHeader(
                'content-type',
                'text/csv; charset=UTF-8'
            );
    }

    public function test_analytics_route_and_menu_exist(): void
    {
        $this->assertTrue(
            \Illuminate\Support\Facades\Route::has(
                'admin.analytics.index'
            )
        );

        $viewFiles = [
            resource_path('views/layouts/navigation.blade.php'),
            resource_path(
                'views/layouts/admin-navigation.blade.php'
            ),
            resource_path(
                'views/admin/partials/navigation.blade.php'
            ),
            resource_path(
                'views/admin/partials/sidebar.blade.php'
            ),
        ];

        $source = collect($viewFiles)
            ->filter(fn (string $path) => is_file($path))
            ->map(fn (string $path) => file_get_contents($path))
            ->implode("\n");

        $this->assertStringContainsString(
            'admin.analytics.index',
            $source
        );
        $this->assertStringContainsString(
            'アナリティクス',
            $source
        );
    }
}
