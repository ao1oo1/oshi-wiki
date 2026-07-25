<?php

namespace Tests\Feature\Writer;

use App\Models\Character;
use App\Models\CharacterRelationship;
use App\Models\User;
use App\Models\Work;
use App\Services\PromptCharacterContextBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedPromptOfficialCharacterRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_relationship_is_added_for_selected_characters(): void
    {
        $user = User::factory()->create();
        $work = Work::factory()->create([
            'title' => '関係性テスト作品',
            'status' => 'published',
        ]);

        $from = Character::factory()->create([
            'work_id' => $work->id,
            'name' => '主人公',
            'status' => 'published',
        ]);

        $to = Character::factory()->create([
            'work_id' => $work->id,
            'name' => '相棒',
            'status' => 'published',
        ]);

        CharacterRelationship::query()->create([
            'work_id' => $work->id,
            'from_character_id' => $from->id,
            'to_character_id' => $to->id,
            'called_name' => '相棒くん',
            'relationship' => '幼なじみ',
            'impression' => '強く信頼している。',
            'notes' => '二人きりでは呼び捨て。',
            'status' => 'published',
        ]);

        $context = app(
            PromptCharacterContextBuilder::class
        )->build($user, [
            'v1:' . $from->id,
            'v1:' . $to->id,
        ]);

        $text = $context['relationships'];

        foreach ([
            '主人公 → 相棒',
            '作品：関係性テスト作品',
            '呼び方：相棒くん',
            '関係性：幼なじみ',
            '印象・気持ち：強く信頼している。',
            '備考：二人きりでは呼び捨て。',
        ] as $expected) {
            $this->assertStringContainsString($expected, $text);
        }
    }

    public function test_unpublished_or_unselected_relationship_is_not_added(): void
    {
        $user = User::factory()->create();
        $work = Work::factory()->create(['status' => 'published']);

        $first = Character::factory()->create([
            'work_id' => $work->id,
            'name' => '一人目',
            'status' => 'published',
        ]);
        $second = Character::factory()->create([
            'work_id' => $work->id,
            'name' => '二人目',
            'status' => 'published',
        ]);
        $third = Character::factory()->create([
            'work_id' => $work->id,
            'name' => '三人目',
            'status' => 'published',
        ]);

        CharacterRelationship::query()->create([
            'work_id' => $work->id,
            'from_character_id' => $first->id,
            'to_character_id' => $second->id,
            'called_name' => '非公開の呼称',
            'status' => 'private',
        ]);

        CharacterRelationship::query()->create([
            'work_id' => $work->id,
            'from_character_id' => $first->id,
            'to_character_id' => $third->id,
            'called_name' => '未選択相手の呼称',
            'status' => 'published',
        ]);

        $context = app(
            PromptCharacterContextBuilder::class
        )->build($user, [
            'v1:' . $first->id,
            'v1:' . $second->id,
        ]);

        $this->assertStringNotContainsString(
            '非公開の呼称',
            $context['relationships']
        );
        $this->assertStringNotContainsString(
            '未選択相手の呼称',
            $context['relationships']
        );
    }

    public function test_form_explains_automatic_relationship_insertion(): void
    {
        $source = file_get_contents(
            resource_path(
                'views/writer/saved_prompts/_form.blade.php'
            )
        );

        $this->assertStringContainsString(
            '管理画面で公開されている呼び方・関係性・印象・補足メモも自動で反映されます。',
            $source
        );
    }
}
