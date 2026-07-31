<?php

namespace App\Support;

use App\Models\SeoSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SeoSettings
{
    public const CACHE_KEY = 'site.seo.settings';

    public static function get(): SeoSetting
    {
        try {
            if (! Schema::hasTable('seo_settings')) {
                return self::defaults();
            }

            return Cache::remember(
                self::CACHE_KEY,
                now()->addHour(),
                fn (): SeoSetting => SeoSetting::query()->first()
                    ?? self::defaults()
            );
        } catch (Throwable) {
            return self::defaults();
        }
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private static function defaults(): SeoSetting
    {
        return new SeoSetting([
            'site_title' => 'Oshi-Wiki',
            'site_description' =>
                '漫画・アニメ・ゲーム作品の設定、キャラクター、'
                . '関係性、物語を整理する創作支援データベースです。',
            'site_keywords' =>
                'アニメ,漫画,ゲーム,キャラクター,人物相関図,'
                . 'ストーリー,世界観,創作支援',
            'append_site_name_to_titles' => true,
        ]);
    }
}
