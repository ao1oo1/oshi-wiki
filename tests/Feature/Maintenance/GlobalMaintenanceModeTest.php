<?php

namespace Tests\Feature\Maintenance;

use App\Models\Role;
use App\Models\User;
use App\Services\ScopedMaintenanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalMaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        app(ScopedMaintenanceService::class)
            ->disable(['all']);

        parent::tearDown();
    }

    public function test_scopes_can_be_combined(): void
    {
        $this->artisan(
            'site:maintenance',
            [
                'state' => 'on',
                'scopes' => ['public', 'writer'],
            ]
        )->assertSuccessful();

        $this->get('/')
            ->assertStatus(503);

        $this->get('/writer/login')
            ->assertStatus(503);

        $this->get('/privacy')
            ->assertStatus(200);

        $this->artisan(
            'site:maintenance',
            [
                'state' => 'off',
                'scopes' => ['writer'],
            ]
        )->assertSuccessful();

        $this->get('/')
            ->assertStatus(503);

        $this->get('/writer/login')
            ->assertStatus(200);
    }

    public function test_super_admin_admin_pages_are_excluded(): void
    {
        $this->artisan(
            'site:maintenance',
            [
                'state' => 'on',
                'scopes' => ['contributor'],
            ]
        )->assertSuccessful();

        $superAdmin = $this->userWithRole(
            User::ROLE_STAFF,
            true
        );

        $contributor = $this->userWithRole(
            User::ROLE_STAFF
        );

        $this->actingAs($superAdmin)
            ->get('/admin')
            ->assertStatus(200);

        $this->actingAs($contributor)
            ->get('/admin')
            ->assertStatus(503)
            ->assertSee('メンテナンス中');
    }

    public function test_each_scope_is_independent(): void
    {
        $service = app(
            ScopedMaintenanceService::class
        );

        $service->enable(['public']);
        $this->get('/')->assertStatus(503);
        $this->get('/writer/login')->assertStatus(200);

        $service->disable(['public']);
        $service->enable(['writer']);
        $this->get('/')->assertStatus(200);
        $this->get('/writer/login')->assertStatus(503);
    }

    private function userWithRole(
        string $roleName,
        bool $isSuperAdmin = false
    ): User {
        $role = Role::query()->firstOrCreate(
            ['name' => $roleName],
            [
                'label' => $roleName,
                'description' => $roleName,
            ]
        );

        $user = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
            'must_change_password' => false,
        ]);

        if ($isSuperAdmin) {
            $user->forceFill([
                'is_super_admin' => true,
            ])->save();
        }

        return $user->fresh();
    }
}
