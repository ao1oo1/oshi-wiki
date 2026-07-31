<?php

namespace Tests\Feature\Writer;

use App\Models\ImpressionAdSlot;
use App\Models\MonetizationService;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriterImpressionAdSlotDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_writer_layout_displays_three_configurable_positions(): void
    {
        $role = Role::query()->firstOrCreate(
            ['name' => User::ROLE_WRITER],
            [
                'label' => 'Writer',
                'description' => 'Writer会員',
            ]
        );

        $user = User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
            'is_super_admin' => false,
        ]);

        $service = MonetizationService::query()->create([
            'name' => 'Writer広告',
            'slug' => 'writer-test-ad',
            'category' => 'other',
            'revenue_model' => 'impression',
            'impression_script' =>
                '<script src="https://ads.example/writer.js"></script>',
            'allowed_script_hosts' => ['ads.example'],
            'ad_identifier' => 'writer-test',
            'priority' => 0,
            'is_active' => true,
        ]);

        foreach ([
            'writer_sidebar_1',
            'writer_sidebar_2',
            'writer_page_bottom',
        ] as $position) {
            ImpressionAdSlot::query()->create([
                'monetization_service_id' => $service->id,
                'name' => $position,
                'page_scope' => 'writer_all',
                'position' => $position,
                'device_type' => 'all',
                'priority' => 0,
                'is_active' => true,
            ]);
        }

        $this->actingAs($user)
            ->get(route('writer.dashboard'))
            ->assertOk()
            ->assertSee(
                'data-writer-ad-position="writer_sidebar_1"',
                false
            )
            ->assertSee(
                'data-writer-ad-position="writer_sidebar_2"',
                false
            )
            ->assertSee(
                'data-writer-ad-position="writer_page_bottom"',
                false
            );
    }

    public function test_public_page_does_not_display_writer_positions(): void
    {
        $service = MonetizationService::query()->create([
            'name' => 'Writer限定広告',
            'slug' => 'writer-only-ad',
            'category' => 'other',
            'revenue_model' => 'impression',
            'impression_script' =>
                '<script src="https://ads.example/writer-only.js"></script>',
            'allowed_script_hosts' => ['ads.example'],
            'ad_identifier' => 'writer-only',
            'priority' => 0,
            'is_active' => true,
        ]);

        ImpressionAdSlot::query()->create([
            'monetization_service_id' => $service->id,
            'name' => 'Writer限定',
            'page_scope' => 'writer_all',
            'position' => 'writer_page_bottom',
            'device_type' => 'all',
            'priority' => 0,
            'is_active' => true,
        ]);

        $this->get(route('public.home'))
            ->assertOk()
            ->assertDontSee('writer-only.js', false);
    }
}
