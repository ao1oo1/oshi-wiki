<?php

namespace App\Support;

use App\Models\Character;
use App\Models\Work;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PublicEntitySeo
{
    private const CHARACTER_FIELDS = [
        '本名' => 'real_name',
        '別名・愛称' => 'aliases',
        '英語表記' => 'name_english',
        '性別' => 'gender',
        '年齢' => 'age',
        '誕生日' => 'birthday',
        '身長' => 'height',
        '体重' => 'weight',
        '血液型' => 'blood_type',
        '出身地' => 'birthplace',
        '種族' => 'species',
        '所属・寮' => 'affiliation',
        '学校・学年・クラス' => 'school_grade_class',
        '職業・役職' => 'occupation_position',
        '家族構成' => 'family_structure',
        '一人称' => 'first_person',
        '二人称' => 'second_person',
        '基本口調' => 'basic_tone',
        '口癖' => 'catchphrases',
        '特徴的な言い回し' => 'distinctive_speech',
        '性格' => 'personality',
        '外見' => 'appearance',
        '能力・技' => 'abilities',
        '背景・経歴' => 'background',
        '作品内での活躍' => 'story_activities',
    ];

    private const WORK_FIELDS = [
        'ジャンル' => 'genre',
        '媒体' => 'original_media',
        '作品概要' => 'description',
        '時系列・舞台設定' => 'timeline_setting',
        '建物・間取り' => 'building_layout',
        '部屋・座席' => 'character_room_seat',
        'よく集まる場所' => 'hangout_places',
        '立入禁止・秘密の場所' => 'restricted_secret_places',
        '食堂・売店・メニュー' => 'cafeteria_store_menu',
        '日課・時間割' => 'daily_schedule',
        '学校・寮・規則' => 'school_dorm_rules',
        '制服・服装' => 'uniform_details',
        '休日・私服' => 'casual_holiday_rules',
        '当番・役割' => 'duty_system',
        '学年・クラス制度' => 'class_grade_system',
        '組織・所属' => 'organizations_memberships',
        '順位・階級' => 'ranking_system',
        '大人・教職員の役割' => 'adult_roles',
        '年間行事' => 'annual_events',
        'イベント進行' => 'event_flow',
        '季節・時間軸' => 'story_season',
        '学校・舞台の所在地' => 'school_location',
        '通学・交通環境' => 'commute_environment',
        '周辺施設・店' => 'nearby_shops',
        '気候・自然' => 'climate_nature',
        '音・環境音' => 'sounds',
        '象徴・モチーフ' => 'symbolic_motifs',
        '必需品・持ち物' => 'required_belongings',
    ];

    public static function forCharacter(Character $character): array
    {
        $workTitles = $character->linkedWorks
            ->where('status', 'published')
            ->pluck('title')
            ->filter()
            ->unique()
            ->values();

        if ($workTitles->isEmpty() && filled($character->work?->title)) {
            $workTitles = collect([$character->work->title]);
        }

        $primaryWork = (string) ($workTitles->first() ?: '作品情報');
        $facts = self::filledFacts(
            $character,
            self::CHARACTER_FIELDS
        );

        $titleParts = collect([
            $character->name,
            self::firstAvailableLabel($facts, [
                '誕生日',
                '所属・寮',
                '学校・学年・クラス',
                '一人称',
            ]),
            'プロフィール',
            $primaryWork,
        ])->filter();

        $title = self::truncate(
            $titleParts->implode('・'),
            66
        );

        $summaryFacts = $facts
            ->filter(
                fn ($value, $label) => in_array(
                    $label,
                    [
                        '誕生日',
                        '年齢',
                        '身長',
                        '所属・寮',
                        '学校・学年・クラス',
                        '職業・役職',
                        '一人称',
                    ],
                    true
                )
            )
            ->take(4);

        $summary = $character->name
            . 'は'
            . self::quotedList($workTitles)
            . 'に登場するキャラクターです。';

        if ($summaryFacts->isNotEmpty()) {
            $summary .= $summaryFacts
                ->map(
                    fn ($value, $label) =>
                        $label . 'は' . self::plainText($value)
                )
                ->implode('、')
                . '。';
        }

        $description = self::truncate(
            $summary
            . '名前、読み方、英語表記、誕生日、所属、寮、学年、'
            . '一人称、口調、能力、関係性などの登録情報を掲載しています。',
            160
        );

        $keywords = self::keywords(
            collect([
                $character->name,
                $character->name_kana,
                $character->real_name,
                $character->aliases,
                $character->name_english,
                $character->search_keywords,
            ])
                ->merge($workTitles)
                ->merge($character->tags->pluck('name'))
                ->merge(
                    $facts->flatMap(
                        fn ($value, $label) => [
                            $label,
                            $value,
                            $character->name . ' ' . $label,
                        ]
                    )
                )
        );

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Person',
            'name' => $character->name,
            'alternateName' => self::alternateNames($character),
            'url' => route('public.characters.show', $character),
            'description' => $description,
            'mainEntityOfPage' => route(
                'public.characters.show',
                $character
            ),
        ];

        if (filled($character->birthday)) {
            $jsonLd['birthDate'] = self::plainText(
                $character->birthday
            );
        }

        if ($workTitles->isNotEmpty()) {
            $jsonLd['isPartOf'] = $workTitles
                ->map(
                    fn ($title) => [
                        '@type' => 'CreativeWork',
                        'name' => $title,
                    ]
                )
                ->values()
                ->all();
        }

        return compact(
            'title',
            'description',
            'keywords',
            'jsonLd',
            'summary'
        );
    }

    public static function forWork(Work $work): array
    {
        $facts = self::filledFacts($work, self::WORK_FIELDS);
        $characterNames = $work->linkedCharacters
            ->where('status', 'published')
            ->pluck('name')
            ->filter()
            ->unique()
            ->take(12)
            ->values();

        $title = self::truncate(
            collect([
                $work->title,
                'キャラクター・世界観・用語・設定',
            ])->filter()->implode('｜'),
            66
        );

        $summaryFacts = $facts
            ->filter(
                fn ($value, $label) => in_array(
                    $label,
                    [
                        'ジャンル',
                        '媒体',
                        '学校・寮・規則',
                        '組織・所属',
                        '学校・舞台の所在地',
                    ],
                    true
                )
            )
            ->take(3);

        $summary = $work->title
            . 'の作品情報ページです。';

        if ($summaryFacts->isNotEmpty()) {
            $summary .= $summaryFacts
                ->map(
                    fn ($value, $label) =>
                        $label . 'は' . self::plainText($value)
                )
                ->implode('、')
                . '。';
        }

        $description = self::truncate(
            $summary
            . 'キャラクター、人物関係、ストーリー、世界観、'
            . '学校・寮・組織、用語、英語表記などの登録情報を掲載しています。',
            160
        );

        $keywords = self::keywords(
            collect([
                $work->title,
                $work->title_kana,
                $work->search_keywords,
                $work->genre,
                $work->original_media,
            ])
                ->merge($work->tags->pluck('name'))
                ->merge($characterNames)
                ->merge(
                    $facts->flatMap(
                        fn ($value, $label) => [
                            $label,
                            $value,
                            $work->title . ' ' . $label,
                        ]
                    )
                )
        );

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'CreativeWork',
            'name' => $work->title,
            'alternateName' => collect([
                $work->title_kana,
                $work->search_keywords,
            ])->filter()->values()->all(),
            'url' => route('public.works.show', $work),
            'description' => $description,
            'mainEntityOfPage' => route('public.works.show', $work),
            'keywords' => $keywords,
        ];

        if (filled($work->genre)) {
            $jsonLd['genre'] = self::plainText($work->genre);
        }

        if ($characterNames->isNotEmpty()) {
            $jsonLd['character'] = $characterNames
                ->map(
                    fn ($name) => [
                        '@type' => 'Person',
                        'name' => $name,
                    ]
                )
                ->all();
        }

        return compact(
            'title',
            'description',
            'keywords',
            'jsonLd',
            'summary'
        );
    }

    private static function filledFacts(
        object $entity,
        array $fields
    ): Collection {
        return collect($fields)
            ->mapWithKeys(
                fn ($attribute, $label) => [
                    $label => self::plainText(
                        data_get($entity, $attribute)
                    ),
                ]
            )
            ->filter();
    }

    private static function firstAvailableLabel(
        Collection $facts,
        array $labels
    ): ?string {
        foreach ($labels as $label) {
            if ($facts->has($label)) {
                return $label;
            }
        }

        return null;
    }

    private static function alternateNames(
        Character $character
    ): array {
        return collect([
            $character->name_kana,
            $character->real_name,
            $character->aliases,
            $character->name_english,
        ])
            ->flatMap(
                fn ($value) => preg_split(
                    '/[\r\n,、\/]+/u',
                    (string) $value
                )
            )
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private static function keywords(Collection $values): string
    {
        return $values
            ->flatMap(
                fn ($value) => preg_split(
                    '/[\r\n,、;；|｜]+/u',
                    self::plainText($value)
                )
            )
            ->map(
                fn ($value) => trim(
                    preg_replace('/\s+/u', ' ', (string) $value)
                )
            )
            ->filter(
                fn ($value) =>
                    $value !== ''
                    && mb_strlen($value) <= 80
            )
            ->unique()
            ->take(80)
            ->implode(',');
    }

    private static function quotedList(Collection $values): string
    {
        if ($values->isEmpty()) {
            return '関連作品';
        }

        return $values
            ->take(3)
            ->map(fn ($value) => '『' . $value . '』')
            ->implode('・');
    }

    private static function plainText(mixed $value): string
    {
        if (is_array($value)) {
            $value = implode('、', $value);
        }

        return trim(
            preg_replace(
                '/\s+/u',
                ' ',
                strip_tags((string) $value)
            )
        );
    }

    private static function truncate(
        string $value,
        int $length
    ): string {
        return Str::limit(
            self::plainText($value),
            $length,
            '…'
        );
    }
}
