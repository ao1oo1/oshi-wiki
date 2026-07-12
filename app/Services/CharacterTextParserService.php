<?php

namespace App\Services;

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

        '年齢' => 'age',
        '所属' => 'affiliation',

        '学年クラス' => 'grade_class',
        '学年・クラス' => 'grade_class',
        'クラス' => 'grade_class',

        '一人称' => 'first_person',

        '口調の例' => 'tone_examples',
        'セリフ例' => 'tone_examples',
        '台詞例' => 'tone_examples',
        '口調' => 'tone',

        '性格・特徴' => 'personality',
        '性格' => 'personality',
        '特徴' => 'personality',

        '外見の特徴' => 'appearance',
        '外見' => 'appearance',
        '容姿' => 'appearance',

        '背景・経歴' => 'background',
        '背景、経歴' => 'background',
        '背景' => 'background',
        '経歴' => 'background',
    ];

    public function parse(string $text): array
    {
        $text = str_replace(["\r", "\r"], "", trim($text));
        $text = preg_replace('/[ \t　]+/u', ' ', $text);

        $result = [
            'name' => null,
            'name_kana' => null,
            'age' => null,
            'affiliation' => null,
            'grade_class' => null,
            'first_person' => null,
            'tone' => null,
            'tone_examples' => null,
            'personality' => null,
            'appearance' => null,
            'background' => null,
        ];

        $headingName = $this->extractHeadingName($text);

        $labels = array_keys($this->labelMap);

        // 長いラベルを優先する
        usort($labels, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        $labelPattern = implode('|', array_map(fn ($label) => preg_quote($label, '/'), $labels));

        // 「名前:」「年齢：」などの位置を全部取得する。改行なしでもOK。
        preg_match_all(
            '/(' . $labelPattern . ')[ \t　]*[:：]/u',
            $text,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        $found = [];

        foreach ($matches[1] as $index => $match) {
            $label = $match[0];
            $labelStart = $match[1];

            $fullMatch = $matches[0][$index][0];
            $fullStart = $matches[0][$index][1];
            $valueStart = $fullStart + strlen($fullMatch);

            $found[] = [
                'label' => $label,
                'key' => $this->labelMap[$label],
                'label_start' => $labelStart,
                'value_start' => $valueStart,
            ];
        }

        foreach ($found as $index => $item) {
            $nextStart = $found[$index + 1]['label_start'] ?? strlen($text);

            $value = substr($text, $item['value_start'], $nextStart - $item['value_start']);
            $value = $this->cleanValue($value);

            if ($value !== null) {
                $result[$item['key']] = $value;
            }
        }

        if (empty($result['name']) && $headingName) {
            $result['name'] = $headingName;
        }

        return $result;
    }

    private function extractHeadingName(string $text): ?string
    {
        $lines = array_values(array_filter(array_map('trim', explode("", $text))));

        foreach ($lines as $line) {
            if (preg_match('/^[■□●○★☆#]+[ \t　]*(.+)$/u', $line, $matches)) {
                $heading = trim($matches[1]);

                // 見出しの後ろに「名前:」などが続いていたら、そこより前だけを見出し名にする
                $heading = preg_split('/名前[ \t　]*[:：]/u', $heading)[0] ?? $heading;

                return trim($heading) ?: null;
            }
        }

        return null;
    }

    private function cleanValue(string $value): ?string
    {
        $value = trim($value);

        // 先頭の装飾や不要スペースを軽く除去
        $value = preg_replace('/^[■□●○★☆#\s　]+/u', '', $value);

        // 3行以上の空行を2行にまとめる
        $value = preg_replace("/{3,}/", "", $value);

        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
