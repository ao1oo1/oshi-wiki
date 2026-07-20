<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonetizationService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'category', 'logo_path', 'description',
        'default_button_label', 'priority', 'is_active',
    ];

    protected function casts(): array
    {
        return ['priority' => 'integer', 'is_active' => 'boolean'];
    }

    public function affiliatePrograms(): HasMany
    {
        return $this->hasMany(AffiliateProgram::class, 'service_id');
    }

    public function workLinks(): HasMany
    {
        return $this->hasMany(WorkMonetizationLink::class, 'service_id');
    }
}
