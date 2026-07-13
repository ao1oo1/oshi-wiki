<?php

namespace App\Services;

use App\Models\OriginalCharacter;
use App\Models\User;
use App\Repositories\OriginalCharacterRepository;
use App\Support\WritingAssistLimits;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OriginalCharacterService
{
    public function __construct(
        private readonly OriginalCharacterRepository $repository
    ) {
    }

    public function paginateForUser(User $user)
    {
        return $this->repository->paginateForUser($user);
    }

    public function countForUser(User $user): int
    {
        return $this->repository->countForUser($user);
    }

    public function createForUser(
        User $user,
        array $data,
        ?UploadedFile $image = null
    ): OriginalCharacter {
        $limit = WritingAssistLimits::originalCharactersPerUser($user);

        if ($limit !== null && $this->repository->countForUser($user) >= $limit) {
            throw ValidationException::withMessages([
                'limit' => "オリジナルキャラクターは最大{$limit}件まで登録できます。",
            ]);
        }

        unset($data['character_image'], $data['remove_image']);

        $data['user_id'] = $user->id;
        $data['status'] = $data['status'] ?? 'active';
        $data['is_main_character'] = (bool) ($data['is_main_character'] ?? false);

        if ($image) {
            $imageData = $this->storeImage($user, $image);
            $data = array_merge($data, $imageData);
        }

        try {
            return $this->repository->create($data);
        } catch (\Throwable $exception) {
            if (! empty($data['image_path'])) {
                Storage::disk('local')->delete($data['image_path']);
            }

            throw $exception;
        }
    }

    public function update(
        OriginalCharacter $originalCharacter,
        array $data,
        ?UploadedFile $image = null,
        bool $removeImage = false
    ): bool {
        unset($data['character_image'], $data['remove_image']);

        $data['is_main_character'] = (bool) ($data['is_main_character'] ?? false);

        $oldImagePath = $originalCharacter->image_path;
        $newImagePath = null;

        if ($image) {
            $imageData = $this->storeImage($originalCharacter->user, $image);
            $data = array_merge($data, $imageData);
            $newImagePath = $imageData['image_path'];
        } elseif ($removeImage) {
            $data['image_path'] = null;
            $data['image_original_name'] = null;
        }

        try {
            $updated = $this->repository->update($originalCharacter, $data);
        } catch (\Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('local')->delete($newImagePath);
            }

            throw $exception;
        }

        if (
            $updated
            && $oldImagePath
            && ($image || $removeImage)
            && $oldImagePath !== $newImagePath
        ) {
            Storage::disk('local')->delete($oldImagePath);
        }

        return $updated;
    }

    public function delete(OriginalCharacter $originalCharacter): bool
    {
        $imagePath = $originalCharacter->image_path;
        $deleted = $this->repository->delete($originalCharacter);

        if ($deleted && $imagePath) {
            Storage::disk('local')->delete($imagePath);
        }

        return $deleted;
    }

    private function storeImage(User $user, UploadedFile $image): array
    {
        $extension = strtolower(
            $image->getClientOriginalExtension()
            ?: $image->extension()
            ?: 'jpg'
        );

        $filename = Str::uuid()->toString() . '.' . $extension;
        $directory = 'private/original-character-images/' . $user->id;

        $path = $image->storeAs($directory, $filename, 'local');

        return [
            'image_path' => $path,
            'image_original_name' => $image->getClientOriginalName(),
        ];
    }
}
