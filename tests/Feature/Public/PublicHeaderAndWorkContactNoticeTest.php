<?php

namespace Tests\Feature\Public;

use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicHeaderAndWorkContactNoticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_header_does_not_show_staff_application_link(): void
    {
        $this->get(route('public.works.index'))
            ->assertOk()
            ->assertDontSee('スタッフ申請');

        $header = file_get_contents(
            resource_path('views/public/partials/header.blade.php')
        );

        $mobileMenu = file_get_contents(
            resource_path('views/public/partials/mobile-menu.blade.php')
        );

        $this->assertStringNotContainsString(
            '/contributor/apply',
            $header
        );

        $this->assertStringNotContainsString(
            '/contributor/apply',
            $mobileMenu
        );
    }

    public function test_work_page_shows_contact_notice_between_characters_and_relationships(): void
    {
        $work = Work::factory()->create([
            'status' => 'published',
        ]);

        $response = $this->get(
            route('public.works.show', $work)
        );

        $response
            ->assertOk()
            ->assertSee('情報に誤りがある場合は')
            ->assertSee('お問い合わせフォーム')
            ->assertSee(
                route(
                    'public.contact.create',
                    ['category' => 'correction']
                ),
                false
            );
    }
}
