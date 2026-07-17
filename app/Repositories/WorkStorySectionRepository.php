<?php

namespace App\Repositories;

use App\Models\Work;
use App\Models\WorkStorySection;
use Illuminate\Support\Collection;

class WorkStorySectionRepository
{
    public function allForWork(Work $work): Collection
    {
        return WorkStorySection::query()
            ->withCount(['events', 'characters'])
            ->with('parentSection')
            ->where('work_id', $work->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function create(array $data): WorkStorySection
    {
        return WorkStorySection::query()->create($data);
    }

    public function update(
        WorkStorySection $section,
        array $data
    ): bool {
        return $section->update($data);
    }

    public function syncEvents(
        WorkStorySection $section,
        array $events
    ): void {
        $section->events()->delete();

        foreach (array_values($events) as $index => $event) {
            $section->events()->create([
                'event_number' =>
                    $event['event_number'] ?? null,
                'title' => $event['title'],
                'timing' => $event['timing'] ?? null,
                'summary' => $event['summary'] ?? null,
                'location' => $event['location'] ?? null,
                'outcome' => $event['outcome'] ?? null,
                'spoiler_level' =>
                    $event['spoiler_level'] ?? 'none',
                'notes' => $event['notes'] ?? null,
                'sort_order' =>
                    $event['sort_order'] ?? ($index + 1),
            ]);
        }
    }

    public function syncCharacters(
        WorkStorySection $section,
        array $characters
    ): void {
        $sync = [];

        foreach (
            array_values($characters)
            as $index => $character
        ) {
            $sync[(int) $character['character_id']] = [
                'appearance_type' =>
                    $character['appearance_type']
                        ?? 'appears',
                'age_at_section' =>
                    $character['age_at_section'] ?? null,
                'school_grade_at_section' =>
                    $character['school_grade_at_section']
                        ?? null,
                'class_at_section' =>
                    $character['class_at_section'] ?? null,
                'affiliation_at_section' =>
                    $character['affiliation_at_section']
                        ?? null,
                'position_at_section' =>
                    $character['position_at_section']
                        ?? null,
                'character_state' =>
                    $character['character_state'] ?? null,
                'first_appearance' =>
                    (bool) (
                        $character['first_appearance']
                            ?? false
                    ),
                'notes' => $character['notes'] ?? null,
                'sort_order' =>
                    $character['sort_order']
                        ?? ($index + 1),
            ];
        }

        $section->characters()->sync($sync);
    }

    public function delete(WorkStorySection $section): bool
    {
        return $section->delete();
    }
}
