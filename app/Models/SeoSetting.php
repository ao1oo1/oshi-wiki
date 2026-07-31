<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    protected $fillable = [
        'site_title',
        'site_description',
        'site_keywords',
        'google_site_verification',
        'default_og_image_url',
    ];
}
