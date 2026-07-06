<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HelpfulVote extends Model
{
    protected $fillable = [
        'target_type',
        'target_id',
        'session_id',
        'ip_address',
    ];
}
