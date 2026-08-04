<?php
namespace App\Services;

use App\Models\Character;
use App\Models\ContentViewDailyStat;
use App\Models\Work;
use Illuminate\Database\Eloquent\Collection;

class HotContentService
{
    public const PERIOD_DAYS=7;
    public const ITEM_LIMIT=6;

    public function works(): Collection
    {
        return $this->rank(Work::class,Work::query()->where('status','published'));
    }

    public function characters(): Collection
    {
        return $this->rank(
            Character::class,
            Character::query()->where('status','published')
        );
    }

    private function rank(string $type, $query): Collection
    {
        $rows=ContentViewDailyStat::query()
            ->select('viewable_id')
            ->selectRaw('SUM(view_count) AS total_views')
            ->where('viewable_type',$type)
            ->whereDate(
                'viewed_on','>=',
                today()->subDays(self::PERIOD_DAYS-1)->toDateString()
            )
            ->groupBy('viewable_id')
            ->orderByDesc('total_views')
            ->orderBy('viewable_id')
            ->limit(self::ITEM_LIMIT)
            ->get();

        $ids=$rows->pluck('viewable_id')->map(fn($id)=>(int)$id)->all();
        $counts=$rows->mapWithKeys(
            fn($row)=>[(int)$row->viewable_id=>(int)$row->total_views]
        );
        $models=$query->whereIn('id',$ids)->get()->keyBy('id');

        return new Collection(
            collect($ids)->map(function($id) use($models,$counts){
                $model=$models->get($id);
                if(!$model) return null;
                $model->setAttribute('hot_view_count',$counts->get($id,0));
                return $model;
            })->filter()->values()->all()
        );
    }
}
