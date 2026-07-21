<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSecurityControlsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        config()->set(
            'security.blocked_ips',
            []
        );

        parent::tearDown();
    }

    public function test_blocked_ip_cannot_access_sensitive_routes(): void
    {
        config()->set(
            'security.blocked_ips',
            ['203.0.113.10']
        );

        $server = [
            'REMOTE_ADDR' => '203.0.113.10',
        ];

        $this->withServerVariables($server)
            ->get('/login')
            ->assertForbidden();

        $this->withServerVariables($server)
            ->get('/register')
            ->assertForbidden();

        $this->withServerVariables($server)
            ->get('/admin/login')
            ->assertForbidden();
    }

    public function test_cidr_block_is_supported(): void
    {
        config()->set(
            'security.blocked_ips',
            ['198.51.100.0/24']
        );

        $this->withServerVariables([
            'REMOTE_ADDR' => '198.51.100.25',
        ])->get('/writer/login')
            ->assertForbidden();
    }

    public function test_login_is_locked_after_repeated_failures(): void
    {
        config()->set(
            'security.login.account_max_attempts',
            5
        );
        config()->set(
            'security.login.ip_max_attempts',
            20
        );
        config()->set(
            'security.login.decay_seconds',
            900
        );

        $email = 'security@example.com';
        $server = [
            'REMOTE_ADDR' => '203.0.113.20',
        ];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables($server)
                ->from('/login')
                ->post('/login', [
                    'email' => $email,
                    'password' => 'wrong-password',
                ])
                ->assertSessionHasErrors('email');
        }

        $response = $this->withServerVariables($server)
            ->from('/login')
            ->post('/login', [
                'email' => $email,
                'password' => 'wrong-password',
            ]);

        $response
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $messages = session('errors')
            ?->get('email', []);

        $this->assertNotEmpty($messages);
        $this->assertStringContainsString(
            '一時的に制限',
            (string) $messages[0]
        );
    }

    public function test_successful_login_still_works(): void
    {
        $user = User::factory()->create();

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.30',
        ])->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect();
    }
}
