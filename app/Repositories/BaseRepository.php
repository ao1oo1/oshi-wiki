<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

abstract class BaseRepository
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch();

        return $result === false ? null : $result;
    }

    protected function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute($params);
    }

    protected function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}