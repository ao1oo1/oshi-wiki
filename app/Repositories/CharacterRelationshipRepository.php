<?php

namespace App\Repositories;

use App\Models\CharacterRelationship;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CharacterRelationshipRepository
{
    public function paginate(int $perPage = 20, ?int $workId = null, ?string $keyword = null, ?string $status = null, ?string $exactKeyword = null): LengthAwarePaginator
    {
        return CharacterRelationship::query()
            ->with(['work', 'fromCharacter', 'toCharacter'])
            ->when($workId, function ($query) use ($workId) {
                $query->where('work_id', $workId);
            })
            ->when($keyword, function ($query) use ($keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->where('called_name', 'like', '%' . $keyword . '%')
                        ->orWhere('relationship', 'like', '%' . $keyword . '%')
                        ->orWhere('impression', 'like', '%' . $keyword . '%')
                        ->orWhere('notes', 'like', '%' . $keyword . '%')
                        ->orWhereHas('fromCharacter', function ($query) use ($keyword) {
                            $query->where('name', 'like', '%' . $keyword . '%');
                        })
                        ->orWhereHas('toCharacter', function ($query) use ($keyword) {
                            $query->where('name', 'like', '%' . $keyword . '%');
                        });
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($exactKeyword, function ($query) use ($exactKeyword) {
                $query->where(function ($query) use ($exactKeyword) {
                    $query->where('called_name', $exactKeyword)
                        ->orWhere('relationship', $exactKeyword)
                        ->orWhere('impression', $exactKeyword)
                        ->orWhereHas('fromCharacter', fn ($query) => $query->where('name', $exactKeyword))
                        ->orWhereHas('toCharacter', fn ($query) => $query->where('name', $exactKeyword));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): CharacterRelationship
    {
        return CharacterRelationship::create($data);
    }

    public function update(CharacterRelationship $characterRelationship, array $data): bool
    {
        return $characterRelationship->update($data);
    }

    public function delete(CharacterRelationship $characterRelationship): bool
    {
        return $characterRelationship->delete();
    }
}
