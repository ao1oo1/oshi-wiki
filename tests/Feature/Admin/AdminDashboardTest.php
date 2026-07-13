<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_admin_dashboard(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_super_admin_can_view_admin_dashboard(): void
    {
        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Oshi-Wiki');
    }

    public function test_writer_cannot_view_admin_dashboard(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertForbidden();
    }

    public function test_dashboard_route_redirects_super_admin_to_admin_dashboard(): void
    {
        $user = $this->createSuperAdmin();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(
            route('admin.dashboard')
        );
    }

    public function test_dashboard_route_redirects_writer_to_writer_dashboard(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(
            route('writer.dashboard')
        );
    }

    private function createSuperAdmin(): User
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $user->forceFill([
            'is_super_admin' => true,
        ])->save();

        return $user->refresh();
    }
}
