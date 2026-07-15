<?php
namespace Tests\Feature\Character;

use App\Models\Character;
use App\Models\Work;
use App\Services\CharacterWorkLinkService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Tests\TestCase;

class CharacterWorkManyToManyPhase1Test extends TestCase
{
    use RefreshDatabase;

    public function test_table_exists(): void
    {
        $this->assertTrue(Schema::hasColumns('character_work',[
            'character_id','work_id','is_primary','appearance_type','sort_order','notes',
        ]));
    }

    public function test_legacy_work_id_syncs_as_primary(): void
    {
        $work=Work::factory()->create();
        $character=Character::factory()->create(['work_id'=>$work->id]);
        $this->assertDatabaseHas('character_work',[
            'character_id'=>$character->id,'work_id'=>$work->id,'is_primary'=>true,
        ]);
        $this->assertTrue($character->isLinkedToWork($work->id));
    }

    public function test_character_can_link_to_multiple_works(): void
    {
        $main=Work::factory()->create();
        $chapter1=Work::factory()->create();
        $chapter2=Work::factory()->create();
        $character=Character::factory()->create(['work_id'=>$main->id]);

        app(CharacterWorkLinkService::class)->sync(
            $character,[$main->id,$chapter1->id,$chapter2->id],$main->id
        );

        $character->refresh();
        $this->assertCount(3,$character->linkedWorks);
        $this->assertSame($main->id,$character->primaryLinkedWork()?->id);
        $this->assertSame(1,Character::query()->count());
    }

    public function test_primary_can_change_without_duplicate_character(): void
    {
        $main=Work::factory()->create();
        $chapter=Work::factory()->create();
        $character=Character::factory()->create(['work_id'=>$main->id]);
        $service=app(CharacterWorkLinkService::class);
        $service->add($character,$chapter->id);
        $service->setPrimary($character,$chapter->id);
        $character->refresh();

        $this->assertSame($chapter->id,$character->work_id);
        $this->assertSame(1,Character::query()->count());
        $this->assertDatabaseHas('character_work',[
            'character_id'=>$character->id,'work_id'=>$main->id,'is_primary'=>false,
        ]);
        $this->assertDatabaseHas('character_work',[
            'character_id'=>$character->id,'work_id'=>$chapter->id,'is_primary'=>true,
        ]);
    }

    public function test_unlinked_work_cannot_be_primary(): void
    {
        $main=Work::factory()->create();
        $other=Work::factory()->create();
        $character=Character::factory()->create(['work_id'=>$main->id]);
        $this->expectException(InvalidArgumentException::class);
        app(CharacterWorkLinkService::class)->setPrimary($character,$other->id);
    }
}
