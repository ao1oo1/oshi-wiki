<?php

namespace App\Services;

class WorkStorySectionTextParserService
{
    public function parse(string $text): array
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = explode("\n", $text);

        $result = [
            'events' => [],
            'section_characters' => [],
        ];

        $mode = null;
        $eventIndex = -1;
        $characterIndex = -1;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '') {
                continue;
            }

            if (preg_match('/^■\s*(.+)$/u', $trimmed, $m)) {
                $result['title'] = trim($m[1]);
                continue;
            }

            if (
                preg_match(
                    '/^(物語詳細|出来事)[:：]?$/u',
                    $trimmed
                )
            ) {
                $mode = 'events';
                continue;
            }

            if (
                preg_match(
                    '/^登場キャラクター[:：]?$/u',
                    $trimmed
                )
            ) {
                $mode = 'characters';
                continue;
            }

            if (
                $mode === 'events'
                && preg_match(
                    '/^(?:[-・]|\d+[.．])\s*(.+)$/u',
                    $trimmed,
                    $m
                )
            ) {
                $eventIndex++;
                $result['events'][$eventIndex] = [
                    'title' => trim($m[1]),
                    'sort_order' => $eventIndex + 1,
                ];
                continue;
            }

            if (
                $mode === 'characters'
                && preg_match(
                    '/^[-・]\s*(.+)$/u',
                    $trimmed,
                    $m
                )
            ) {
                $characterIndex++;
                $result['section_characters'][$characterIndex] = [
                    'character_name' => trim($m[1]),
                    'selected' => 1,
                    'sort_order' => $characterIndex + 1,
                ];
                continue;
            }

            if (! preg_match(
                '/^([^:：]+)[:：]\s*(.*)$/u',
                $trimmed,
                $m
            )) {
                if (
                    $mode === 'events'
                    && $eventIndex >= 0
                ) {
                    $result['events'][$eventIndex]['summary'] =
                        trim(
                            (
                                $result['events'][$eventIndex]['summary']
                                    ?? ''
                            )
                            . "\n"
                            . $trimmed
                        );
                }

                continue;
            }

            $key = trim($m[1]);
            $value = trim($m[2]);

            if ($mode === 'events' && $eventIndex >= 0) {
                $field = match ($key) {
                    '番号' => 'event_number',
                    'タイミング', '時期' => 'timing',
                    '場所' => 'location',
                    '詳細', '概要' => 'summary',
                    '結果' => 'outcome',
                    '備考' => 'notes',
                    default => null,
                };

                if ($field) {
                    $result['events'][$eventIndex][$field] =
                        $value;
                }

                continue;
            }

            if (
                $mode === 'characters'
                && $characterIndex >= 0
            ) {
                $field = match ($key) {
                    '年齢' => 'age_at_section',
                    '学年' => 'school_grade_at_section',
                    'クラス' => 'class_at_section',
                    '所属' => 'affiliation_at_section',
                    '役職' => 'position_at_section',
                    '状態', '当時の状態' => 'character_state',
                    '備考' => 'notes',
                    '初登場' => 'first_appearance',
                    default => null,
                };

                if ($field) {
                    $result['section_characters']
                        [$characterIndex][$field] = $value;
                }

                continue;
            }

            $field = match ($key) {
                '種別' => 'section_type',
                '章番号', '番号' => 'section_number',
                '章名', '編名', 'タイトル' => 'title',
                '読み仮名' => 'title_kana',
                '短い表示名', '表示名' => 'short_label',
                '概要' => 'synopsis',
                'この章までに登場する設定',
                'その章までの設定',
                '累積設定' => 'cumulative_settings',
                '備考' => 'notes',
                'ネタバレ区分' => 'spoiler_level',
                '表示順' => 'sort_order',
                default => null,
            };

            if ($field) {
                $result[$field] = $value;
            }
        }

        return $result;
    }
}
