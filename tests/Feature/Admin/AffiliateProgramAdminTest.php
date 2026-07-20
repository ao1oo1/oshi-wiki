<?php

namespace Tests\Feature\Admin;

use App\Models\AffiliateProgram;
use App\Models\MonetizationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateProgramAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_affiliate_programs(): void
    {
        $user = $this->superAdmin();
        $service = $this->service();

        $payload = [
            'service_id' => $service->id,
            'name' => 'もしも Amazon',
            'provider_name' => 'もしもアフィリエイト',
            'url_template' =>
                'https://www.amazon.co.jp/dp/{product_code}'
                . '?tag={affiliate_identifier}',
            'affiliate_identifier' => 'oshi-22',
            'additional_parameters_text' =>
                '{"source":"oshi-wiki"}',
            'allowed_hosts_text' =>
                "amazon.co.jp\nwww.amazon.co.jp",
            'code_validation_pattern' => '/^[A-Z0-9]{10}$/',
            'code_example' => 'B012345678',
            'priority' => 10,
            'is_default' => 1,
            'is_affiliate' => 1,
            'is_active' => 1,
            'starts_at' => null,
            'ends_at' => null,
        ];

        $this->actingAs($user)
            ->post(
                route('admin.monetization.programs.store'),
                $payload
            )
            ->assertRedirect(
                route('admin.monetization.programs.index')
            );

        $program = AffiliateProgram::query()->firstOrFail();

        $this->assertSame(
            ['amazon.co.jp', 'www.amazon.co.jp'],
            $program->allowed_hosts
        );
        $this->assertSame(
            ['source' => 'oshi-wiki'],
            $program->additional_parameters
        );
        $this->assertTrue($program->is_default);

        $payload['name'] = 'もしも Amazon 更新';
        $payload['is_active'] = 0;

        $this->actingAs($user)
            ->put(
                route(
                    'admin.monetization.programs.update',
                    $program
                ),
                $payload
            )
            ->assertRedirect(
                route('admin.monetization.programs.index')
            );

        $this->assertDatabaseHas('affiliate_programs', [
            'id' => $program->id,
            'name' => 'もしも Amazon 更新',
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->delete(
                route(
                    'admin.monetization.programs.destroy',
                    $program
                )
            )
            ->assertRedirect(
                route('admin.monetization.programs.index')
            );

        $this->assertSoftDeleted('affiliate_programs', [
            'id' => $program->id,
        ]);
    }

    public function test_only_one_default_program_exists_per_service(): void
    {
        $user = $this->superAdmin();
        $service = $this->service();

        $first = AffiliateProgram::query()->create(
            $this->modelPayload($service, '最初', true)
        );

        $payload = $this->requestPayload($service, '次', true);

        $this->actingAs($user)
            ->post(
                route('admin.monetization.programs.store'),
                $payload
            )
            ->assertRedirect(
                route('admin.monetization.programs.index')
            );

        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue(
            AffiliateProgram::query()
                ->where('name', '次')
                ->firstOrFail()
                ->is_default
        );
    }

    public function test_invalid_template_and_host_are_rejected(): void
    {
        $user = $this->superAdmin();
        $service = $this->service();

        $payload = $this->requestPayload($service, '不正', false);
        $payload['url_template'] =
            'http://example.com/{unknown}';
        $payload['allowed_hosts_text'] =
            'https://example.com/path';

        $this->actingAs($user)
            ->from(route('admin.monetization.programs.index'))
            ->post(
                route('admin.monetization.programs.store'),
                $payload
            )
            ->assertRedirect(
                route('admin.monetization.programs.index')
            )
            ->assertSessionHasErrors('url_template');

        $this->assertDatabaseCount('affiliate_programs', 0);
    }

    public function test_non_super_admin_cannot_manage_programs(): void
    {
        $staff = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => false,
        ]);

        $this->actingAs($staff)
            ->get(route('admin.monetization.programs.index'))
            ->assertForbidden();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->forceFill(['is_super_admin' => true])->save();

        return $user->refresh();
    }

    private function service(): MonetizationService
    {
        return MonetizationService::query()->create([
            'name' => 'Amazon',
            'slug' => 'amazon',
            'category' => 'goods',
            'priority' => 0,
            'is_active' => true,
        ]);
    }

    private function requestPayload(
        MonetizationService $service,
        string $name,
        bool $default
    ): array {
        return [
            'service_id' => $service->id,
            'name' => $name,
            'provider_name' => 'ASP',
            'url_template' =>
                'https://shop.example.com/{product_code}'
                . '?tag={affiliate_identifier}',
            'affiliate_identifier' => 'tag',
            'additional_parameters_text' => '',
            'allowed_hosts_text' => 'shop.example.com',
            'code_validation_pattern' => '',
            'code_example' => '',
            'priority' => 0,
            'is_default' => $default ? 1 : 0,
            'is_affiliate' => 1,
            'is_active' => 1,
            'starts_at' => null,
            'ends_at' => null,
        ];
    }

    private function modelPayload(
        MonetizationService $service,
        string $name,
        bool $default
    ): array {
        return [
            'service_id' => $service->id,
            'name' => $name,
            'provider_name' => 'ASP',
            'url_template' =>
                'https://shop.example.com/{product_code}',
            'affiliate_identifier' => 'tag',
            'allowed_hosts' => ['shop.example.com'],
            'priority' => 0,
            'is_default' => $default,
            'is_affiliate' => true,
            'is_active' => true,
        ];
    }
}
