<?php

namespace Tests\Feature\Seo;

use App\Http\Middleware\ApplyNoindexToPrivatePages;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PrivatePagesNoindexTest extends TestCase
{
    #[DataProvider('privatePathProvider')]
    public function test_private_paths_receive_noindex(
        string $path
    ): void {
        $middleware = new ApplyNoindexToPrivatePages();
        $request = Request::create($path, 'GET');

        $response = $middleware->handle(
            $request,
            fn () => new Response(
                '<!doctype html><html><head>'
                . '<title>Private</title>'
                . '</head><body>Private</body></html>',
                200,
                ['Content-Type' => 'text/html; charset=UTF-8']
            )
        );

        $this->assertSame(
            'noindex, follow',
            $response->headers->get('X-Robots-Tag')
        );

        $this->assertStringContainsString(
            '<meta name="robots" content="noindex,follow">',
            (string) $response->getContent()
        );
    }

    #[DataProvider('publicPathProvider')]
    public function test_public_paths_are_not_noindexed(
        string $path
    ): void {
        $middleware = new ApplyNoindexToPrivatePages();
        $request = Request::create($path, 'GET');

        $response = $middleware->handle(
            $request,
            fn () => new Response(
                '<!doctype html><html><head>'
                . '<title>Public</title>'
                . '</head><body>Public</body></html>',
                200,
                ['Content-Type' => 'text/html; charset=UTF-8']
            )
        );

        $this->assertNull(
            $response->headers->get('X-Robots-Tag')
        );

        $this->assertStringNotContainsString(
            'content="noindex,follow"',
            (string) $response->getContent()
        );
    }

    public function test_existing_index_robots_meta_is_replaced(): void
    {
        $middleware = new ApplyNoindexToPrivatePages();
        $request = Request::create('/login', 'GET');

        $response = $middleware->handle(
            $request,
            fn () => new Response(
                '<html><head>'
                . '<meta name="robots" '
                . 'content="index, follow, max-image-preview:large">'
                . '</head><body></body></html>',
                200,
                ['Content-Type' => 'text/html']
            )
        );

        $content = (string) $response->getContent();

        $this->assertStringContainsString(
            '<meta name="robots" content="noindex,follow">',
            $content
        );

        $this->assertStringNotContainsString(
            'index, follow, max-image-preview:large',
            $content
        );

        $this->assertSame(
            1,
            substr_count($content, 'name="robots"')
        );
    }

    public function test_real_login_response_has_noindex(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertHeader(
                'X-Robots-Tag',
                'noindex, follow'
            )
            ->assertSee(
                '<meta name="robots" content="noindex,follow">',
                false
            );
    }

    public function test_real_register_response_has_noindex(): void
    {
        $response = $this->get('/register');

        $response
            ->assertOk()
            ->assertHeader(
                'X-Robots-Tag',
                'noindex, follow'
            )
            ->assertSee(
                '<meta name="robots" content="noindex,follow">',
                false
            );
    }

    public function test_real_forgot_password_response_has_noindex(): void
    {
        $response = $this->get('/forgot-password');

        $response
            ->assertOk()
            ->assertHeader(
                'X-Robots-Tag',
                'noindex, follow'
            )
            ->assertSee(
                '<meta name="robots" content="noindex,follow">',
                false
            );
    }

    public function test_admin_redirect_has_noindex_header(): void
    {
        $this->get('/admin')
            ->assertHeader(
                'X-Robots-Tag',
                'noindex, follow'
            );
    }

    public function test_writer_redirect_has_noindex_header(): void
    {
        $this->get('/writer/dashboard')
            ->assertHeader(
                'X-Robots-Tag',
                'noindex, follow'
            );
    }

    public function test_real_public_home_is_not_noindexed(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        $this->assertNull(
            $response->headers->get('X-Robots-Tag')
        );

        $response->assertDontSee(
            '<meta name="robots" content="noindex,follow">',
            false
        );
    }

    public static function privatePathProvider(): array
    {
        return [
            'login' => ['/login'],
            'register' => ['/register'],
            'forgot password' => ['/forgot-password'],
            'reset password' => ['/reset-password/token-value'],
            'verify email' => ['/verify-email'],
            'confirm password' => ['/confirm-password'],
            'admin root' => ['/admin'],
            'admin child' => ['/admin/works'],
            'writer root' => ['/writer'],
            'writer child' => ['/writer/dashboard'],
        ];
    }

    public static function publicPathProvider(): array
    {
        return [
            'home' => ['/'],
            'works' => ['/works'],
            'work detail' => ['/works/43'],
            'character detail' => ['/characters/1'],
            'public writing tool' => ['/writing-tool'],
        ];
    }
}
