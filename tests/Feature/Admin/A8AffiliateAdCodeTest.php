<?php

namespace Tests\Feature\Admin;

use App\Models\MonetizationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class A8AffiliateAdCodeTest extends TestCase
{
    use RefreshDatabase;

    private const A8_CODE =
        '<a href="https://px.a8.net/svt/ejp?'
        . 'a8mat=4B8COV+2Z637M+2PEO+BYLJM" rel="nofollow">'
        . 'テレビや雑誌で話題の【ココナラ】電話占いが１分100円から'
        . '</a>'
        . "\n"
        . '<img border="0" width="1" height="1" '
        . 'src="https://www10.a8.net/0.gif?'
        . 'a8mat=4B8COV+2Z637M+2PEO+BYLJM" alt="">';

    public function test_super_admin_can_register_a8_link_pixel_code(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        $this->actingAs($user)
            ->post(
                route('admin.monetization.services.store'),
                $this->payload(self::A8_CODE)
            )
            ->assertRedirect(route('admin.monetization.services.index'))
            ->assertSessionHasNoErrors();

        $service = MonetizationService::query()
            ->where('slug', 'a8-coconala-fortune')
            ->firstOrFail();

        $this->assertSame(self::A8_CODE, $service->impression_script);
        $this->assertSame(
            ['px.a8.net', 'www10.a8.net'],
            $service->allowed_script_hosts
        );
    }

    public function test_a8_code_requires_nofollow(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        $code = str_replace(' rel="nofollow"', '', self::A8_CODE);

        $this->actingAs($user)
            ->from(route('admin.monetization.services.index'))
            ->post(
                route('admin.monetization.services.store'),
                $this->payload($code)
            )
            ->assertSessionHasErrors('impression_script');
    }

    public function test_event_attributes_are_rejected(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        $code = str_replace(
            ' rel="nofollow"',
            ' rel="nofollow" onclick="alert(1)"',
            self::A8_CODE
        );

        $this->actingAs($user)
            ->from(route('admin.monetization.services.index'))
            ->post(
                route('admin.monetization.services.store'),
                $this->payload($code)
            )
            ->assertSessionHasErrors('impression_script');
    }

    public function test_tracking_image_must_be_one_pixel(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        $code = str_replace('width="1"', 'width="300"', self::A8_CODE);

        $this->actingAs($user)
            ->from(route('admin.monetization.services.index'))
            ->post(
                route('admin.monetization.services.store'),
                $this->payload($code)
            )
            ->assertSessionHasErrors('impression_script');
    }

    public function test_unlisted_image_host_is_rejected(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        $payload = $this->payload(self::A8_CODE);
        $payload['allowed_script_hosts_text'] = 'px.a8.net';

        $this->actingAs($user)
            ->from(route('admin.monetization.services.index'))
            ->post(route('admin.monetization.services.store'), $payload)
            ->assertSessionHasErrors('impression_script');
    }

    private function payload(string $code): array
    {
        return [
            'name' => 'A8.net ココナラ電話占い',
            'slug' => 'a8-coconala-fortune',
            'category' => 'other',
            'revenue_model' => 'impression',
                'impression_ad_format' => 'text',
            'description' => 'A8.netテキスト広告',
            'impression_script' => $code,
            'allowed_script_hosts_text' => "px.a8.net\nwww10.a8.net",
            'ad_identifier' => 'a8-coconala-fortune',
            'default_button_label' => null,
            'priority' => 10,
            'is_active' => 1,
        ];
    }
    public function test_data_attributes_are_rejected_on_a8_link(): void
    {
        $user = User::factory()->create([
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        $code = str_replace(
            ' rel="nofollow"',
            ' rel="nofollow" data-extra="x"',
            self::A8_CODE
        );

        $this->actingAs($user)
            ->from(route('admin.monetization.services.index'))
            ->post(
                route('admin.monetization.services.store'),
                $this->payload($code)
            )
            ->assertRedirect(
                route('admin.monetization.services.index')
            )
            ->assertSessionHasErrors('impression_script');
    }

}
