<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CharacterWork extends Pivot
{
    protected $table = 'character_work';
    public $incrementing = true;
    protected $fillable = [
        'character_id','work_id','is_primary','appearance_type','sort_order','notes',
    ];
    protected function casts(): array {
        return ['is_primary'=>'boolean','sort_order'=>'integer'];
    }
    public function character(): BelongsTo { return $this->belongsTo(Character::class); }
    public function work(): BelongsTo { return $this->belongsTo(Work::class); }
}
