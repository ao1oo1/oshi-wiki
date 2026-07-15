<?php

namespace App\Services;

use App\Models\Work;

class WorkWorldbuildingPromptBuilder
{
    public function build(?int $workId, array $selectedCategories): string
    {
        if (! $workId || $selectedCategories === []) {
            return '';
        }

        $work = Work::query()
            ->with(['canonEvents', 'termUsages'])
            ->where('status', 'published')
            ->find($workId);

        if (! $work) {
            return '';
        }

        $selectedCategories = array_values(array_intersect(
            $selectedCategories,
            array_keys($this->categories())
        ));

        $blocks = [];

        foreach ($selectedCategories as $category) {
            $block = $this->buildCategory($work, $category);

            if ($block !== '') {
                $blocks[] = $block;
            }
        }

        return implode(PHP_EOL . PHP_EOL, $blocks);
    }

    public function categories(): array
    {
        return [
            'story_design' => '物語の設計',
            'buildings' => '建物・空間',
            'life_rules' => '生活・ルール',
            'organizations' => '組織・制度',
            'events_time' => '行事・時間の流れ',
            'geography' => '地理・周辺環境',
            'sensory' => '小物・感覚的な情報',
            'canon_events' => '原作の重要イベント年表',
            'term_usages' => '用語の使用例',
        ];
    }

    private function buildCategory(Work $work, string $category): string
    {
        return match ($category) {
            'story_design' => $this->fieldBlock($work, '物語の設計', [
                '時間軸の指定' => 'timeline_setting',
            ]),
            'buildings' => $this->fieldBlock($work, '建物・空間', [
                '校舎や寮の間取り・構造' => 'building_layout',
                'キャラごとの部屋・席の位置' => 'character_room_seat',
                'キャラがよくいる場所・たまり場' => 'hangout_places',
                '立ち入り禁止区域・秘密の場所' => 'restricted_secret_places',
                '食堂・購買のメニューや人気商品' => 'cafeteria_store_menu',
            ]),
            'life_rules' => $this->fieldBlock($work, '生活・ルール', [
                '一日のスケジュール' => 'daily_schedule',
                '校則・寮則' => 'school_dorm_rules',
                '制服の詳細' => 'uniform_details',
                '私服・休日の過ごし方のルール' => 'casual_holiday_rules',
                '当番制度' => 'duty_system',
            ]),
            'organizations' => $this->fieldBlock($work, '組織・制度', [
                'クラス編成・学年の仕組み' => 'class_grade_system',
                '生徒会・委員会・部活動とキャラの所属' => 'organizations_memberships',
                '成績・序列の制度' => 'ranking_system',
                '教師・寮母など大人キャラの配置と役割' => 'adult_roles',
            ]),
            'events_time' => $this->fieldBlock($work, '行事・時間の流れ', [
                '年間行事とその時期' => 'annual_events',
                '行事の具体的な流れ・名物イベント' => 'event_flow',
                '作中の季節・月がわかる情報' => 'story_season',
            ]),
            'geography' => $this->fieldBlock($work, '地理・周辺環境', [
                '学園の所在地' => 'school_location',
                '通学手段・通学路の風景' => 'commute_environment',
                '近くの店・生徒の行きつけ' => 'nearby_shops',
                '気候・自然環境' => 'climate_nature',
            ]),
            'sensory' => $this->fieldBlock($work, '小物・感覚的な情報', [
                '音' => 'sounds',
                '学園の象徴的なモチーフ' => 'symbolic_motifs',
                '持ち物の指定' => 'required_belongings',
            ]),
            'canon_events' => $this->canonEventsBlock($work),
            'term_usages' => $this->termUsagesBlock($work),
            default => '',
        };
    }

    private function fieldBlock(Work $work, string $heading, array $fields): string
    {
        $lines = [];

        foreach ($fields as $label => $column) {
            $value = trim((string) $work->{$column});

            if ($value !== '') {
                $lines[] = '・' . $label . '：' . $value;
            }
        }

        return $lines === []
            ? ''
            : '■ ' . $heading . PHP_EOL . implode(PHP_EOL, $lines);
    }

    private function canonEventsBlock(Work $work): string
    {
        $lines = [];

        foreach ($work->canonEvents->take(50) as $event) {
            $parts = array_values(array_filter([
                trim((string) $event->timing),
                trim((string) $event->event_name),
                trim((string) $event->event_status),
                trim((string) $event->notes),
            ], fn (string $value): bool => $value !== ''));

            if ($parts !== []) {
                $lines[] = '・' . implode('／', $parts);
            }
        }

        return $lines === []
            ? ''
            : '■ 原作の重要イベント年表' . PHP_EOL . implode(PHP_EOL, $lines);
    }

    private function termUsagesBlock(Work $work): string
    {
        $lines = [];

        foreach ($work->termUsages->take(50) as $termUsage) {
            $term = trim((string) $termUsage->term);
            $meaning = trim((string) $termUsage->meaning);
            $example = trim((string) $termUsage->usage_example);

            if ($term === '' && $meaning === '' && $example === '') {
                continue;
            }

            $line = '・' . ($term !== '' ? $term : '用語未入力');

            if ($meaning !== '') {
                $line .= '：' . $meaning;
            }

            if ($example !== '') {
                $line .= PHP_EOL . '  使用例：' . $example;
            }

            $lines[] = $line;
        }

        return $lines === []
            ? ''
            : '■ 用語の使用例' . PHP_EOL . implode(PHP_EOL, $lines);
    }
}
