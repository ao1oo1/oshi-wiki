<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WorkParentChildPhase2ATest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_index_can_filter_child_works(): void
    {
        $user = $this->superAdmin();

        $parent = Work::factory()->create([
            'title' => '親作品一覧',
        ]);

        Work::factory()->create([
            'title' => '子作品一覧',
            'parent_work_id' => $parent->id,
        ]);

        Work::factory()->create([
            'title' => '単独作品一覧',
        ]);

        $this->actingAs($user)
            ->get(route('admin.works.index', [
                'work_type' => 'child',
            ]))
            ->assertOk()
            ->assertSee('子作品一覧')
            ->assertDontSee('単独作品一覧');
    }

    public function test_parent_filter_only_lists_actual_parent_works(): void
    {
        $user = $this->superAdmin();

        $parent = Work::factory()->create([
            'title' => '候補に出る親作品',
        ]);

        Work::factory()->create([
            'title' => '候補用子作品',
            'parent_work_id' => $parent->id,
        ]);

        Work::factory()->create([
            'title' => '候補に出ない単独作品',
        ]);

        $response = $this->actingAs($user)
            ->get(route('admin.works.index'))
            ->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString(
            '候補に出る親作品',
            $html
        );

        $this->assertStringNotContainsString(
            '<option value="'
            . Work::query()
                ->where('title', '候補に出ない単独作品')
                ->value('id')
            . '">候補に出ない単独作品</option>',
            preg_replace('/\s+/', ' ', $html) ?? $html
        );
    }

    public function test_admin_index_can_filter_by_parent_work(): void
    {
        $user = $this->superAdmin();

        $parentA = Work::factory()->create(['title' => '親A']);
        $parentB = Work::factory()->create(['title' => '親B']);

        Work::factory()->create([
            'title' => '親Aの子',
            'parent_work_id' => $parentA->id,
        ]);

        Work::factory()->create([
            'title' => '親Bの子',
            'parent_work_id' => $parentB->id,
        ]);

        $this->actingAs($user)
            ->get(route('admin.works.index', [
                'parent_work_id' => $parentA->id,
            ]))
            ->assertOk()
            ->assertSee('親Aの子')
            ->assertDontSee('親Bの子');
    }

    public function test_csv_export_contains_parent_title(): void
    {
        $user = $this->superAdmin();

        $parent = Work::factory()->create([
            'title' => 'CSV親作品',
        ]);

        $child = Work::factory()->create([
            'title' => 'CSV子作品',
            'parent_work_id' => $parent->id,
        ]);

        $response = $this->actingAs($user)->get(
            route('admin.works.csv-export', [
                'work_id' => $child->id,
            ])
        );

        $response->assertOk();

        $this->assertStringContainsString(
            'parent_work_title',
            $response->getContent()
        );
        $this->assertStringContainsString(
            'CSV親作品',
            $response->getContent()
        );
    }

    public function test_csv_import_can_resolve_parent_by_title(): void
    {
        $user = $this->superAdmin();

        $parent = Work::factory()->create([
            'title' => '名前指定親作品',
        ]);

        $csv = implode("\n", [
            'title,status,parent_work_title,child_sort_order',
            '名前指定子作品,draft,名前指定親作品,4',
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
            'title' => '名前指定子作品',
            'parent_work_id' => $parent->id,
            'child_sort_order' => 4,
        ]);
    }

    public function test_text_import_form_contains_parent_fields(): void
    {
        $user = $this->superAdmin();

        Work::factory()->create([
            'title' => 'テキスト登録親作品',
        ]);

        $this->actingAs($user)
            ->get(route('admin.works.import.create'))
            ->assertOk()
            ->assertSee('親作品')
            ->assertSee('name="parent_work_id"', false)
            ->assertSee('name="child_sort_order"', false)
            ->assertSee('テキスト登録親作品');
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
