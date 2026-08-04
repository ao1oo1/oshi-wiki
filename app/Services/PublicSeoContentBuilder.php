<?php

namespace App\Services;

use App\Models\Character;
use App\Models\Work;
use Illuminate\Support\Str;

class PublicSeoContentBuilder
{
    public function workTitle(Work $work): string
    {
        if (filled($work->seo_title ?? null)) {
            return $this->limit(
                (string) $work->seo_title,
                70
            );
        }

        $media = (string) ($work->original_media ?? '');

        $suffix = match (true) {
            str_contains($media, 'ゲーム')
                || str_contains(strtolower($media), 'game')
                => 'キャラクター一覧・人物関係・ストーリー・世界観',

            str_contains($media, '漫画')
                || str_contains($media, 'アニメ')
                || str_contains(strtolower($media), 'manga')
                || str_contains(strtolower($media), 'anime')
                => '登場人物・人物相関図・関係性・ストーリーまとめ',

            default
                => 'キャラクター・人物相関図・世界観・用語・ストーリー',
        };

        return $this->limit(
            "{$work->title}｜{$suffix}",
            70
        );
    }

    public function workDescription(Work $work): string
    {
        if (filled($work->seo_description ?? null)) {
            return $this->limit(
                (string) $work->seo_description,
                160
            );
        }

        return $this->limit(
            "『{$work->title}』のキャラクター、人物関係、"
            . '所属、世界観、用語、ストーリーを'
            . '創作資料・作品考察向けに整理しています。',
            160
        );
    }

    public function characterTitle(
        Character $character
    ): string {
        if (filled($character->seo_title ?? null)) {
            return $this->limit(
                (string) $character->seo_title,
                70
            );
        }

        $workTitle = $this->characterWorkTitle($character);

        $full = sprintf(
            '%s｜プロフィール・関係性・口調｜%s',
            $character->name,
            $workTitle
        );

        if (mb_strlen($full) <= 70) {
            return $full;
        }

        $compact = sprintf(
            '%s｜人物像・関係性｜%s',
            $character->name,
            $workTitle
        );

        if (mb_strlen($compact) <= 70) {
            return $compact;
        }

        $separatorLength = mb_strlen('｜人物｜');
        $availableNameLength = max(
            1,
            70 - mb_strlen($workTitle) - $separatorLength
        );

        $characterName = mb_substr(
            $character->name,
            0,
            $availableNameLength
        );

        return sprintf(
            '%s｜人物｜%s',
            $characterName,
            $workTitle
        );
    }

    public function characterDescription(
        Character $character
    ): string {
        if (filled($character->seo_description ?? null)) {
            return $this->limit(
                (string) $character->seo_description,
                160
            );
        }

        $workTitle = $this->characterWorkTitle($character);

        return $this->limit(
            "『{$workTitle}』の{$character->name}について、"
            . 'プロフィール、性格、経歴、所属、人間関係、'
            . '口調・話し方を創作資料として整理しています。',
            160
        );
    }

    private function characterWorkTitle(
        Character $character
    ): string {
        $character->loadMissing(['linkedWorks', 'work']);

        $published = $character->linkedWorks
            ->first(fn ($work) => $work->status === 'published');

        return (string) (
            $published?->title
            ?? (
                $character->work?->status === 'published'
                    ? $character->work?->title
                    : null
            )
            ?? '関連作品'
        );
    }

    private function limit(
        string $value,
        int $length
    ): string {
        return Str::limit(
            trim(preg_replace('/\s+/u', ' ', $value) ?? $value),
            $length,
            ''
        );
    }
}
