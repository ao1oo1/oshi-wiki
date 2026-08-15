<?php

namespace Tests\Feature\Admin;

use App\Models\AdsenseSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdsenseSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_adsense_setting_defaults_to_requested_publisher_id(): void
    {
        $setting = AdsenseSetting::current();

        $this->assertTrue(
            (bool) $setting->is_enabled
        );

        $this->assertSame(
            'ca-pub-3916030283806562',
            $setting->publisher_id
        );
    }

    public function test_public_home_outputs_adsense_loader_once(): void
    {
        $setting = AdsenseSetting::current();

        $setting->update([
            'is_enabled' => true,
            'publisher_id' => 'ca-pub-3916030283806562',
        ]);

        $this->assertTrue(
            (bool) $setting->fresh()->is_enabled
        );

        $response = $this->get(
            route('public.home')
        );

        $response->assertOk();

        $html = $response->getContent();

        $this->assertSame(
            1,
            substr_count(
                $html,
                'pagead2.googlesyndication.com/pagead/js/adsbygoogle.js'
            )
        );

        $this->assertStringContainsString(
            'client=ca-pub-3916030283806562',
            $html
        );
    }

    public function test_disabled_adsense_is_not_output_on_public_home(): void
    {
        $setting = AdsenseSetting::current();

        $setting->update([
            'is_enabled' => false,
            'publisher_id' => 'ca-pub-3916030283806562',
        ]);

        $setting = $setting->fresh();

        $this->assertFalse(
            (bool) $setting->is_enabled
        );

        $response = $this->get(
            route('public.home')
        );

        $response->assertOk();

        $this->assertStringNotContainsString(
            'pagead2.googlesyndication.com/pagead/js/adsbygoogle.js',
            $response->getContent()
        );
    }
}
