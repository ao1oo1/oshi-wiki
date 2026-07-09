<?php

namespace App\Services;

use App\Models\Character;
use App\Models\OriginalCharacter;
use App\Models\OriginalCharacterRelationship;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PromptCharacterContextBuilder
{
    public function build(User $user, array $refs): array
    {
        $refs = collect($refs)
            ->filter(fn ($ref) => is_string($ref) && str_contains($ref, ':'))
            ->unique()
            ->values();

        if ($refs->isEmpty()) {
            return [
                'characters' => '',
                'relationships' => '',
            ];
        }

        $originalIds = $this->idsBySource($refs, 'original');
        $officialIds = $this->idsBySource($refs, 'v1_character');

        $originalCharacters = $this->originalCharacters($user, $originalIds);
        $officialCharacters = $this->officialCharacters($officialIds);

        return [
            'characters' => $this->buildCharacterText($originalCharacters, $officialCharacters),
            'relationships' => $this->buildRelationshipText($user, $refs, $officialIds),
        ];
    }

    private function idsBySource(Collection $refs, string $source): array
    {
        return $refs
            ->filter(fn ($ref) => str_starts_with($ref, $source . ':'))
            ->map(fn ($ref) => (int) str_replace($source . ':', '', $ref))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function originalCharacters(User $user, array $ids): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        return OriginalCharacter::query()
            ->whereIn('id', $ids)
            ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('user_id', $user->id))
            ->orderBy('name')
            ->get();
    }

    private function officialCharacters(array $ids): Collection
    {
        if (empty($ids)) {
            return collect();
        }

        return Character::query()
            ->with('work')
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->get();
    }

    private function buildCharacterText(Collection $originalCharacters, Collection $officialCharacters): string
    {
        $blocks = [];

        foreach ($originalCharacters as $character) {
            $blocks[] = $this->formatOriginalCharacter($character);
        }

        foreach ($officialCharacters as $character) {
            $blocks[] = $this->formatOfficialCharacter($character);
        }

        return implode("\n\n", array_filter($blocks));
    }

    private function formatOriginalCharacter(OriginalCharacter $character): string
    {
        $lines = [
            '■ オリジナルキャラクター：' . $character->name,
        ];

        $this->appendIfFilled($lines, '読み仮名', $character->name_kana);
        $this->appendIfFilled($lines, '年齢', $character->age);
        $this->appendIfFilled($lines, '性別', $character->gender);
        $this->appendIfFilled($lines, '所属', $character->affiliation);
        $this->appendIfFilled($lines, '学年・クラス', $character->school_grade);
        $this->appendIfFilled($lines, '一人称', $character->first_person);
        $this->appendIfFilled($lines, '口調', $character->speech_style);
        $this->appendIfFilled($lines, '口調例', $character->speech_examples);
        $this->appendIfFilled($lines, '性格・特徴', $character->personality);
        $this->appendIfFilled($lines, '外見', $character->appearance);
        $this->appendIfFilled($lines, '背景・経歴', $character->background);
        $this->appendIfFilled($lines, '絶対に守りたい設定', $character->important_points);
        $this->appendIfFilled($lines, 'NG設定・避けたい表現', $character->ng_points);
        $this->appendIfFilled($lines, '備考', $character->notes);

        return implode("\n", $lines);
    }

    private function formatOfficialCharacter(Character $character): string
    {
        $workTitle = $character->work?->title;
        $name = $workTitle ? $workTitle . ' ＞ ' . $character->name : $character->name;

        $lines = [
            '■ 作品キャラクター：' . $name,
        ];

        $this->appendIfFilled($lines, '読み仮名', $character->name_kana ?? null);
        $this->appendIfFilled($lines, '年齢', $character->age ?? null);
        $this->appendIfFilled($lines, '所属', $character->affiliation ?? null);
        $this->appendIfFilled($lines, '学年・クラス', $character->school_grade ?? null);
        $this->appendIfFilled($lines, '一人称', $character->first_person ?? null);
        $this->appendIfFilled($lines, '口調', $character->speech_style ?? null);
        $this->appendIfFilled($lines, '口調例', $character->speech_examples ?? null);
        $this->appendIfFilled($lines, '性格・特徴', $character->personality ?? null);
        $this->appendIfFilled($lines, '外見', $character->appearance ?? null);
        $this->appendIfFilled($lines, '背景・経歴', $character->background ?? null);

        return implode("\n", $lines);
    }

    private function appendIfFilled(array &$lines, string $label, mixed $value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        $lines[] = $label . '：' . $value;
    }

    private function buildRelationshipText(User $user, Collection $refs, array $officialIds): string
    {
        $blocks = [];

        $blocks = array_merge(
            $blocks,
            $this->buildOriginalRelationshipText($user, $refs)
        );

        $blocks = array_merge(
            $blocks,
            $this->buildOfficialRelationshipText($officialIds)
        );

        return implode("\n\n", array_filter($blocks));
    }

    private function buildOriginalRelationshipText(User $user, Collection $refs): array
    {
        if (! Schema::hasTable('original_character_relationships')) {
            return [];
        }

        $refKeys = $refs->values()->all();

        $relationships = OriginalCharacterRelationship::query()
            ->with([
                'fromCharacter',
                'toCharacter',
                'fromOfficialCharacter.work',
                'toOfficialCharacter.work',
            ])
            ->forUser($user)
            ->get();

        $blocks = [];

        foreach ($relationships as $relationship) {
            $fromRef = $this->relationshipRef($relationship, 'from');
            $toRef = $this->relationshipRef($relationship, 'to');

            if (! in_array($fromRef, $refKeys, true) || ! in_array($toRef, $refKeys, true)) {
                continue;
            }

            $lines = [
                '■ ' . $relationship->fromDisplayName() . ' → ' . $relationship->toDisplayName(),
            ];

            $this->appendIfFilled($lines, '呼び方', $relationship->called_name);
            $this->appendIfFilled($lines, '関係性', $relationship->relationship_type);
            $this->appendIfFilled($lines, '印象・気持ち', $relationship->impression);
            $this->appendIfFilled($lines, '備考', $relationship->notes);

            $blocks[] = implode("\n", $lines);
        }

        return $blocks;
    }

    private function relationshipRef(OriginalCharacterRelationship $relationship, string $direction): string
    {
        $sourceColumn = $direction . '_character_source';

        if ($relationship->{$sourceColumn} === OriginalCharacterRelationship::SOURCE_V1_CHARACTER) {
            return 'v1_character:' . $relationship->{$direction . '_character_id'};
        }

        return 'original:' . $relationship->{$direction . '_original_character_id'};
    }

    private function buildOfficialRelationshipText(array $officialIds): array
    {
        if (count($officialIds) < 2 || ! Schema::hasTable('character_relationships')) {
            return [];
        }

        $query = DB::table('character_relationships')
            ->whereIn('from_character_id', $officialIds)
            ->whereIn('to_character_id', $officialIds);

        if (Schema::hasColumn('character_relationships', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        if (Schema::hasColumn('character_relationships', 'status')) {
            $query->whereIn('status', ['published', 'active', 'draft']);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $characters = Character::query()
            ->with('work')
            ->whereIn('id', $officialIds)
            ->get()
            ->keyBy('id');

        $blocks = [];

        foreach ($rows as $row) {
            $from = $characters->get($row->from_character_id);
            $to = $characters->get($row->to_character_id);

            if (! $from || ! $to) {
                continue;
            }

            $lines = [
                '■ ' . $this->officialName($from) . ' → ' . $this->officialName($to),
            ];

            $this->appendIfFilled($lines, '呼び方', $row->called_name ?? null);
            $this->appendIfFilled($lines, '関係性', $row->relationship ?? null);
            $this->appendIfFilled($lines, '印象・気持ち', $row->impression ?? null);
            $this->appendIfFilled($lines, '備考', $row->notes ?? null);

            $blocks[] = implode("\n", $lines);
        }

        return $blocks;
    }

    private function officialName(Character $character): string
    {
        $workTitle = $character->work?->title;

        return $workTitle
            ? $workTitle . ' ＞ ' . $character->name
            : $character->name;
    }
}
