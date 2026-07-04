<?php

namespace Tests\Feature\Public;

use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWorkTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_public_works_index(): void
    {
        $response = $this->get('/works');

        $response->assertStatus(200);
        $response->assertSee('Oshi-Wiki');
    }

    public function test_public_works_index_shows_only_published_works(): void
    {
        Work::factory()->create([
            'title' => '公開作品',
            'status' => 'published',
        ]);

        Work::factory()->create([
            'title' => '下書き作品',
            'status' => 'draft',
        ]);

        $response = $this->get('/works');

        $response->assertStatus(200);
        $response->assertSee('公開作品');
        $response->assertDontSee('下書き作品');
    }

    public function test_public_work_detail_can_show_published_work(): void
    {
        $work = Work::factory()->create([
            'title' => '公開詳細作品',
            'status' => 'published',
        ]);

        $response = $this->get('/works/' . $work->id);

        $response->assertStatus(200);
        $response->assertSee('公開詳細作品');
    }

    public function test_public_work_detail_returns_404_for_draft_work(): void
    {
        $work = Work::factory()->create([
            'title' => '非公開作品',
            'status' => 'draft',
        ]);

        $response = $this->get('/works/' . $work->id);

        $response->assertStatus(404);
    }
}
