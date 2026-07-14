<?php

namespace App\Services;

use App\Models\Character;
use App\Models\OriginalCharacter;
use App\Models\OriginalCharacterRelationship;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class PromptCharacterContextBuilder
{
    public function build(
        User $user,
        array $refs
    ): array {
        $refs = collect($refs)
            ->filter(
                fn ($ref) =>
                    is_string($ref)
                    && preg_match(
                        '/^(original|v1):\d+$/',
                        $ref
                    )
            )
            ->unique()
            ->values();

        if ($refs->isEmpty()) {
            return [
                'characters' => '',
                'relationships' => '',
            ];
        }

        $originalIds = $this->idsBySource(
            $refs,
            'original'
        );

        $v1Ids = $this->idsBySource(
            $refs,
            'v1'
        );

        $originalCharacters = $this->originalCharacters(
            $user,
            $originalIds
        );

        $v1Characters = $this->v1Characters($v1Ids);

        return [
            'characters' => $this->buildCharacterText(
                $originalCharacters,
                $v1Characters
            ),
            'relationships' => $this->buildRelationshipText(
                $user,
                $refs
            ),
        ];
    }

    private function idsBySource(
        Collection $refs,
        string $source
    ): array {
        return $refs
            ->filter(
                fn ($ref) =>
                    str_starts_with($ref, $source . ':')
            )
            ->map(
                fn ($ref) =>
                    (int) str_replace(
                        $source . ':',
                        '',
                        $ref
                    )
            )
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function originalCharacters(
        User $user,
        array $ids
    ): Collection {
        if ($ids === []) {
            return collect();
        }

        return OriginalCharacter::query()
            ->whereIn('id', $ids)
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get();
    }

    private function v1Characters(
        array $ids
    ): Collection {
        if ($ids === []) {
            return collect();
        }

        return Character::query()
            ->with('work')
            ->whereIn('id', $ids)
            ->where('status', 'published')
            ->whereHas('work', function ($query): void {
                $query->where('status', 'published');
            })
            ->orderBy('name')
            ->get();
    }

    private function buildCharacterText(
        Collection $originalCharacters,
        Collection $v1Characters
    ): string {
        $blocks = [];

        foreach ($originalCharacters as $character) {
            $blocks[] = $this->formatOriginalCharacter(
                $character
            );
        }

        foreach ($v1Characters as $character) {
            $blocks[] = $this->formatV1Character(
                $character
            );
        }

        return implode(
            PHP_EOL . PHP_EOL,
            array_filter($blocks)
        );
    }

    private function formatOriginalCharacter(
        OriginalCharacter $character
    ): string {
        $lines = [
            '■ オリジナルキャラクター：'
                . $character->name,
        ];

        $this->appendIfFilled(
            $lines,
            '読み仮名',
            $character->name_kana
        );

        $this->appendIfFilled(
            $lines,
            '年齢',
            $character->age
        );

        $this->appendIfFilled(
            $lines,
            '性別',
            $character->gender
        );

        $this->appendIfFilled(
            $lines,
            '所属',
            $character->affiliation
        );

        $this->appendIfFilled(
            $lines,
            '学年・クラス',
            $character->school_grade
        );

        $this->appendIfFilled(
            $lines,
            '一人称',
            $character->first_person
        );

        $this->appendIfFilled(
            $lines,
            '口調',
            $character->speech_style
        );

        $this->appendIfFilled(
            $lines,
            '口調例',
            $character->speech_examples
        );

        $this->appendIfFilled(
            $lines,
            '性格・特徴',
            $character->personality
        );

        $this->appendIfFilled(
            $lines,
            '外見',
            $character->appearance
        );

        $this->appendIfFilled(
            $lines,
            '背景・経歴',
            $character->background
        );

        $this->appendIfFilled(
            $lines,
            '絶対に守りたい設定',
            $character->important_points
        );

        $this->appendIfFilled(
            $lines,
            'NG設定・避けたい表現',
            $character->ng_points
        );

        $this->appendIfFilled(
            $lines,
            '備考',
            $character->notes
        );

        return implode(PHP_EOL, $lines);
    }

    private function formatV1Character(
        Character $character
    ): string {
        $lines = [
            '■ 登録済みキャラクター：'
                . $character->name,
        ];

        $this->appendIfFilled(
            $lines,
            '原作作品',
            $character->work?->title
        );

        $this->appendIfFilled(
            $lines,
            '読み仮名',
            $character->name_kana
        );

        $this->appendIfFilled(
            $lines,
            '年齢',
            $character->age
        );

        $this->appendIfFilled(
            $lines,
            '所属',
            $character->affiliation
        );

        $this->appendIfFilled(
            $lines,
            '学年・クラス',
            $character->school_grade_class
        );

        $this->appendIfFilled(
            $lines,
            '一人称',
            $character->first_person
        );

        $this->appendIfFilled(
            $lines,
            '口調',
            $character->basic_tone
        );

        $this->appendIfFilled(
            $lines,
            '口調例',
            $character->short_quote_examples
        );

        $this->appendIfFilled(
            $lines,
            '性格・特徴',
            $character->personality
        );

        $this->appendIfFilled(
            $lines,
            '外見',
            $character->appearance
        );

        $this->appendIfFilled(
            $lines,
            '背景・経歴',
            $character->background
        );

        return implode(PHP_EOL, $lines);
    }

    private function appendIfFilled(
        array &$lines,
        string $label,
        mixed $value
    ): void {
        $value = trim((string) $value);

        if ($value === '') {
            return;
        }

        $lines[] = $label . '：' . $value;
    }

    private function buildRelationshipText(
        User $user,
        Collection $refs
    ): string {
        if (
            ! Schema::hasTable(
                'original_character_relationships'
            )
        ) {
            return '';
        }

        $refKeys = $refs->values()->all();

        $relationships =
            OriginalCharacterRelationship::query()
                ->with([
                    'fromCharacter',
                    'toCharacter',
                    'fromV1Character.work',
                    'toV1Character.work',
                ])
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->get();

        $blocks = [];

        foreach ($relationships as $relationship) {
            $fromRef = $relationship->fromReference();
            $toRef = $relationship->toReference();

            if (
                ! $fromRef
                || ! $toRef
                || ! in_array($fromRef, $refKeys, true)
                || ! in_array($toRef, $refKeys, true)
            ) {
                continue;
            }

            if (
                $relationship->from_character_source
                    === OriginalCharacterRelationship::SOURCE_V1
                && ! $this->isPublishedV1Character(
                    $relationship->fromV1Character
                )
            ) {
                continue;
            }

            if (
                $relationship->to_character_source
                    === OriginalCharacterRelationship::SOURCE_V1
                && ! $this->isPublishedV1Character(
                    $relationship->toV1Character
                )
            ) {
                continue;
            }

            $lines = [
                '■ '
                    . $relationship->fromDisplayName()
                    . ' → '
                    . $relationship->toDisplayName(),
            ];

            $this->appendIfFilled(
                $lines,
                '呼び方',
                $relationship->called_name
            );

            $this->appendIfFilled(
                $lines,
                '関係性',
                $relationship->relationship_type
            );

            $this->appendIfFilled(
                $lines,
                '印象・気持ち',
                $relationship->impression
            );

            $this->appendIfFilled(
                $lines,
                '備考',
                $relationship->notes
            );

            $blocks[] = implode(PHP_EOL, $lines);
        }

        return implode(
            PHP_EOL . PHP_EOL,
            array_filter($blocks)
        );
    }

    private function isPublishedV1Character(
        ?Character $character
    ): bool {
        return $character !== null
            && $character->status === 'published'
            && $character->work !== null
            && $character->work->status === 'published';
    }
}
