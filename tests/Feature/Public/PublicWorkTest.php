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

    public function test_public_work_detail_section_order(): void
    {
        $contents = file_get_contents(
            resource_path('views/public/works/show.blade.php')
        );

        $characterPosition = strpos(
            $contents,
            "                キャラクター\n"
        );
        $storyPosition = strpos(
            $contents,
            '章・編ごとの物語詳細'
        );
        $relationshipPosition = strpos(
            $contents,
            "                キャラクター関係性\n"
        );

        $this->assertNotFalse($characterPosition);
        $this->assertNotFalse($storyPosition);
        $this->assertNotFalse($relationshipPosition);
        $this->assertLessThan($storyPosition, $characterPosition);
        $this->assertLessThan(
            $relationshipPosition,
            $storyPosition
        );
    }

    public function test_public_work_detail_hides_purchase_section(): void
    {
        $contents = file_get_contents(
            resource_path('views/public/works/show.blade.php')
        );

        $this->assertStringNotContainsString(
            "public.works._monetization",
            $contents
        );
        $this->assertStringNotContainsString(
            '配信・購入情報',
            $contents
        );
    }

    public function test_public_work_characters_are_loaded_by_id(): void
    {
        $contents = file_get_contents(
            app_path('Http/Controllers/Public/WorkController.php')
        );

        $showPosition = strpos(
            $contents,
            'public function show(Work $work): View'
        );

        $this->assertNotFalse($showPosition);

        $showBlock = substr($contents, $showPosition);

        $this->assertStringContainsString(
            "->reorder()",
            $showBlock
        );
        $this->assertStringContainsString(
            "->orderBy('characters.id')",
            $showBlock
        );
    }

}
