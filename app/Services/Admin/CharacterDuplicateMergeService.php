<?php

namespace App\Services\Admin;

use App\Models\Character;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class CharacterDuplicateMergeService
{
    public function groups(): Collection
    {
        $duplicateKeys = Character::query()
            ->select([
                'work_id',
                'name',
                DB::raw('COUNT(*) as duplicate_count'),
                DB::raw('MIN(id) as keep_id'),
            ])
            ->groupBy('work_id', 'name')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('work_id')
            ->orderBy('name')
            ->get();

        if ($duplicateKeys->isEmpty()) {
            return collect();
        }

        $characters = Character::query()
            ->with([
                'work',
                'linkedWorks',
                'tags',
            ])
            ->where(function ($query) use ($duplicateKeys): void {
                foreach ($duplicateKeys as $key) {
                    $query->orWhere(function ($groupQuery) use ($key): void {
                        $groupQuery
                            ->where('work_id', $key->work_id)
                            ->where('name', $key->name);
                    });
                }
            })
            ->orderBy('work_id')
            ->orderBy('name')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Character $character): string =>
                $this->key(
                    (int) $character->work_id,
                    (string) $character->name
                )
            );

        return $duplicateKeys
            ->map(function ($key) use ($characters): array {
                $groupKey = $this->key(
                    (int) $key->work_id,
                    (string) $key->name
                );

                $items = $characters->get(
                    $groupKey,
                    collect()
                )->values();

                $keep = $items->first();
                $duplicates = $items->slice(1)->values();

                return [
                    'token' => $this->encodeToken(
                        (int) $key->work_id,
                        (string) $key->name
                    ),
                    'work_id' => (int) $key->work_id,
                    'work_title' =>
                        $keep?->work?->title
                        ?? '作品ID ' . $key->work_id,
                    'name' => (string) $key->name,
                    'keep' => $keep,
                    'duplicates' => $duplicates,
                    'duplicate_count' => $duplicates->count(),
                    'total_count' => $items->count(),
                ];
            })
            ->filter(
                fn (array $group): bool =>
                    $group['keep'] instanceof Character
                    && $group['duplicate_count'] > 0
            )
            ->values();
    }

    public function mergeTokens(array $tokens): array
    {
        $tokens = array_values(array_unique($tokens));

        return DB::transaction(function () use ($tokens): array {
            $mergedGroups = 0;
            $deletedCharacters = 0;
            $skippedGroups = 0;

            foreach ($tokens as $token) {
                [$workId, $name] = $this->decodeToken($token);

                $characters = Character::query()
                    ->where('work_id', $workId)
                    ->where('name', $name)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($characters->count() < 2) {
                    $skippedGroups++;

                    continue;
                }

                $keep = $characters->firstOrFail();
                $duplicates = $characters->slice(1)->values();

                foreach ($duplicates as $duplicate) {
                    $this->moveReferences($keep, $duplicate);
                    $duplicate->delete();
                    $deletedCharacters++;
                }

                $mergedGroups++;
            }

            return [
                'merged_groups' => $mergedGroups,
                'deleted_characters' => $deletedCharacters,
                'skipped_groups' => $skippedGroups,
            ];
        }, 3);
    }

    private function moveReferences(
        Character $keep,
        Character $duplicate
    ): void {
        $this->moveCharacterTags($keep, $duplicate);
        $this->moveCharacterWorks($keep, $duplicate);
        $this->moveStorySections($keep, $duplicate);
        $this->moveOfficialRelationships($keep, $duplicate);
        $this->moveWriterRelationships($keep, $duplicate);
        $this->moveSavedPromptReferences($keep, $duplicate);
        $this->moveHelpfulVotes($keep, $duplicate);
    }

    private function moveCharacterTags(
        Character $keep,
        Character $duplicate
    ): void {
        if (! Schema::hasTable('character_tag')) {
            return;
        }

        $rows = DB::table('character_tag')
            ->where('character_id', $duplicate->id)
            ->get();

        foreach ($rows as $row) {
            DB::table('character_tag')->insertOrIgnore([
                'character_id' => $keep->id,
                'tag_id' => $row->tag_id,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('character_tag')
            ->where('character_id', $duplicate->id)
            ->delete();
    }

    private function moveCharacterWorks(
        Character $keep,
        Character $duplicate
    ): void {
        if (! Schema::hasTable('character_work')) {
            return;
        }

        $rows = DB::table('character_work')
            ->where('character_id', $duplicate->id)
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $existing = DB::table('character_work')
                ->where('character_id', $keep->id)
                ->where('work_id', $row->work_id)
                ->first();

            if ($existing) {
                DB::table('character_work')
                    ->where('id', $existing->id)
                    ->update([
                        'is_primary' =>
                            (int) $row->work_id
                                === (int) $keep->work_id,
                        'appearance_type' =>
                            $existing->appearance_type
                            ?: $row->appearance_type,
                        'notes' =>
                            $existing->notes
                            ?: $row->notes,
                        'sort_order' => min(
                            (int) $existing->sort_order,
                            (int) $row->sort_order
                        ),
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table('character_work')->insert([
                'character_id' => $keep->id,
                'work_id' => $row->work_id,
                'is_primary' =>
                    (int) $row->work_id
                        === (int) $keep->work_id,
                'appearance_type' => $row->appearance_type,
                'sort_order' => $row->sort_order,
                'notes' => $row->notes,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('character_work')
            ->where('character_id', $duplicate->id)
            ->delete();

        DB::table('character_work')
            ->where('character_id', $keep->id)
            ->update([
                'is_primary' => false,
                'updated_at' => now(),
            ]);

        DB::table('character_work')->updateOrInsert(
            [
                'character_id' => $keep->id,
                'work_id' => $keep->work_id,
            ],
            [
                'is_primary' => true,
                'sort_order' => 0,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function moveStorySections(
        Character $keep,
        Character $duplicate
    ): void {
        if (! Schema::hasTable(
            'character_work_story_section'
        )) {
            return;
        }

        $rows = DB::table(
            'character_work_story_section'
        )
            ->where('character_id', $duplicate->id)
            ->orderBy('id')
            ->get();

        foreach ($rows as $row) {
            $existing = DB::table(
                'character_work_story_section'
            )
                ->where(
                    'work_story_section_id',
                    $row->work_story_section_id
                )
                ->where('character_id', $keep->id)
                ->first();

            if ($existing) {
                DB::table(
                    'character_work_story_section'
                )
                    ->where('id', $existing->id)
                    ->update([
                        'appearance_type' =>
                            $existing->appearance_type
                            ?: $row->appearance_type,
                        'age_at_section' =>
                            $existing->age_at_section
                            ?: $row->age_at_section,
                        'school_grade_at_section' =>
                            $existing->school_grade_at_section
                            ?: $row->school_grade_at_section,
                        'class_at_section' =>
                            $existing->class_at_section
                            ?: $row->class_at_section,
                        'affiliation_at_section' =>
                            $existing->affiliation_at_section
                            ?: $row->affiliation_at_section,
                        'position_at_section' =>
                            $existing->position_at_section
                            ?: $row->position_at_section,
                        'character_state' =>
                            $existing->character_state
                            ?: $row->character_state,
                        'first_appearance' =>
                            (bool) $existing->first_appearance
                            || (bool) $row->first_appearance,
                        'notes' =>
                            $existing->notes
                            ?: $row->notes,
                        'sort_order' => min(
                            (int) $existing->sort_order,
                            (int) $row->sort_order
                        ),
                        'updated_at' => now(),
                    ]);

                continue;
            }

            DB::table(
                'character_work_story_section'
            )->insert([
                'work_story_section_id' =>
                    $row->work_story_section_id,
                'character_id' => $keep->id,
                'appearance_type' =>
                    $row->appearance_type,
                'age_at_section' =>
                    $row->age_at_section,
                'school_grade_at_section' =>
                    $row->school_grade_at_section,
                'class_at_section' =>
                    $row->class_at_section,
                'affiliation_at_section' =>
                    $row->affiliation_at_section,
                'position_at_section' =>
                    $row->position_at_section,
                'character_state' =>
                    $row->character_state,
                'first_appearance' =>
                    $row->first_appearance,
                'notes' => $row->notes,
                'sort_order' => $row->sort_order,
                'created_at' => $row->created_at ?? now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('character_work_story_section')
            ->where('character_id', $duplicate->id)
            ->delete();
    }

    private function moveOfficialRelationships(
        Character $keep,
        Character $duplicate
    ): void {
        if (! Schema::hasTable('character_relationships')) {
            return;
        }

        DB::table('character_relationships')
            ->where('from_character_id', $duplicate->id)
            ->where('to_character_id', $keep->id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('character_relationships')
            ->where('from_character_id', $keep->id)
            ->where('to_character_id', $duplicate->id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('character_relationships')
            ->where('from_character_id', $duplicate->id)
            ->update([
                'from_character_id' => $keep->id,
                'updated_at' => now(),
            ]);

        DB::table('character_relationships')
            ->where('to_character_id', $duplicate->id)
            ->update([
                'to_character_id' => $keep->id,
                'updated_at' => now(),
            ]);
    }

    private function moveWriterRelationships(
        Character $keep,
        Character $duplicate
    ): void {
        if (! Schema::hasTable(
            'original_character_relationships'
        )) {
            return;
        }

        DB::table('original_character_relationships')
            ->where('from_character_id', $duplicate->id)
            ->update([
                'from_character_id' => $keep->id,
                'updated_at' => now(),
            ]);

        DB::table('original_character_relationships')
            ->where('to_character_id', $duplicate->id)
            ->update([
                'to_character_id' => $keep->id,
                'updated_at' => now(),
            ]);
    }

    private function moveSavedPromptReferences(
        Character $keep,
        Character $duplicate
    ): void {
        if (
            ! Schema::hasTable('saved_prompts')
            || ! Schema::hasColumn(
                'saved_prompts',
                'selected_character_refs'
            )
        ) {
            return;
        }

        $oldRef = 'v1:' . $duplicate->id;
        $newRef = 'v1:' . $keep->id;

        DB::table('saved_prompts')
            ->whereNotNull('selected_character_refs')
            ->orderBy('id')
            ->chunkById(
                200,
                function ($prompts) use (
                    $oldRef,
                    $newRef
                ): void {
                    foreach ($prompts as $prompt) {
                        $refs = json_decode(
                            (string) $prompt
                                ->selected_character_refs,
                            true
                        );

                        if (
                            ! is_array($refs)
                            || ! in_array(
                                $oldRef,
                                $refs,
                                true
                            )
                        ) {
                            continue;
                        }

                        $refs = array_values(array_unique(
                            array_map(
                                fn ($ref) =>
                                    $ref === $oldRef
                                        ? $newRef
                                        : $ref,
                                $refs
                            )
                        ));

                        DB::table('saved_prompts')
                            ->where('id', $prompt->id)
                            ->update([
                                'selected_character_refs' =>
                                    json_encode(
                                        $refs,
                                        JSON_UNESCAPED_UNICODE
                                        | JSON_UNESCAPED_SLASHES
                                    ),
                                'updated_at' => now(),
                            ]);
                    }
                }
            );
    }

    private function moveHelpfulVotes(
        Character $keep,
        Character $duplicate
    ): void {
        if (! Schema::hasTable('helpful_votes')) {
            return;
        }

        $typeCandidates = [
            Character::class,
            'character',
            'characters',
        ];

        DB::table('helpful_votes')
            ->whereIn('target_type', $typeCandidates)
            ->where('target_id', $duplicate->id)
            ->update([
                'target_id' => $keep->id,
                'updated_at' => now(),
            ]);
    }

    private function key(int $workId, string $name): string
    {
        return $workId . "\0" . $name;
    }

    private function encodeToken(
        int $workId,
        string $name
    ): string {
        return rtrim(strtr(base64_encode(
            json_encode(
                [
                    'work_id' => $workId,
                    'name' => $name,
                ],
                JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            ) ?: ''
        ), '+/', '-_'), '=');
    }

    private function decodeToken(string $token): array
    {
        $padding = strlen($token) % 4;

        if ($padding > 0) {
            $token .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode(
            strtr($token, '-_', '+/'),
            true
        );

        $payload = $decoded !== false
            ? json_decode($decoded, true)
            : null;

        $workId = (int) ($payload['work_id'] ?? 0);
        $name = (string) ($payload['name'] ?? '');

        if ($workId < 1 || $name === '') {
            throw new RuntimeException(
                '重複グループの指定が不正です。'
            );
        }

        return [$workId, $name];
    }
}
