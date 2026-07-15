<?php
namespace App\Services;

use App\Models\Character;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CharacterWorkLinkService
{
    public function sync(Character $character, array $workIds, int $primaryWorkId): void
    {
        $ids = collect($workIds)->map(fn($id)=>(int)$id)->filter()->push($primaryWorkId)->unique()->values();
        DB::transaction(function () use ($character,$ids,$primaryWorkId): void {
            $data = [];
            foreach ($ids as $id) {
                $data[$id] = ['is_primary'=>$id === $primaryWorkId,'sort_order'=>0];
            }
            $character->linkedWorks()->sync($data);
            if ((int)$character->work_id !== $primaryWorkId) {
                $character->forceFill(['work_id'=>$primaryWorkId])->saveQuietly();
            }
            $character->unsetRelation('linkedWorks');
            $character->unsetRelation('work');
        });
    }

    public function add(Character $character, int $workId): void
    {
        if ($workId <= 0) throw new InvalidArgumentException('作品IDが不正です。');
        $character->linkedWorks()->syncWithoutDetaching([
            $workId => ['is_primary'=>(int)$character->work_id === $workId,'sort_order'=>0],
        ]);
        $character->unsetRelation('linkedWorks');
    }

    public function setPrimary(Character $character, int $workId): void
    {
        if (! $character->linkedWorks()->whereKey($workId)->exists()) {
            throw new InvalidArgumentException('主作品にする作品は先に紐付けてください。');
        }
        DB::transaction(function () use ($character,$workId): void {
            DB::table('character_work')->where('character_id',$character->id)
                ->update(['is_primary'=>false,'updated_at'=>now()]);
            DB::table('character_work')->where('character_id',$character->id)->where('work_id',$workId)
                ->update(['is_primary'=>true,'updated_at'=>now()]);
            $character->forceFill(['work_id'=>$workId])->saveQuietly();
            $character->unsetRelation('linkedWorks');
            $character->unsetRelation('work');
        });
    }
}
