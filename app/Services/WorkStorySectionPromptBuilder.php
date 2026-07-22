<?php

namespace App\Services;

use App\Models\WorkStorySection;
use Illuminate\Support\Collection;

class WorkStorySectionPromptBuilder
{
    public function build(
        ?int $legacySectionId,
        array $selectedRanges = []
    ): string {
        $ranges = collect($selectedRanges)
            ->filter(fn ($range): bool =>
                is_array($range)
                && ! empty($range['section_id'])
                && ! empty($range['start'])
                && ! empty($range['end'])
            )
            ->map(fn (array $range): array => [
                'section_id' => (int) $range['section_id'],
                'start' => (int) $range['start'],
                'end' => (int) $range['end'],
            ])
            ->sortBy([
                ['section_id', 'asc'],
                ['start', 'asc'],
            ])
            ->values();

        if ($ranges->isNotEmpty()) {
            return $this->buildRanges($ranges);
        }

        if (! $legacySectionId) {
            return '';
        }

        $section = $this->sectionQuery()
            ->find($legacySectionId);

        return $section
            ? $this->buildSection($section, null, null)
            : '';
    }

    private function buildRanges(Collection $ranges): string
    {
        $sectionIds = $ranges
            ->pluck('section_id')
            ->unique()
            ->values();

        $sections = $this->sectionQuery()
            ->whereIn('id', $sectionIds)
            ->get()
            ->keyBy('id');

        $blocks = [];

        foreach (
            $ranges->groupBy('section_id')
            as $sectionId => $sectionRanges
        ) {
            $section = $sections->get((int) $sectionId);

            if (! $section) {
                continue;
            }

            foreach ($sectionRanges as $range) {
                $blocks[] = $this->buildSection(
                    $section,
                    $range['start'],
                    $range['end']
                );
            }
        }

        return collect($blocks)
            ->filter()
            ->implode(PHP_EOL . PHP_EOL);
    }

    private function sectionQuery()
    {
        return WorkStorySection::query()
            ->with([
                'work.parentWork',
                'parentSection',
                'events',
                'characters',
            ])
            ->whereIn(
                'status',
                ['draft', 'published']
            )
            ->whereHas(
                'work',
                fn ($query) =>
                    $query->where('status', 'published')
            );
    }

    private function buildSection(
        WorkStorySection $section,
        ?int $start,
        ?int $end
    ): string {
        $lines = [
            '章・編：' . $this->sectionLabel($section),
        ];

        if ($start !== null && $end !== null) {
            $lines[] =
                "参照範囲：物語詳細{$start}～{$end}";
        }

        $this->append($lines, '概要', $section->synopsis);
        $this->append(
            $lines,
            'この章までの設定',
            $section->cumulative_settings
        );

        $events = $section->events
            ->sortBy([
                ['sort_order', 'asc'],
                ['id', 'asc'],
            ])
            ->values();

        if ($start !== null && $end !== null) {
            $events = $events
                ->slice($start - 1, $end - $start + 1)
                ->values();
        }

        if ($events->isNotEmpty()) {
            $lines[] = '■ この章・編の物語詳細';

            foreach ($events as $index => $event) {
                $number = $start !== null
                    ? $start + $index
                    : $index + 1;

                $lines[] =
                    $number . '. '
                    . ($event->title ?: '名称未設定');

                $this->append($lines, 'タイミング', $event->timing, '  ');
                $this->append($lines, '場所', $event->location, '  ');
                $this->append($lines, '詳細', $event->summary, '  ');
                $this->append($lines, '結果', $event->outcome, '  ');
                $this->append($lines, '備考', $event->notes, '  ');
            }
        }

        if ($section->characters->isNotEmpty()) {
            $lines[] = '■ この章・編の登場人物';

            foreach ($section->characters as $character) {
                $lines[] = '・' . $character->name;
                $pivot = $character->pivot;

                $this->append($lines, '年齢', $pivot->age_at_section, '  ');
                $this->append($lines, '学年', $pivot->school_grade_at_section, '  ');
                $this->append($lines, 'クラス', $pivot->class_at_section, '  ');
                $this->append($lines, '所属', $pivot->affiliation_at_section, '  ');
                $this->append($lines, '役職', $pivot->position_at_section, '  ');
                $this->append($lines, '状態', $pivot->character_state, '  ');
                $this->append($lines, '備考', $pivot->notes, '  ');
            }
        }

        return implode(PHP_EOL, $lines);
    }

    private function sectionLabel(
        WorkStorySection $section
    ): string {
        $parts = [];

        if ($section->parentSection) {
            $parts[] = $section->parentSection->title;
        }

        $parts[] = trim(
            ($section->short_label
                ? $section->short_label . ' '
                : '')
            . $section->title
        );

        return implode(' ＞ ', $parts);
    }

    private function append(
        array &$lines,
        string $label,
        mixed $value,
        string $prefix = ''
    ): void {
        $value = trim((string) ($value ?? ''));

        if ($value !== '') {
            $lines[] = $prefix . $label . '：' . $value;
        }
    }
}
