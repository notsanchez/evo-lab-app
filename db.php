<?php
function db(): PDO
{
    static $pdo;
    if (!$pdo) {
        $dsn  = 'mysql:host=infra.brellecare.com.br;port=3308;dbname=evo-lab;charset=utf8mb4';
        $user = 'mysql';
        $pass = 'e1a435a3b5046c2714c0';

        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}