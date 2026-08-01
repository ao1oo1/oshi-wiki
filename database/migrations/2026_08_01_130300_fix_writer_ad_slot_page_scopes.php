<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const WRITER_POSITIONS = [
        'writer_sidebar_1',
        'writer_sidebar_2',
        'writer_page_bottom',
    ];

    public function up(): void
    {
        DB::table('impression_ad_slots')
            ->whereIn('position', self::WRITER_POSITIONS)
            ->where('page_scope', '!=', 'writer_all')
            ->update([
                'page_scope' => 'writer_all',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // 過去の対象ページを安全に復元できないため処理しません。
    }
};
