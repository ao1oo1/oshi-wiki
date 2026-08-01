<?php

namespace Tests\Feature\Public;

use App\Models\Character;
use App\Models\CharacterRelationship;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRelationshipTableMobileTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_character_page_marks_relationship_tables_for_mobile_cards(): void
    {
        $work = Work::factory()->create([
            'title' => '公開作品',
            'status' => 'published',
        ]);

        $from = Character::factory()->create([
            'work_id' => $work->id,
            'name' => '夏目貴志',
            'status' => 'published',
        ]);

        $to = Character::factory()->create([
            'work_id' => $work->id,
            'name' => 'ニャンコ先生',
            'status' => 'published',
        ]);

        CharacterRelationship::forceCreate([
            'work_id' => $work->id,
            'from_character_id' => $from->id,
            'to_character_id' => $to->id,
            'called_name' => '先生',
            'relationship' => '用心棒',
            'impression' => '大切な相棒',
            'status' => 'published',
        ]);

        $this->get(route('public.characters.show', $from))
            ->assertOk()
            ->assertSee('public-character-relationship-table-wrap', false)
            ->assertSee('public-character-relationship-table', false)
            ->assertSee('data-label="相手"', false)
            ->assertSee('data-label="印象・気持ち等"', false);
    }

    public function test_public_work_page_marks_relationship_tables_for_mobile_cards(): void
    {
        $work = Work::factory()->create([
            'title' => '公開作品',
            'status' => 'published',
        ]);

        $from = Character::factory()->create([
            'work_id' => $work->id,
            'name' => 'キャラA',
            'status' => 'published',
        ]);

        $to = Character::factory()->create([
            'work_id' => $work->id,
            'name' => 'キャラB',
            'status' => 'published',
        ]);

        CharacterRelationship::forceCreate([
            'work_id' => $work->id,
            'from_character_id' => $from->id,
            'to_character_id' => $to->id,
            'called_name' => 'B',
            'relationship' => '仲間',
            'impression' => '信頼している',
            'status' => 'published',
        ]);

        $this->get('/works/' . $work->id)
            ->assertOk()
            ->assertSee('public-work-relationship-table-wrap', false)
            ->assertSee('public-work-relationship-table', false)
            ->assertSee('data-label="キャラクター"', false)
            ->assertSee('data-label="印象・気持ち等"', false);
    }
}
