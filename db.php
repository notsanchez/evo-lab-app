<?php
require_once __DIR__ . '/../vendor/autoload.php';

function env(string $key, string $default = ''): string
{
    return $_ENV[$key]          ??
           $_SERVER[$key]       ??
           getenv($key)         ??
           $default;
}

function db(): PDO
{
    static $pdo;
    if (!$pdo) {
        if (file_exists(__DIR__ . '/.env')) {
            (Dotenv\Dotenv::createImmutable(__DIR__))->load();
        }

        $host = env('DB_HOST', 'localhost');
        $port = env('DB_PORT', '3306');
        $name = env('DB_NAME', 'test');
        $user = env('DB_USER', 'root');
        $pass = env('DB_PASS', '');

        $dsn  = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
