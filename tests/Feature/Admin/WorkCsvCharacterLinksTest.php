<?php

namespace Tests\Feature\Admin;

use App\Models\Character;
use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkCsvCharacterLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_contains_linked_character_columns(): void
    {
        $user = $this->superAdmin();
        $primaryWork = Work::factory()->create();
        $targetWork = Work::factory()->create();
        $character = Character::factory()->create(['work_id' => $primaryWork->id, 'name' => 'CSV紐付けキャラ']);
        $targetWork->linkedCharacters()->attach($character->id, ['is_primary' => false, 'sort_order' => 0]);

        $csv = $this->actingAs($user)->get(route('admin.works.csv-export', ['work_id' => $targetWork->id]))->getContent();
        $this->assertStringContainsString('character_ids', $csv);
        $this->assertStringContainsString('character_names', $csv);
        $this->assertStringContainsString('CSV紐付けキャラ', $csv);
    }

    public function test_import_can_add_and_remove_additional_character_links(): void
    {
        $user = $this->superAdmin();
        $primaryWork = Work::factory()->create();
        $targetWork = Work::factory()->create(['title' => '対象作品']);
        $keep = Character::factory()->create(['work_id' => $primaryWork->id]);
        $remove = Character::factory()->create(['work_id' => $primaryWork->id]);
        $targetWork->linkedCharacters()->attach($remove->id, ['is_primary' => false, 'sort_order' => 0]);

        $this->importCsv($user, ['work_id','title','status','character_ids'], [$targetWork->id,'対象作品','draft',(string) $keep->id]);

        $this->assertTrue($targetWork->linkedCharacters()->whereKey($keep->id)->exists());
        $this->assertFalse($targetWork->linkedCharacters()->whereKey($remove->id)->exists());
    }

    public function test_import_cannot_remove_primary_character_link(): void
    {
        $user = $this->superAdmin();
        $work = Work::factory()->create(['title' => '主作品']);
        $character = Character::factory()->create(['work_id' => $work->id]);

        $response = $this->importCsv($user, ['work_id','title','status','character_ids'], [$work->id,'主作品','draft','']);
        $response->assertSessionHas('csv_errors');
        $this->assertTrue($work->linkedCharacters()->whereKey($character->id)->exists());
    }

    public function test_import_without_character_columns_preserves_links(): void
    {
        $user = $this->superAdmin();
        $primaryWork = Work::factory()->create();
        $targetWork = Work::factory()->create(['title' => '変更対象']);
        $character = Character::factory()->create(['work_id' => $primaryWork->id]);
        $targetWork->linkedCharacters()->attach($character->id, ['is_primary' => false, 'sort_order' => 0]);

        $this->importCsv($user, ['work_id','title','status'], [$targetWork->id,'変更後','draft']);
        $this->assertTrue($targetWork->linkedCharacters()->whereKey($character->id)->exists());
    }

    public function test_new_work_can_link_existing_characters(): void
    {
        $user = $this->superAdmin();
        $primaryWork = Work::factory()->create();
        $character = Character::factory()->create(['work_id' => $primaryWork->id]);

        $this->importCsv($user, ['work_id','title','status','character_ids'], ['', '新規派生作品','draft',(string) $character->id]);
        $work = Work::query()->where('title', '新規派生作品')->firstOrFail();
        $this->assertTrue($work->linkedCharacters()->whereKey($character->id)->exists());
        $this->assertSame($primaryWork->id, $character->fresh()->work_id);
    }

    private function importCsv(User $user, array $headers, array $row)
    {
        $path = tempnam(sys_get_temp_dir(), 'work-character-links-');
        $handle = fopen($path, 'wb');
        fputcsv($handle, $headers, ',', '"', '');
        fputcsv($handle, $row, ',', '"', '');
        fclose($handle);

        return $this->actingAs($user)->post(route('admin.works.csv-import.store'), [
            'default_status' => 'draft',
            'csv_file' => new \Illuminate\Http\UploadedFile($path, 'works.csv', 'text/csv', null, true),
        ]);
    }

    private function superAdmin(): User
    {
        return User::factory()->create(['is_super_admin' => true, 'status' => 'active']);
    }
}
