<?php

namespace App\Services;

use App\Models\WorkStorySection;

class WorkStorySectionPromptBuilder
{
    public function build(?int $sectionId): string
    {
        if (! $sectionId) {
            return '';
        }

        $section = WorkStorySection::query()
            ->where('status', 'published')
            ->with([
                'work.parentWork',
                'parentSection',
                'events',
                'characters' => function ($query): void {
                    $query
                        ->where('characters.status', 'published')
                        ->orderByPivot('sort_order');
                },
            ])
            ->find($sectionId);

        if (! $section) {
            return '';
        }

        if (
            $section->work->parent_work_id !== null
            && $section->work->parentWork?->status
                !== 'published'
        ) {
            return '';
        }

        $lines = [
            '参照章・編：' . $this->sectionLabel($section),
        ];

        if ($section->parentSection) {
            $lines[] =
                '親となる編・部：'
                . $section->parentSection->title;
        }

        if (filled($section->synopsis)) {
            $lines[] = '';
            $lines[] = '■ 章・編の概要';
            $lines[] = trim((string) $section->synopsis);
        }

        if (filled($section->cumulative_settings)) {
            $lines[] = '';
            $lines[] = '■ この章までに登場する設定';
            $lines[] = trim(
                (string) $section->cumulative_settings
            );
        }

        if ($section->events->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '■ この章・編の物語詳細';

            foreach ($section->events as $index => $event) {
                $header = '・' . ($index + 1) . '. '
                    . $event->title;

                $meta = collect([
                    $event->timing
                        ? '時期：' . $event->timing
                        : null,
                    $event->location
                        ? '場所：' . $event->location
                        : null,
                ])->filter()->implode(' / ');

                $lines[] = $meta !== ''
                    ? $header . '（' . $meta . '）'
                    : $header;

                if (filled($event->summary)) {
                    $lines[] =
                        '  詳細：'
                        . trim((string) $event->summary);
                }

                if (filled($event->outcome)) {
                    $lines[] =
                        '  結果：'
                        . trim((string) $event->outcome);
                }

                if (filled($event->notes)) {
                    $lines[] =
                        '  備考：'
                        . trim((string) $event->notes);
                }
            }
        }

        if ($section->characters->isNotEmpty()) {
            $lines[] = '';
            $lines[] = '■ 章・編時点の登場キャラクター';

            foreach ($section->characters as $character) {
                $pivot = $character->pivot;

                $snapshot = collect([
                    $pivot->age_at_section
                        ? '年齢：' . $pivot->age_at_section
                        : null,
                    $pivot->school_grade_at_section
                        ? '学年：'
                            . $pivot->school_grade_at_section
                        : null,
                    $pivot->class_at_section
                        ? 'クラス：'
                            . $pivot->class_at_section
                        : null,
                    $pivot->affiliation_at_section
                        ? '所属：'
                            . $pivot->affiliation_at_section
                        : null,
                    $pivot->position_at_section
                        ? '役職：'
                            . $pivot->position_at_section
                        : null,
                    $pivot->first_appearance
                        ? '初登場'
                        : null,
                ])->filter()->implode(' / ');

                $lines[] = '・' . $character->name
                    . (
                        $snapshot !== ''
                            ? '（' . $snapshot . '）'
                            : ''
                    );

                if (filled($pivot->character_state)) {
                    $lines[] =
                        '  当時の状態・立場：'
                        . trim(
                            (string) $pivot->character_state
                        );
                }

                if (filled($pivot->notes)) {
                    $lines[] =
                        '  備考：'
                        . trim((string) $pivot->notes);
                }
            }
        }

        if (filled($section->notes)) {
            $lines[] = '';
            $lines[] = '■ 章・編の備考';
            $lines[] = trim((string) $section->notes);
        }

        $lines[] = '';
        $lines[] =
            'この章・編の時点より後に判明する設定や、'
            . '後の章で変化する年齢・学年・所属を、'
            . 'この時点の事実として混在させないでください。';

        return implode(PHP_EOL, $lines);
    }

    private function sectionLabel(
        WorkStorySection $section
    ): string {
        return collect([
            $section->short_label,
            $section->title,
        ])->filter()->unique()->implode(' ');
    }
}
