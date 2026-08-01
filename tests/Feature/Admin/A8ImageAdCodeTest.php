<?php

namespace Tests\Feature\Admin;

use App\Models\MonetizationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class A8ImageAdCodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_register_a8_image_ad(): void
    {
        $user = $this->superAdmin();

        $script = '<a href="https://px.a8.net/svt/ejp?'
            . 'a8mat=4B9VHD+87B2DE+2ULW+BZ8OX" rel="nofollow">'
            . '<img border="0" width="320" height="50" alt="" '
            . 'src="https://www22.a8.net/svt/bgt?'
            . 'aid=260801185496&wid=003&eno=01'
            . '&mid=s00000013298002012000&mc=1"></a>'
            . '<img border="0" width="1" height="1" '
            . 'src="https://www13.a8.net/0.gif?'
            . 'a8mat=4B9VHD+87B2DE+2ULW+BZ8OX" alt="">';

        $this->actingAs($user)
            ->post(route('admin.monetization.services.store'), [
                'name' => 'A8画像広告',
                'slug' => 'a8-image-ad',
                'category' => 'other',
                'revenue_model' => 'impression',
                'impression_ad_format' => 'image',
                'impression_script' => $script,
                'allowed_script_hosts_text' =>
                    "px.a8.net\nwww22.a8.net\nwww13.a8.net",
                'ad_identifier' => 'a8-image-320x50',
                'priority' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(
                route('admin.monetization.services.index')
            )
            ->assertSessionHasNoErrors();

        $service = MonetizationService::query()->firstOrFail();

        $this->assertSame(
            'image',
            $service->impression_ad_format
        );
        $this->assertSame($script, $service->impression_script);
        $this->assertSame(
            ['px.a8.net', 'www22.a8.net', 'www13.a8.net'],
            $service->allowed_script_hosts
        );
    }

    public function test_image_ad_rejects_plain_text_link(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->from(route('admin.monetization.services.index'))
            ->post(route('admin.monetization.services.store'), [
                'name' => '形式不一致',
                'slug' => 'wrong-image-ad',
                'category' => 'other',
                'revenue_model' => 'impression',
                'impression_ad_format' => 'image',
                'impression_script' =>
                    '<a href="https://px.a8.net/test" '
                    . 'rel="nofollow">テキスト</a>'
                    . '<img width="1" height="1" '
                    . 'src="https://www13.a8.net/0.gif" alt="">',
                'allowed_script_hosts_text' =>
                    "px.a8.net\nwww13.a8.net",
                'ad_identifier' => 'wrong-image',
                'priority' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(
                route('admin.monetization.services.index')
            )
            ->assertSessionHasErrors('impression_script');

        $this->assertDatabaseCount('monetization_services', 0);
    }

    public function test_image_ad_rejects_unlisted_banner_host(): void
    {
        $user = $this->superAdmin();

        $this->actingAs($user)
            ->from(route('admin.monetization.services.index'))
            ->post(route('admin.monetization.services.store'), [
                'name' => '未許可画像',
                'slug' => 'unlisted-image-host',
                'category' => 'other',
                'revenue_model' => 'impression',
                'impression_ad_format' => 'image',
                'impression_script' =>
                    '<a href="https://px.a8.net/test" '
                    . 'rel="nofollow">'
                    . '<img border="0" width="320" height="50" '
                    . 'src="https://evil.example/banner.gif" alt="">'
                    . '</a><img border="0" width="1" height="1" '
                    . 'src="https://www13.a8.net/0.gif" alt="">',
                'allowed_script_hosts_text' =>
                    "px.a8.net\nwww13.a8.net",
                'ad_identifier' => 'unlisted-image',
                'priority' => 0,
                'is_active' => 1,
            ])
            ->assertRedirect(
                route('admin.monetization.services.index')
            )
            ->assertSessionHasErrors('impression_script');
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);
        $user->forceFill(['is_super_admin' => true])->save();

        return $user;
    }
}
