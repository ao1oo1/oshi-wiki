<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarContactFormLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sidebar_has_public_contact_form_link(): void
    {
        $admin = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(
                'href="' . route('public.contact.create') . '"',
                false
            )
            ->assertSee('お問い合わせフォーム');
    }

    public function test_writer_sidebar_has_public_contact_form_link(): void
    {
        $role = Role::query()->firstOrCreate(
            ['name' => User::ROLE_WRITER],
            [
                'label' => '一般執筆ユーザー',
                'description' => '小説執筆補助機能',
            ]
        );

        $writer = User::factory()->create([
            'status' => 'active',
            'role_id' => $role->id,
            'is_super_admin' => false,
        ]);

        $this->actingAs($writer)
            ->get(route('writer.dashboard'))
            ->assertOk()
            ->assertSee(
                'href="' . route('public.contact.create') . '"',
                false
            )
            ->assertSee('お問い合わせフォーム');
    }

    public function test_all_sidebar_sources_use_named_contact_route(): void
    {
        $paths = [
            'views/admin/partials/navigation.blade.php',
            'views/admin/partials/mobile-navigation.blade.php',
            'views/writer/partials/navigation.blade.php',
            'views/writer/original_characters/_layout_start.blade.php',
            'views/writer/original_characters/_layout.blade.php',
        ];

        foreach ($paths as $path) {
            $source = file_get_contents(resource_path($path));

            $this->assertStringContainsString(
                "route('public.contact.create')",
                $source,
                $path
            );

            $this->assertStringContainsString(
                'お問い合わせフォーム',
                $source,
                $path
            );
        }
    }
}
