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
        ?int $tagId = null
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
                        ->orWhere('age', 'like', '%' . $keyword . '%')
                        ->orWhere('affiliation', 'like', '%' . $keyword . '%')
                        ->orWhere('grade_class', 'like', '%' . $keyword . '%')
                        ->orWhere('first_person', 'like', '%' . $keyword . '%')
                        ->orWhere('tone', 'like', '%' . $keyword . '%')
                        ->orWhere('tone_examples', 'like', '%' . $keyword . '%')
                        ->orWhere('personality', 'like', '%' . $keyword . '%')
                        ->orWhere('appearance', 'like', '%' . $keyword . '%')
                        ->orWhere('background', 'like', '%' . $keyword . '%');
                });
            })
            ->when($tagId, function ($query) use ($tagId) {
                $query->whereHas('tags', function ($query) use ($tagId) {
                    $query->where('tags.id', $tagId);
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
