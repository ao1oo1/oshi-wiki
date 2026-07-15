<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Work;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkCsvExactFilterTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        return User::factory()->create([
            'is_super_admin' => true,
            'status' => 'active',
        ]);
    }

    public function test_csv_export_applies_exact_keyword_filter(): void
    {
        Work::factory()->create([
            'title' => '完全一致対象作品',
            'title_kana' => 'かんぜんいっちたいしょうさくひん',
            'genre' => 'ファンタジー',
            'original_media' => '漫画',
            'status' => 'published',
        ]);

        Work::factory()->create([
            'title' => '完全一致対象作品 外伝',
            'title_kana' => 'かんぜんいっちたいしょうさくひん がいでん',
            'genre' => 'ファンタジー',
            'original_media' => '漫画',
            'status' => 'published',
        ]);

        $response = $this->actingAs($this->superAdmin())
            ->get(route('admin.works.csv-export', [
                'exact_keyword' => '完全一致対象作品',
            ]));

        $response->assertOk();

        $csv = $response->getContent();

        $this->assertStringContainsString('完全一致対象作品', $csv);
        $this->assertStringNotContainsString('完全一致対象作品 外伝', $csv);
    }

    public function test_csv_export_combines_exact_keyword_and_status_with_and_condition(): void
    {
        Work::factory()->create([
            'title' => 'AND検索対象',
            'title_kana' => 'あんどけんさくたいしょう',
            'genre' => 'アクション',
            'original_media' => 'ゲーム',
            'status' => 'published',
        ]);

        Work::factory()->create([
            'title' => 'AND検索対象',
            'title_kana' => 'あんどけんさくたいしょう',
            'genre' => 'アクション',
            'original_media' => 'ゲーム',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->superAdmin())
            ->get(route('admin.works.csv-export', [
                'exact_keyword' => 'AND検索対象',
                'status' => 'published',
            ]));

        $response->assertOk();

        $csv = $response->getContent();
        $rows = array_values(array_filter(
            preg_split('/\r\n|\r|\n/', trim($csv))
        ));

        $this->assertCount(2, $rows);
        $this->assertStringContainsString('published', $rows[1]);
        $this->assertStringNotContainsString('draft', $rows[1]);
    }

    public function test_csv_export_can_match_exact_keyword_against_non_title_fields(): void
    {
        Work::factory()->create([
            'title' => 'ジャンル一致作品',
            'title_kana' => 'じゃんるいっちさくひん',
            'genre' => '完全一致ジャンル',
            'original_media' => '小説',
        ]);

        Work::factory()->create([
            'title' => '部分一致だけの作品',
            'title_kana' => 'ぶぶんいっちだけのさくひん',
            'genre' => '完全一致ジャンル派生',
            'original_media' => '小説',
        ]);

        $response = $this->actingAs($this->superAdmin())
            ->get(route('admin.works.csv-export', [
                'exact_keyword' => '完全一致ジャンル',
            ]));

        $response->assertOk();

        $csv = $response->getContent();

        $this->assertStringContainsString('ジャンル一致作品', $csv);
        $this->assertStringNotContainsString('部分一致だけの作品', $csv);
    }
}
