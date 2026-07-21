<?php

namespace Tests\Feature\Writer;

use App\Models\BillingPlan;
use App\Models\OriginalCharacter;
use App\Models\Role;
use App\Models\User;
use App\Models\UserBillingProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WriterCsvFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_guide_is_available_for_writer(): void
    {
        $user = $this->writer();

        $this->actingAs($user)
            ->get(route('writer.csv.guide'))
            ->assertOk()
            ->assertSee('CSVインポート・エクスポートの使い方')
            ->assertSee('そもそもCSVとは？')
            ->assertSee('エクスポートの使い方')
            ->assertSee('インポートの使い方')
            ->assertSee('CSV UTF-8')
            ->assertSee('2,000行')
            ->assertSee('CSV管理画面へ戻る');
    }

    public function test_csv_index_links_to_guide(): void
    {
        $user = $this->writer();

        $this->actingAs($user)
            ->get(route('writer.csv.index'))
            ->assertOk()
            ->assertSee('はじめての方向け：CSV機能の使い方')
            ->assertSee(route('writer.csv.guide'));
    }

    public function test_free_user_can_export_but_cannot_import(): void
    {
        $user = $this->writer();

        $this->actingAs($user)
            ->get(route('writer.csv.export', 'characters'))
            ->assertOk();

        $file = UploadedFile::fake()->createWithContent(
            'characters.csv',
            "name\nテスト\n"
        );

        $this->actingAs($user)
            ->post(
                route('writer.csv.import', 'characters'),
                ['csv_file' => $file]
            )
            ->assertForbidden();
    }

    public function test_plus_user_can_import_character_csv(): void
    {
        $user = $this->plusWriter();

        $file = UploadedFile::fake()->createWithContent(
            'characters.csv',
            "name,name_kana,is_main_character\n夢乃,ゆめの,1\n"
        );

        $this->actingAs($user)
            ->post(
                route('writer.csv.import', 'characters'),
                ['csv_file' => $file]
            )
            ->assertRedirect(route('writer.csv.index'));

        $this->assertDatabaseHas('original_characters', [
            'user_id' => $user->id,
            'name' => '夢乃',
            'name_kana' => 'ゆめの',
        ]);
    }

    public function test_export_is_limited_to_current_user(): void
    {
        $user = $this->writer();
        $other = $this->writer();

        OriginalCharacter::query()->create([
            'user_id' => $user->id,
            'name' => '本人データ',
            'status' => 'active',
        ]);

        OriginalCharacter::query()->create([
            'user_id' => $other->id,
            'name' => '他人データ',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)
            ->get(route('writer.csv.export', 'characters'));

        $content = $response->streamedContent();

        $this->assertStringContainsString('本人データ', $content);
        $this->assertStringNotContainsString('他人データ', $content);
    }

    private function writer(): User
    {
        $role = Role::query()->firstOrCreate(
            ['name' => User::ROLE_WRITER],
            [
                'label' => 'Writer',
                'description' => 'Writer会員',
            ]
        );

        return User::factory()->create([
            'role_id' => $role->id,
            'status' => 'active',
        ]);
    }

    private function plusWriter(): User
    {
        $user = $this->writer();

        $plan = BillingPlan::query()->updateOrCreate(
            ['slug' => 'plus'],
            [
                'name' => 'Oshi-Wiki Plus',
                'monthly_price' => 480,
                'yearly_price' => 4800,
                'limits' => config('billing.plans.plus.limits'),
                'is_active' => true,
            ]
        );

        UserBillingProfile::query()->create([
            'user_id' => $user->id,
            'billing_plan_id' => $plan->id,
            'status' => 'active',
            'current_period_end' => now()->addMonth(),
        ]);

        return $user->fresh();
    }
}
