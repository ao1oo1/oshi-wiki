<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminWorkTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_works_index(): void
    {
        $response = $this->get('/admin/works');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_works_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/works');

        $response->assertStatus(200);
        $response->assertSee('作品');
    }

    public function test_authenticated_user_can_create_work(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/works', [
            'title' => 'テスト作品',
            'title_kana' => 'てすとさくひん',
            'genre' => 'テスト',
            'original_media' => '漫画',
            'description' => 'テスト用の作品です。',
            'status' => 'published',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('works', [
            'title' => 'テスト作品',
            'status' => 'published',
        ]);
    }

    public function test_authenticated_user_can_view_work_detail(): void
    {
        $user = User::factory()->create();

        $work = Work::factory()->create([
            'title' => '詳細確認作品',
            'status' => 'published',
        ]);

        $response = $this->actingAs($user)->get('/admin/works/' . $work->id);

        $response->assertStatus(200);
        $response->assertSee('詳細確認作品');
    }
}
