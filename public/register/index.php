<?php
session_start();
require_once __DIR__.'/../../db.php';

$name  = trim($_POST['first'] ?? '');
$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 4) {
    header('Location: login?err=1'); exit;
}

try {
    $pdo = db();

    /* 1. e-mail já existe? */
    $q = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $q->execute([$email]);
    if ($q->fetch()) {
        header('Location: login?err=2'); exit;
    }

    /* 2. cria o usuário */
    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash) VALUES (?,?,?)'
    );
    $stmt->execute([
        $name,
        $email,
        password_hash($pass, PASSWORD_DEFAULT)
    ]);

    /* 3. loga automaticamente */
    $_SESSION['user'] = [
        'id'    => $pdo->lastInsertId(),
        'name'  => $name,
        'email' => $email
    ];
    header('Location: dashboard');
} catch (PDOException $e) {
    /* exiba apenas em ambiente de DEV! */
    echo "<pre>Erro PDO: " . $e->getMessage() . "</pre>";
    exit;
}
