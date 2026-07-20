<?php

namespace Tests\Feature\Admin;

use App\Models\AffiliateProgram;
use App\Models\MonetizationService;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkMonetizationLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkMonetizationLinkAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_work_links_and_settings(): void
    {
        $user = $this->superAdmin();
        [$service, $program] = $this->program();
        $work = Work::factory()->create();

        $payload = [
            'service_id' => $service->id,
            'affiliate_program_id' => $program->id,
            'product_code' => 'B012345678',
            'product_type' => 'volume',
            'title' => '第1巻',
            'button_label' => 'Amazonで見る',
            'campaign_code' => '',
            'availability_status' => 'available',
            'priority' => 5,
            'is_active' => 1,
            'starts_at' => null,
            'ends_at' => null,
            'verification_note' => '手動確認',
        ];

        $this->actingAs($user)
            ->post(
                route(
                    'admin.works.monetization-links.store',
                    $work
                ),
                $payload
            )
            ->assertRedirect(
                route(
                    'admin.works.monetization-links.index',
                    $work
                )
            );

        $link = WorkMonetizationLink::query()->firstOrFail();

        $this->assertNotNull($link->public_key);
        $this->assertSame($work->id, $link->work_id);

        $payload['title'] = '第1巻 更新';
        $payload['availability_status'] = 'checking';

        $this->actingAs($user)
            ->put(
                route(
                    'admin.works.monetization-links.update',
                    [$work, $link]
                ),
                $payload
            )
            ->assertRedirect(
                route(
                    'admin.works.monetization-links.index',
                    $work
                )
            );

        $this->assertDatabaseHas('work_monetization_links', [
            'id' => $link->id,
            'title' => '第1巻 更新',
            'availability_status' => 'checking',
        ]);

        $this->actingAs($user)
            ->patch(
                route(
                    'admin.works.monetization-settings.update',
                    $work
                ),
                [
                    'monetization_enabled' => 1,
                    'monetization_inheritance' => 'self',
                    'isbn' => '9781234567890',
                    'official_store_url' =>
                        'https://example.com/store',
                ]
            )
            ->assertRedirect(
                route(
                    'admin.works.monetization-links.index',
                    $work
                )
            );

        $this->assertDatabaseHas('works', [
            'id' => $work->id,
            'monetization_enabled' => true,
            'monetization_inheritance' => 'self',
            'isbn' => '9781234567890',
        ]);

        $this->actingAs($user)
            ->delete(
                route(
                    'admin.works.monetization-links.destroy',
                    [$work, $link]
                )
            )
            ->assertRedirect(
                route(
                    'admin.works.monetization-links.index',
                    $work
                )
            );

        $this->assertSoftDeleted('work_monetization_links', [
            'id' => $link->id,
        ]);
    }

    public function test_program_must_belong_to_selected_service(): void
    {
        $user = $this->superAdmin();
        [$service, $program] = $this->program();

        $otherService = MonetizationService::query()->create([
            'name' => '別サービス',
            'slug' => 'other-service',
            'category' => 'goods',
            'priority' => 1,
            'is_active' => true,
        ]);

        $work = Work::factory()->create();

        $this->actingAs($user)
            ->from(
                route(
                    'admin.works.monetization-links.index',
                    $work
                )
            )
            ->post(
                route(
                    'admin.works.monetization-links.store',
                    $work
                ),
                [
                    'service_id' => $otherService->id,
                    'affiliate_program_id' => $program->id,
                    'product_code' => 'B012345678',
                    'product_type' => 'series',
                    'availability_status' => 'available',
                    'priority' => 0,
                    'is_active' => 1,
                ]
            )
            ->assertSessionHasErrors('affiliate_program_id');

        $this->assertDatabaseCount('work_monetization_links', 0);
    }

    public function test_non_super_admin_cannot_manage_work_links(): void
    {
        $staff = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => false,
        ]);
        $work = Work::factory()->create();

        $this->actingAs($staff)
            ->get(
                route(
                    'admin.works.monetization-links.index',
                    $work
                )
            )
            ->assertForbidden();
    }

    public function test_link_from_other_work_returns_not_found(): void
    {
        $user = $this->superAdmin();
        [$service, $program] = $this->program();

        $firstWork = Work::factory()->create();
        $secondWork = Work::factory()->create();

        $link = WorkMonetizationLink::query()->create([
            'work_id' => $firstWork->id,
            'service_id' => $service->id,
            'affiliate_program_id' => $program->id,
            'product_code' => 'B012345678',
            'product_type' => 'series',
            'availability_status' => 'available',
            'priority' => 0,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(
                route(
                    'admin.works.monetization-links.edit',
                    [$secondWork, $link]
                )
            )
            ->assertNotFound();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->forceFill(['is_super_admin' => true])->save();

        return $user->refresh();
    }

    private function program(): array
    {
        $service = MonetizationService::query()->create([
            'name' => 'Amazon',
            'slug' => 'amazon',
            'category' => 'goods',
            'priority' => 0,
            'is_active' => true,
        ]);

        $program = AffiliateProgram::query()->create([
            'service_id' => $service->id,
            'name' => 'Amazon公式',
            'provider_name' => 'Amazon',
            'url_template' =>
                'https://www.amazon.co.jp/dp/{product_code}',
            'affiliate_identifier' => 'tag',
            'allowed_hosts' => ['amazon.co.jp'],
            'code_validation_pattern' => '/^[A-Z0-9]{10}$/',
            'priority' => 0,
            'is_default' => true,
            'is_affiliate' => true,
            'is_active' => true,
        ]);

        return [$service, $program];
    }
}
