<?php

namespace Tests\Feature\Admin;

use App\Models\AffiliateProgram;
use App\Models\MonetizationService;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkMonetizationLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LinkVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_verify_one_link(): void
    {
        $user = $this->superAdmin();
        $link = $this->link();

        Http::fake([
            'www.amazon.co.jp/*' => Http::response('', 200),
        ]);

        $this->actingAs($user)
            ->post(route(
                'admin.works.monetization-links.verify',
                [$link->work, $link]
            ))
            ->assertRedirect(route(
                'admin.works.monetization-links.index',
                $link->work
            ));

        $link->refresh();

        $this->assertSame('available', $link->availability_status);
        $this->assertSame('manual', $link->verification_method);
        $this->assertNotNull($link->last_verified_at);
        $this->assertStringContainsString(
            'HTTP 200',
            (string) $link->verification_note
        );
    }

    public function test_404_marks_link_as_ended(): void
    {
        $user = $this->superAdmin();
        $link = $this->link();

        Http::fake([
            'www.amazon.co.jp/*' => Http::response('', 404),
        ]);

        $this->actingAs($user)
            ->post(route(
                'admin.works.monetization-links.verify',
                [$link->work, $link]
            ))
            ->assertRedirect();

        $this->assertSame(
            'ended',
            $link->fresh()->availability_status
        );
    }

    public function test_500_does_not_mark_link_as_ended(): void
    {
        $user = $this->superAdmin();
        $link = $this->link();

        Http::fake([
            'www.amazon.co.jp/*' => Http::response('', 500),
        ]);

        $this->actingAs($user)
            ->post(route(
                'admin.works.monetization-links.verify',
                [$link->work, $link]
            ))
            ->assertRedirect();

        $this->assertSame(
            'checking',
            $link->fresh()->availability_status
        );
    }

    public function test_head_not_supported_falls_back_to_get(): void
    {
        $user = $this->superAdmin();
        $link = $this->link();

        Http::fakeSequence()
            ->push('', 405)
            ->push('', 200);

        $this->actingAs($user)
            ->post(route(
                'admin.works.monetization-links.verify',
                [$link->work, $link]
            ))
            ->assertRedirect();

        Http::assertSentCount(2);
        $this->assertSame(
            'available',
            $link->fresh()->availability_status
        );
    }

    public function test_super_admin_can_verify_all_links(): void
    {
        $user = $this->superAdmin();
        $first = $this->link('B012345678');
        $second = $this->link('B087654321');

        Http::fake([
            'www.amazon.co.jp/*' => Http::response('', 200),
        ]);

        $this->actingAs($user)
            ->post(route(
                'admin.monetization.links.verify-all'
            ))
            ->assertRedirect(route(
                'admin.monetization.analytics.index'
            ));

        $this->assertSame(
            'available',
            $first->fresh()->availability_status
        );
        $this->assertSame(
            'available',
            $second->fresh()->availability_status
        );
    }

    public function test_console_command_verifies_links(): void
    {
        $link = $this->link();

        Http::fake([
            'www.amazon.co.jp/*' => Http::response('', 200),
        ]);

        $this->artisan('monetization:verify-links', ['--limit' => 10])
            ->assertSuccessful();

        $this->assertSame(
            'available',
            $link->fresh()->availability_status
        );
        $this->assertSame(
            'scheduled',
            $link->fresh()->verification_method
        );
    }

    public function test_staff_cannot_verify_links(): void
    {
        $staff = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => false,
        ]);
        $link = $this->link();

        $this->actingAs($staff)
            ->post(route(
                'admin.works.monetization-links.verify',
                [$link->work, $link]
            ))
            ->assertForbidden();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->forceFill(['is_super_admin' => true])->save();

        return $user->refresh();
    }

    private function link(
        string $productCode = 'B012345678'
    ): WorkMonetizationLink {
        $work = Work::factory()->create([
            'status' => 'published',
            'monetization_enabled' => true,
        ]);

        $service = MonetizationService::query()->firstOrCreate(
            ['slug' => 'amazon-verifier'],
            [
                'name' => 'Amazon',
                'category' => 'goods',
                'priority' => 0,
                'is_active' => true,
            ]
        );

        $program = AffiliateProgram::query()->firstOrCreate(
            [
                'service_id' => $service->id,
                'name' => 'Amazon検証',
            ],
            [
                'url_template' =>
                    'https://www.amazon.co.jp/dp/{product_code}',
                'allowed_hosts' => ['amazon.co.jp'],
                'code_validation_pattern' => '/^[A-Z0-9]{10}$/',
                'priority' => 0,
                'is_default' => true,
                'is_affiliate' => true,
                'is_active' => true,
            ]
        );

        return WorkMonetizationLink::query()->create([
            'work_id' => $work->id,
            'service_id' => $service->id,
            'affiliate_program_id' => $program->id,
            'product_code' => $productCode,
            'product_type' => 'series',
            'availability_status' => 'unknown',
            'priority' => 0,
            'is_active' => true,
        ]);
    }
}
