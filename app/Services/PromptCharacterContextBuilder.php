<?php

namespace App\Services;

use App\Models\OriginalCharacter;
use App\Models\OriginalCharacterRelationship;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PromptCharacterContextBuilder
{
    public function build(User $user, array $refs): array
    {
        $refs = collect($refs)
            ->filter(fn ($ref) => is_string($ref) && preg_match('/^original:\d+$/', $ref))
            ->unique()
            ->values();

        if ($refs->isEmpty()) {
            return [
                'characters' => '',
                'relationships' => '',
            ];
        }

        $originalIds = $this->idsBySource($refs, 'original');
        $originalCharacters = $this->originalCharacters($user, $originalIds);

        return [
            'characters' => $this->buildCharacterText($originalCharacters),
            'relationships' => $this->buildOriginalRelationshipText($user, $refs),
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
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();
    }

    private function buildCharacterText(Collection $originalCharacters): string
    {
        $blocks = [];

        foreach ($originalCharacters as $character) {
            $blocks[] = $this->formatOriginalCharacter($character);
        }

        return implode("", array_filter($blocks));
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

        return implode("", $lines);
    }

    private function appendIfFilled(array &$lines, string $label, mixed $value): void
    {
        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        $lines[] = $label . '：' . $value;
    }

    private function buildOriginalRelationshipText(User $user, Collection $refs): string
    {
        if (! Schema::hasTable('original_character_relationships')) {
            return '';
        }

        $refKeys = $refs->values()->all();

        $relationships = OriginalCharacterRelationship::query()
            ->with(['fromCharacter', 'toCharacter'])
            ->where('user_id', $user->id)
            ->get();

        $blocks = [];

        foreach ($relationships as $relationship) {
            $fromRef = 'original:' . $relationship->from_original_character_id;
            $toRef = 'original:' . $relationship->to_original_character_id;

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

            $blocks[] = implode("", $lines);
        }

        return implode("", array_filter($blocks));
    }
}
