<?php

namespace Database\Seeders;

use App\Models\Work;
use Illuminate\Database\Seeder;

class WorkSeeder extends Seeder
{
    public function run(): void
    {
        Work::updateOrCreate(
            ['slug' => 'sample-work'],
            [
                'title' => 'サンプル作品',
                'title_kana' => 'さんぷるさくひん',
                'genre' => 'fantasy',
                'original_media' => 'original',
                'official_url' => null,
                'guideline_url' => null,
                'description' => 'Oshi-Wiki開発用のサンプル作品です。',
                'status' => 'published',
                'review_status' => 'approved',
                'published_at' => now(),
            ]
        );
    }
}
