<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LinkClick extends Model
{
    protected $fillable = [
        'work_monetization_link_id',
        'work_id',
        'service_id',
        'affiliate_program_id',
        'visitor_hash',
        'user_agent_hash',
        'referer_host',
        'referer_path',
        'clicked_at',
    ];

    protected function casts(): array
    {
        return [
            'clicked_at' => 'datetime',
        ];
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(
            WorkMonetizationLink::class,
            'work_monetization_link_id'
        );
    }

    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(MonetizationService::class);
    }

    public function affiliateProgram(): BelongsTo
    {
        return $this->belongsTo(AffiliateProgram::class);
    }
}
