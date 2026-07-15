<?php

namespace App\Repositories;

use App\Models\Character;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CharacterRepository
{
    public function paginate(
        int $perPage = 20,
        ?int $workId = null,
        ?string $keyword = null,
        ?int $tagId = null,
        ?string $status = null,
        ?string $exactKeyword = null
    ): LengthAwarePaginator {
        return Character::query()
            ->with(['work', 'tags'])
            ->when($workId, function ($query) use ($workId) {
                $query->where('work_id', $workId);
            })
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('name', 'like', '%' . $keyword . '%')
                        ->orWhere('name_kana', 'like', '%' . $keyword . '%')
                        ->orWhere('real_name', 'like', '%' . $keyword . '%')
                        ->orWhere('aliases', 'like', '%' . $keyword . '%')
                        ->orWhere('name_english', 'like', '%' . $keyword . '%')
                        ->orWhere('gender', 'like', '%' . $keyword . '%')
                        ->orWhere('age', 'like', '%' . $keyword . '%')
                        ->orWhere('birthday', 'like', '%' . $keyword . '%')
                        ->orWhere('height', 'like', '%' . $keyword . '%')
                        ->orWhere('weight', 'like', '%' . $keyword . '%')
                        ->orWhere('blood_type', 'like', '%' . $keyword . '%')
                        ->orWhere('birthplace', 'like', '%' . $keyword . '%')
                        ->orWhere('species', 'like', '%' . $keyword . '%')
                        ->orWhere('affiliation', 'like', '%' . $keyword . '%')
                        ->orWhere('school_grade_class', 'like', '%' . $keyword . '%')
                        ->orWhere('occupation_position', 'like', '%' . $keyword . '%')
                        ->orWhere('family_structure', 'like', '%' . $keyword . '%')
                        ->orWhere('first_person', 'like', '%' . $keyword . '%')
                        ->orWhere('second_person', 'like', '%' . $keyword . '%')
                        ->orWhere('basic_tone', 'like', '%' . $keyword . '%')
                        ->orWhere('catchphrases', 'like', '%' . $keyword . '%')
                        ->orWhere('distinctive_speech', 'like', '%' . $keyword . '%')
                        ->orWhere('tone_by_relationship', 'like', '%' . $keyword . '%')
                        ->orWhere('short_quote_examples', 'like', '%' . $keyword . '%')
                        ->orWhere('personality', 'like', '%' . $keyword . '%')
                        ->orWhere('appearance', 'like', '%' . $keyword . '%')
                        ->orWhere('abilities', 'like', '%' . $keyword . '%')
                        ->orWhere('background', 'like', '%' . $keyword . '%')
                        ->orWhere('story_activities', 'like', '%' . $keyword . '%')
                        ->orWhere('source_title', 'like', '%' . $keyword . '%')
                        ->orWhere('source_url', 'like', '%' . $keyword . '%');
                });
            })
            ->when($tagId, function ($query) use ($tagId) {
                $query->whereHas('tags', function ($query) use ($tagId) {
                    $query->where('tags.id', $tagId);
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($exactKeyword, function ($query) use ($exactKeyword) {
                $query->where(function ($query) use ($exactKeyword) {
                    $query->where('name', $exactKeyword)
                        ->orWhere('name_kana', $exactKeyword)
                        ->orWhere('real_name', $exactKeyword)
                        ->orWhere('aliases', $exactKeyword)
                        ->orWhere('name_english', $exactKeyword);
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Character
    {
        return Character::create($data);
    }

    public function update(Character $character, array $data): bool
    {
        return $character->update($data);
    }

    public function delete(Character $character): bool
    {
        return $character->delete();
    }

    public function findWithWork(Character $character): Character
    {
        return $character->load([
            'work',
            'tags',
            'outgoingRelationships.toCharacter',
            'incomingRelationships.fromCharacter',
        ]);
    }

    public function syncTags(Character $character, array $tagIds): void
    {
        $character->tags()->sync($tagIds);
    }
}
