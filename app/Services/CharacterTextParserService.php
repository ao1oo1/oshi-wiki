<?php

namespace App\Services;

use App\Models\Character;

class CharacterTextParserService
{
    private array $labelMap = [
        '名前' => 'name',
        '氏名' => 'name',
        'キャラ名' => 'name',

        '読み仮名' => 'name_kana',
        '読み' => 'name_kana',
        'ふりがな' => 'name_kana',
        'フリガナ' => 'name_kana',

        '本名' => 'real_name',
        '別名・愛称' => 'aliases',
        '別名' => 'aliases',
        '愛称' => 'aliases',
        '英語表記' => 'name_english',
        '性別' => 'gender',
        '年齢' => 'age',
        '生年月日・誕生日' => 'birthday',
        '生年月日' => 'birthday',
        '誕生日' => 'birthday',
        '身長' => 'height',
        '体重' => 'weight',
        '血液型' => 'blood_type',
        '出身地' => 'birthplace',
        '種族' => 'species',
        '所属' => 'affiliation',

        '学校・学年・クラス' => 'school_grade_class',
        '学年・クラス' => 'school_grade_class',
        '学年クラス' => 'school_grade_class',
        'クラス' => 'school_grade_class',

        '職業・役職' => 'occupation_position',
        '職業' => 'occupation_position',
        '役職' => 'occupation_position',
        '家族構成' => 'family_structure',

        '外見の特徴' => 'appearance',
        '外見' => 'appearance',
        '容姿' => 'appearance',

        '性格・特徴' => 'personality',
        '性格' => 'personality',
        '特徴' => 'personality',

        '一人称' => 'first_person',
        '二人称' => 'second_person',

        '基本口調' => 'basic_tone',
        '口調' => 'basic_tone',
        '口癖' => 'catchphrases',
        '特徴的な言い回し' => 'distinctive_speech',
        '相手による口調の違い' => 'tone_by_relationship',

        '短いセリフ例' => 'short_quote_examples',
        '口調の例' => 'short_quote_examples',
        'セリフ例' => 'short_quote_examples',
        '台詞例' => 'short_quote_examples',

        '能力・技・戦闘' => 'abilities',
        '能力' => 'abilities',

        '背景・経歴' => 'background',
        '背景、経歴' => 'background',
        '背景' => 'background',
        '経歴' => 'background',

        '作品内での活躍' => 'story_activities',

        'ページ名または資料名' => 'source_title',
        'ページ名・資料名' => 'source_title',
        '出典' => 'source_title',
        'URL' => 'source_url',
        '情報源区分' => 'source_type',
        '信頼度' => 'source_reliability',
        '確認日' => 'source_checked_at',
        'ネタバレ' => 'spoiler_level',
    ];

    public function parse(string $text): array
    {
        $text = str_replace("\r", '', trim($text));
        $text = preg_replace('/[ \t　]+/u', ' ', $text) ?? $text;

        $result = array_fill_keys([
            'name',
            'name_kana',
            'real_name',
            'aliases',
            'name_english',
            'gender',
            'age',
            'birthday',
            'height',
            'weight',
            'blood_type',
            'birthplace',
            'species',
            'affiliation',
            'school_grade_class',
            'occupation_position',
            'family_structure',
            'appearance',
            'personality',
            'first_person',
            'second_person',
            'basic_tone',
            'catchphrases',
            'distinctive_speech',
            'tone_by_relationship',
            'short_quote_examples',
            'abilities',
            'background',
            'story_activities',
            'source_title',
            'source_url',
            'source_type',
            'source_reliability',
            'source_checked_at',
            'spoiler_level',
        ], null);

        $headingName = $this->extractHeadingName($text);

        $labels = array_keys($this->labelMap);

        usort(
            $labels,
            fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a)
        );

        $labelPattern = implode(
            '|',
            array_map(
                fn (string $label): string => preg_quote($label, '/'),
                $labels
            )
        );

        preg_match_all(
            '/(' . $labelPattern . ')[ \t　]*[:：]/u',
            $text,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        $found = [];

        foreach ($matches[1] as $index => $match) {
            $label = $match[0];
            $fullMatch = $matches[0][$index][0];
            $fullStart = $matches[0][$index][1];

            $found[] = [
                'key' => $this->labelMap[$label],
                'label_start' => $match[1],
                'value_start' => $fullStart + strlen($fullMatch),
            ];
        }

        foreach ($found as $index => $item) {
            $nextStart = $found[$index + 1]['label_start'] ?? strlen($text);

            $value = substr(
                $text,
                $item['value_start'],
                $nextStart - $item['value_start']
            );

            $value = $this->cleanValue($value);

            if ($value !== null) {
                $result[$item['key']] = $this->normalizeChoice(
                    $item['key'],
                    $value
                );
            }
        }

        if (empty($result['name']) && $headingName) {
            $result['name'] = $headingName;
        }

        return $result;
    }

    private function extractHeadingName(string $text): ?string
    {
        $lines = array_values(
            array_filter(
                array_map(
                    'trim',
                    preg_split('/\R/u', $text) ?: []
                )
            )
        );

        foreach ($lines as $line) {
            if (
                preg_match(
                    '/^[■□●○★☆#]+[ \t　]*(.+)$/u',
                    $line,
                    $matches
                )
            ) {
                $heading = trim($matches[1]);

                $heading = preg_split(
                    '/名前[ \t　]*[:：]/u',
                    $heading
                )[0] ?? $heading;

                return trim($heading) ?: null;
            }
        }

        return null;
    }

    private function cleanValue(string $value): ?string
    {
        $value = trim($value);

        $value = preg_replace(
            '/^[■□●○★☆#\s　]+/u',
            '',
            $value
        ) ?? $value;

        $value = preg_replace(
            '/\n{3,}/u',
            "\n\n",
            $value
        ) ?? $value;

        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function normalizeChoice(
        string $key,
        string $value
    ): string {
        $maps = [
            'source_type' => array_flip(
                Character::SOURCE_TYPES
            ),
            'source_reliability' => array_flip(
                Character::SOURCE_RELIABILITIES
            ),
            'spoiler_level' => array_flip(
                Character::SPOILER_LEVELS
            ),
        ];

        if (! isset($maps[$key])) {
            return $value;
        }

        return $maps[$key][$value] ?? $value;
    }
}
