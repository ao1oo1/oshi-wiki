<?php

namespace Tests\Feature\Public;

use Tests\TestCase;

class WriterLoginLinkAndStorySectionMarkupTest extends TestCase
{
    public function test_writer_login_has_database_link(): void
    {
        $view = file_get_contents(
            resource_path('views/auth/writer-login.blade.php')
        );

        $this->assertStringContainsString(
            'データベースを見る',
            $view
        );

        $this->assertStringContainsString(
            "route('public.home')",
            $view
        );
    }

    public function test_public_story_events_are_flat_cards_without_notes(): void
    {
        $view = file_get_contents(
            resource_path('views/public/works/show.blade.php')
        );

        $start = strpos(
            $view,
            'id="work-story-sections"'
        );
        $end = strpos(
            $view,
            '</section>',
            $start
        );

        $this->assertNotFalse($start);
        $this->assertNotFalse($end);

        $section = substr(
            $view,
            $start,
            $end - $start
        );

        $this->assertStringNotContainsString(
            '<strong>備考</strong>',
            $section
        );

        $this->assertStringNotContainsString(
            '$section->notes',
            $section
        );

        $this->assertStringNotContainsString(
            '<details class="rounded-xl border border-[#E2E8F0] p-4"',
            $section
        );

        $this->assertStringContainsString(
            '{{ $event->summary }}',
            $section
        );

        $this->assertStringContainsString(
            '{{ $event->outcome }}',
            $section
        );

        $this->assertSame(
            substr_count($section, '<details'),
            substr_count($section, '</details>')
        );
    }
}
