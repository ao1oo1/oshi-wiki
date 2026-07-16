<?php

namespace Tests\Feature\Writer;

use App\Models\Role;
use App\Models\SavedPrompt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedPromptAiResultAreaHiddenTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_result_save_area_is_hidden(): void
    {
        $user = $this->writerUser();

        $prompt = SavedPrompt::query()->create([
            'user_id' => $user->id,
            'title' => '非表示確認用プロンプト',
            'work_source' => SavedPrompt::WORK_SOURCE_ORIGINAL,
            'prompt_body' => '確認用本文',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get(
            route('writer.prompts.show', $prompt)
        );

        $response
            ->assertOk()
            ->assertDontSee('プロット・執筆用データを保存する')
            ->assertDontSee('AIが出した結論を保存する')
            ->assertSee('おすすめプロンプト')
            ->assertSee('プロンプト本文');
    }

    private function writerUser(): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => 'writer'],
            ['label' => '一般執筆ユーザー']
        );

        return User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }
}
