<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $driver = Config::get('database.default', 'mysql');

        if ($driver !== 'mysql') {
            throw new PDOException('Unsupported database driver: ' . $driver);
        }

        $host = Config::get('database.connections.mysql.host', '127.0.0.1');
        $port = Config::get('database.connections.mysql.port', '3306');
        $database = Config::get('database.connections.mysql.database', 'oshi_wiki');
        $username = Config::get('database.connections.mysql.username', 'root');
        $password = Config::get('database.connections.mysql.password', '');
        $charset = Config::get('database.connections.mysql.charset', 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

        self::$pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }
}