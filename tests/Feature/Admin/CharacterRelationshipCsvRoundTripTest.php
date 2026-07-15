<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\CharacterRelationship;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CharacterRelationshipCsvRoundTripTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }

    public function test_export_contains_relationship_fields_and_names(): void
    {
        $work = Work::factory()->create(['title' => 'テスト作品']);
        $from = Character::factory()->create(['work_id' => $work->id, 'name' => '人物A']);
        $to = Character::factory()->create(['work_id' => $work->id, 'name' => '人物B']);

        CharacterRelationship::query()->create([
            'work_id' => $work->id,
            'from_character_id' => $from->id,
            'to_character_id' => $to->id,
            'called_name' => 'Bさん',
            'relationship' => '友人',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->superAdmin())
            ->get(route('admin.character-relationships.csv-export'));

        $response->assertOk();
        $csv = $response->getContent();

        $this->assertStringContainsString('relationship_id', $csv);
        $this->assertStringContainsString('テスト作品', $csv);
        $this->assertStringContainsString('人物A', $csv);
        $this->assertStringContainsString('人物B', $csv);
        $this->assertStringContainsString('Bさん', $csv);
    }

    public function test_import_creates_and_updates_relationship(): void
    {
        $work = Work::factory()->create();
        $from = Character::factory()->create(['work_id' => $work->id]);
        $to = Character::factory()->create(['work_id' => $work->id]);

        $csv = implode("\n", [
            'relationship_id,work_id,from_character_id,to_character_id,called_name,relationship,impression,notes,status',
            ",{$work->id},{$from->id},{$to->id},呼び方,仲間,信頼,補足,draft",
        ]);

        $response = $this->actingAs($this->superAdmin())
            ->post(route('admin.character-relationships.csv-import.store'), [
                'csv_file' => UploadedFile::fake()->createWithContent('relationships.csv', $csv),
                'default_status' => 'draft',
            ]);

        $response->assertRedirect(route('admin.character-relationships.csv-import.create'));

        $relationship = CharacterRelationship::query()->firstOrFail();

        $this->assertSame('呼び方', $relationship->called_name);
        $this->assertSame('仲間', $relationship->relationship);

        $updateCsv = implode("\n", [
            'relationship_id,work_id,from_character_id,to_character_id,called_name,relationship,impression,notes,status',
            "{$relationship->id},{$work->id},{$from->id},{$to->id},更新呼称,親友,強い信頼,更新補足,published",
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.character-relationships.csv-import.store'), [
                'csv_file' => UploadedFile::fake()->createWithContent('relationships-update.csv', $updateCsv),
                'default_status' => 'draft',
            ])
            ->assertRedirect(route('admin.character-relationships.csv-import.create'));

        $relationship->refresh();

        $this->assertSame('更新呼称', $relationship->called_name);
        $this->assertSame('親友', $relationship->relationship);
        $this->assertSame('published', $relationship->status);
    }

    public function test_import_rejects_characters_from_different_work(): void
    {
        $workA = Work::factory()->create();
        $workB = Work::factory()->create();
        $from = Character::factory()->create(['work_id' => $workA->id]);
        $to = Character::factory()->create(['work_id' => $workB->id]);

        $csv = implode("\n", [
            'relationship_id,work_id,from_character_id,to_character_id,called_name,relationship,impression,notes,status',
            ",{$workA->id},{$from->id},{$to->id},呼称,友人,,,draft",
        ]);

        $this->actingAs($this->superAdmin())
            ->post(route('admin.character-relationships.csv-import.store'), [
                'csv_file' => UploadedFile::fake()->createWithContent('invalid.csv', $csv),
                'default_status' => 'draft',
            ])
            ->assertRedirect(route('admin.character-relationships.csv-import.create'))
            ->assertSessionHas('csv_errors');

        $this->assertDatabaseCount('character_relationships', 0);
    }

    public function test_index_shows_csv_buttons_only_for_super_admin(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('admin.character-relationships.index'))
            ->assertOk()
            ->assertSee('CSV取り込み')
            ->assertSee('CSVエクスポート');
    }
}
