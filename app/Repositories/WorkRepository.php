<?php

namespace App\Repositories;

class WorkRepository extends BaseRepository
{
    public function findPublished(): array
    {
        return $this->fetchAll(
            "SELECT *
             FROM works
             WHERE status = 'published'
               AND deleted_at IS NULL
             ORDER BY updated_at DESC"
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne(
            "SELECT *
             FROM works
             WHERE slug = :slug
               AND status = 'published'
               AND deleted_at IS NULL
             LIMIT 1",
            [
                'slug' => $slug,
            ]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            "SELECT *
             FROM works
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1",
            [
                'id' => $id,
            ]
        );
    }
}