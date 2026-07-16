<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WorkCsvParentChildCompatibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_csv_without_parent_columns_still_updates_work(): void
    {
        $user = $this->superAdmin();

        $work = Work::factory()->create([
            'title' => '更新前',
            'parent_work_id' => null,
            'child_sort_order' => 0,
        ]);

        $csv = implode("\n", [
            'work_id,title,status',
            "{$work->id},更新後,draft",
        ]);

        $file = UploadedFile::fake()->createWithContent(
            'works.csv',
            $csv
        );

        $this->actingAs($user)
            ->post(route('admin.works.csv-import.store'), [
                'csv_file' => $file,
                'default_status' => 'draft',
            ])
            ->assertRedirect(
                route('admin.works.csv-import.create')
            );

        $this->assertDatabaseHas('works', [
            'id' => $work->id,
            'title' => '更新後',
            'parent_work_id' => null,
            'child_sort_order' => 0,
        ]);
    }

    public function test_csv_can_keep_integer_parent_child_fields(): void
    {
        $user = $this->superAdmin();

        $parent = Work::factory()->create([
            'title' => '親作品',
        ]);

        $child = Work::factory()->create([
            'title' => '子作品更新前',
            'parent_work_id' => $parent->id,
            'child_sort_order' => 3,
        ]);

        $csv = implode("\n", [
            'work_id,title,status,parent_work_id,child_sort_order',
            "{$child->id},子作品更新後,draft,{$parent->id},5",
        ]);

        $file = UploadedFile::fake()->createWithContent(
            'works.csv',
            $csv
        );

        $this->actingAs($user)
            ->post(route('admin.works.csv-import.store'), [
                'csv_file' => $file,
                'default_status' => 'draft',
            ])
            ->assertRedirect(
                route('admin.works.csv-import.create')
            );

        $this->assertDatabaseHas('works', [
            'id' => $child->id,
            'title' => '子作品更新後',
            'parent_work_id' => $parent->id,
            'child_sort_order' => 5,
        ]);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create([
            'status' => 'active',
        ]);

        $user->forceFill([
            'is_super_admin' => true,
        ])->save();

        return $user->refresh();
    }
}
