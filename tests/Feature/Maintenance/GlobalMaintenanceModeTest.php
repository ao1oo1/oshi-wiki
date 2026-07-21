<?php

namespace Tests\Feature\Maintenance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class GlobalMaintenanceModeTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        if (app()->isDownForMaintenance()) {
            Artisan::call('up');
        }

        parent::tearDown();
    }

    public function test_command_can_enable_and_disable_maintenance(): void
    {
        $this->artisan('site:maintenance status')
            ->expectsOutput('現在：メンテナンスモード OFF')
            ->assertSuccessful();

        $this->artisan('site:maintenance on')
            ->assertSuccessful();

        $this->assertTrue(
            app()->isDownForMaintenance()
        );

        $this->artisan('site:maintenance off')
            ->expectsOutput(
                'メンテナンスモードを解除しました。'
            )
            ->assertSuccessful();

        $this->assertFalse(
            app()->isDownForMaintenance()
        );
    }

    public function test_direct_urls_show_custom_maintenance_page(): void
    {
        Artisan::call('site:maintenance', [
            'state' => 'on',
        ]);

        foreach ([
            '/',
            '/works',
            '/writer/login',
            '/writer/dashboard',
            '/admin',
        ] as $uri) {
            $this->get($uri)
                ->assertStatus(503)
                ->assertSee('メンテナンス中')
                ->assertSee('公式Xのお知らせを見る')
                ->assertSee('保存されていなかった入力内容');
        }
    }

    public function test_old_coming_soon_middleware_is_removed(): void
    {
        $this->assertFileDoesNotExist(
            app_path(
                'Http/Middleware/ShowComingSoonForHome.php'
            )
        );

        $bootstrap = file_get_contents(
            base_path('bootstrap/app.php')
        );

        $this->assertIsString($bootstrap);
        $this->assertStringNotContainsString(
            'ShowComingSoonForHome',
            $bootstrap
        );
    }
}
