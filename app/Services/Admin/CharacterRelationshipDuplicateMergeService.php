<?php

namespace App\Services\Admin;

use App\Models\CharacterRelationship;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CharacterRelationshipDuplicateMergeService
{
    public function groups(): Collection
    {
        $keys = CharacterRelationship::query()
            ->select([
                'work_id',
                'from_character_id',
                'to_character_id',
                DB::raw('COUNT(*) as duplicate_count'),
                DB::raw('MIN(id) as keep_id'),
            ])
            ->groupBy(
                'work_id',
                'from_character_id',
                'to_character_id'
            )
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('work_id')
            ->orderBy('from_character_id')
            ->orderBy('to_character_id')
            ->get();

        if ($keys->isEmpty()) {
            return collect();
        }

        $relationships = CharacterRelationship::query()
            ->with([
                'work',
                'fromCharacter',
                'toCharacter',
            ])
            ->where(function ($query) use ($keys): void {
                foreach ($keys as $key) {
                    $query->orWhere(
                        function ($groupQuery) use ($key): void {
                            $groupQuery
                                ->where('work_id', $key->work_id)
                                ->where(
                                    'from_character_id',
                                    $key->from_character_id
                                )
                                ->where(
                                    'to_character_id',
                                    $key->to_character_id
                                );
                        }
                    );
                }
            })
            ->orderBy('work_id')
            ->orderBy('from_character_id')
            ->orderBy('to_character_id')
            ->orderBy('id')
            ->get()
            ->groupBy(
                fn (CharacterRelationship $relationship): string =>
                    $this->key(
                        (int) $relationship->work_id,
                        (int) $relationship->from_character_id,
                        (int) $relationship->to_character_id
                    )
            );

        return $keys
            ->map(function ($key) use ($relationships): array {
                $groupKey = $this->key(
                    (int) $key->work_id,
                    (int) $key->from_character_id,
                    (int) $key->to_character_id
                );

                $items = $relationships
                    ->get($groupKey, collect())
                    ->values();

                $keep = $items->first();
                $duplicates = $items->slice(1)->values();

                return [
                    'token' => $this->encodeToken(
                        (int) $key->work_id,
                        (int) $key->from_character_id,
                        (int) $key->to_character_id
                    ),
                    'work_id' => (int) $key->work_id,
                    'work_title' =>
                        $keep?->work?->title
                        ?? '作品ID ' . $key->work_id,
                    'from_character_id' =>
                        (int) $key->from_character_id,
                    'from_character_name' =>
                        $keep?->fromCharacter?->name
                        ?? 'キャラクターID '
                            . $key->from_character_id,
                    'to_character_id' =>
                        (int) $key->to_character_id,
                    'to_character_name' =>
                        $keep?->toCharacter?->name
                        ?? 'キャラクターID '
                            . $key->to_character_id,
                    'keep' => $keep,
                    'duplicates' => $duplicates,
                    'duplicate_count' => $duplicates->count(),
                    'total_count' => $items->count(),
                ];
            })
            ->filter(
                fn (array $group): bool =>
                    $group['keep']
                        instanceof CharacterRelationship
                    && $group['duplicate_count'] > 0
            )
            ->values();
    }

    public function mergeTokens(array $tokens): array
    {
        $tokens = array_values(array_unique($tokens));

        return DB::transaction(function () use ($tokens): array {
            $mergedGroups = 0;
            $deletedRelationships = 0;
            $skippedGroups = 0;

            foreach ($tokens as $token) {
                [
                    $workId,
                    $fromCharacterId,
                    $toCharacterId,
                ] = $this->decodeToken($token);

                $relationships =
                    CharacterRelationship::query()
                        ->where('work_id', $workId)
                        ->where(
                            'from_character_id',
                            $fromCharacterId
                        )
                        ->where(
                            'to_character_id',
                            $toCharacterId
                        )
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                if ($relationships->count() < 2) {
                    $skippedGroups++;

                    continue;
                }

                $relationships
                    ->slice(1)
                    ->each(
                        function (
                            CharacterRelationship $relationship
                        ) use (&$deletedRelationships): void {
                            $relationship->delete();
                            $deletedRelationships++;
                        }
                    );

                $mergedGroups++;
            }

            return [
                'merged_groups' => $mergedGroups,
                'deleted_relationships' =>
                    $deletedRelationships,
                'skipped_groups' => $skippedGroups,
            ];
        }, 3);
    }

    private function key(
        int $workId,
        int $fromCharacterId,
        int $toCharacterId
    ): string {
        return implode(
            ':',
            [
                $workId,
                $fromCharacterId,
                $toCharacterId,
            ]
        );
    }

    private function encodeToken(
        int $workId,
        int $fromCharacterId,
        int $toCharacterId
    ): string {
        $json = json_encode([
            'work_id' => $workId,
            'from_character_id' => $fromCharacterId,
            'to_character_id' => $toCharacterId,
        ]);

        return rtrim(
            strtr(base64_encode($json ?: ''), '+/', '-_'),
            '='
        );
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
        $fromCharacterId =
            (int) ($payload['from_character_id'] ?? 0);
        $toCharacterId =
            (int) ($payload['to_character_id'] ?? 0);

        if (
            $workId < 1
            || $fromCharacterId < 1
            || $toCharacterId < 1
        ) {
            throw new RuntimeException(
                '重複グループの指定が不正です。'
            );
        }

        return [
            $workId,
            $fromCharacterId,
            $toCharacterId,
        ];
    }
}
