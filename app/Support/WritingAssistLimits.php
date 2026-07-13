<?php

namespace App\Support;

use App\Models\User;

class WritingAssistLimits
{
    public static function isUnlimited(?User $user): bool
    {
        return (bool) $user?->isSuperAdmin();
    }

    public static function originalCharactersPerUser(?User $user = null): ?int
    {
        if (self::isUnlimited($user)) {
            return null;
        }

        return (int) config('writing_assist.limits.original_characters_per_user', 30);
    }

    public static function relationshipsPerUser(?User $user = null): ?int
    {
        if (self::isUnlimited($user)) {
            return null;
        }

        return (int) config('writing_assist.limits.relationships_per_user', 100);
    }

    public static function promptsPerUser(?User $user = null): ?int
    {
        if (self::isUnlimited($user)) {
            return null;
        }

        return (int) config('writing_assist.limits.prompts_per_user', 50);
    }

    public static function storiesPerUser(?User $user = null): ?int
    {
        if (self::isUnlimited($user)) {
            return null;
        }

        return (int) config(
            'writing_assist.limits.stories_per_user',
            10
        );
    }

    public static function storyBodyMaxLength(?User $user = null): ?int
    {
        if (self::isUnlimited($user)) {
            return null;
        }

        return (int) config(
            'writing_assist.limits.story_body_max_length',
            100000
        );
    }

    public static function promptBodyMaxLength(?User $user = null): ?int
    {
        if (self::isUnlimited($user)) {
            return null;
        }

        return (int) config('writing_assist.limits.prompt_body_max_length', 20000);
    }

    public static function synopsisMaxLength(?User $user = null): ?int
    {
        if (self::isUnlimited($user)) {
            return null;
        }

        return (int) config('writing_assist.limits.synopsis_max_length', 5000);
    }

    public static function noteMaxLength(?User $user = null): ?int
    {
        if (self::isUnlimited($user)) {
            return null;
        }

        return (int) config('writing_assist.limits.note_max_length', 2000);
    }

    public static function longNoteMaxLength(?User $user = null): ?int
    {
        if (self::isUnlimited($user)) {
            return null;
        }

        return (int) config('writing_assist.limits.long_note_max_length', 5000);
    }

    public static function all(?User $user = null): array
    {
        return [
            'is_unlimited' => self::isUnlimited($user),
            'original_characters_per_user' => self::originalCharactersPerUser($user),
            'relationships_per_user' => self::relationshipsPerUser($user),
            'prompts_per_user' => self::promptsPerUser($user),
            'stories_per_user' => self::storiesPerUser($user),
            'story_body_max_length' => self::storyBodyMaxLength($user),
            'prompt_body_max_length' => self::promptBodyMaxLength($user),
            'synopsis_max_length' => self::synopsisMaxLength($user),
            'note_max_length' => self::noteMaxLength($user),
            'long_note_max_length' => self::longNoteMaxLength($user),
        ];
    }

    public static function labelFor(?int $limit): string
    {
        return $limit === null ? '制限なし' : number_format($limit);
    }
}
