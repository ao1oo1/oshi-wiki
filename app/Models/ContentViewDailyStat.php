<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentViewDailyStat extends Model
{
    protected $fillable = ['viewable_type','viewable_id','viewed_on','view_count'];

    protected function casts(): array
    {
        return ['viewed_on'=>'date','view_count'=>'integer'];
    }

    public function viewable(): MorphTo
    {
        return $this->morphTo();
    }
}
