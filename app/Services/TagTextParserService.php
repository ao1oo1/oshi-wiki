<?php

namespace App\Services;

class TagTextParserService
{
    private array $labelMap = [
        'タグ名' => 'name',
        '名前' => 'name',
        '名称' => 'name',

        '種類' => 'type',
        'タイプ' => 'type',

        '説明' => 'description',
        '概要' => 'description',

        '状態' => 'status',
    ];

    public function parse(string $text): array
    {
        $text = str_replace(["\r", "\r"], "", trim($text));
        $text = preg_replace('/[ \t　]+/u', ' ', $text);

        $result = [
            'name' => null,
            'type' => null,
            'description' => null,
            'status' => null,
        ];

        $headingName = $this->extractHeading($text);

        $labels = array_keys($this->labelMap);
        usort($labels, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        $labelPattern = implode('|', array_map(fn ($label) => preg_quote($label, '/'), $labels));

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

            $found[] = [
                'label' => $label,
                'key' => $this->labelMap[$label],
                'label_start' => $labelStart,
                'value_start' => $fullStart + strlen($fullMatch),
            ];
        }

        foreach ($found as $index => $item) {
            $nextStart = $found[$index + 1]['label_start'] ?? strlen($text);
            $value = substr($text, $item['value_start'], $nextStart - $item['value_start']);
            $result[$item['key']] = $this->clean($value);
        }

        if (empty($result['name']) && $headingName) {
            $result['name'] = $headingName;
        }

        if (empty($result['type'])) {
            $result['type'] = 'general';
        }

        if (empty($result['status'])) {
            $result['status'] = 'draft';
        }

        return $result;
    }

    private function extractHeading(string $text): ?string
    {
        $lines = array_values(array_filter(array_map('trim', explode("", $text))));

        foreach ($lines as $line) {
            if (preg_match('/^[■□●○★☆#]+[ \t　]*(.+)$/u', $line, $matches)) {
                $heading = trim($matches[1]);
                $heading = preg_split('/タグ名[ \t　]*[:：]/u', $heading)[0] ?? $heading;
                return trim($heading) ?: null;
            }
        }

        return null;
    }

    private function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = preg_replace('/^[■□●○★☆#\s　]+/u', '', trim($value));
        $value = preg_replace("/{3,}/", "", $value);
        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
