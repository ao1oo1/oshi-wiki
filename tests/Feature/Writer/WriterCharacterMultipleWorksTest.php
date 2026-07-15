<?php

namespace Tests\Feature\Writer;

use App\Models\Character;
use App\Models\Role;
use App\Models\User;
use App\Models\Work;
use App\Services\CharacterWorkLinkService;
use App\Services\PromptCharacterContextBuilder;
use App\Services\SavedPromptService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WriterCharacterMultipleWorksTest extends TestCase
{
    use RefreshDatabase;

    public function test_prompt_form_shows_character_linked_as_additional_work(): void
    {
        $user = $this->writerUser();

        $primary = Work::factory()->create([
            'status' => 'published',
        ]);

        $chapter = Work::factory()->create([
            'title' => '公開第1章',
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $primary->id,
            'name' => '章選択キャラクター',
            'status' => 'published',
        ]);

        app(CharacterWorkLinkService::class)->add(
            $character,
            $chapter->id
        );

        $this->actingAs($user)
            ->get(route('writer.prompts.create'))
            ->assertOk()
            ->assertSee('公開第1章')
            ->assertSee('章選択キャラクター');
    }

    public function test_saved_prompt_keeps_v1_character_from_additional_work(): void
    {
        $user = $this->writerUser();

        $primary = Work::factory()->create([
            'status' => 'published',
        ]);

        $chapter = Work::factory()->create([
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $primary->id,
            'name' => '追加作品プロンプトキャラ',
            'status' => 'published',
        ]);

        app(CharacterWorkLinkService::class)->add(
            $character,
            $chapter->id
        );

        $prompt = app(SavedPromptService::class)->createForUser(
            $user,
            [
                'title' => '多対多テスト',
                'category' => 'scene',
                'purpose' => 'テスト',
                'work_ref' => 'work:' . $chapter->id,
                'selected_character_refs' => [
                    'v1:' . $character->id,
                ],
                'writing_style' => 'other',
                'writing_style_other' => '指定なし',
                'genre' => 'other',
                'genre_other' => '指定なし',
                'status' => 'active',
            ]
        );

        $this->assertSame(
            ['v1:' . $character->id],
            $prompt->selected_character_refs
        );

        $this->assertStringContainsString(
            '追加作品プロンプトキャラ',
            $prompt->prompt_body
        );
    }

    public function test_prompt_context_lists_all_published_linked_works(): void
    {
        $user = User::factory()->create();

        $primary = Work::factory()->create([
            'title' => '基本作品',
            'status' => 'published',
        ]);

        $chapter = Work::factory()->create([
            'title' => '第1章',
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $primary->id,
            'name' => '複数作品表示キャラ',
            'status' => 'published',
        ]);

        app(CharacterWorkLinkService::class)->add(
            $character,
            $chapter->id
        );

        $context = app(PromptCharacterContextBuilder::class)->build(
            $user,
            ['v1:' . $character->id]
        );

        $this->assertStringContainsString(
            '基本作品',
            $context['characters']
        );

        $this->assertStringContainsString(
            '第1章',
            $context['characters']
        );
    }

    public function test_writer_relationship_form_shows_additional_work_character(): void
    {
        $user = $this->writerUser();

        $primary = Work::factory()->create([
            'status' => 'published',
        ]);

        $chapter = Work::factory()->create([
            'title' => '関係性対象章',
            'status' => 'published',
        ]);

        $character = Character::factory()->create([
            'work_id' => $primary->id,
            'name' => '関係性追加作品キャラ',
            'status' => 'published',
        ]);

        app(CharacterWorkLinkService::class)->add(
            $character,
            $chapter->id
        );

        $this->actingAs($user)
            ->get(route(
                'writer.original-character-relationships.create'
            ))
            ->assertOk()
            ->assertSee('関係性対象章')
            ->assertSee('関係性追加作品キャラ');
    }
    private function writerUser(): User
    {
        $writerRole = Role::query()->firstOrCreate(
            ['name' => 'writer'],
            ['label' => '一般執筆ユーザー']
        );

        return User::factory()->create([
            'status' => 'active',
            'role_id' => $writerRole->id,
        ]);
    }


}
