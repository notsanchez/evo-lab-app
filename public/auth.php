<?php
session_start();
require_once __DIR__.'/../db.php';

$email = $_POST['user'] ?? '';
$pass  = $_POST['pass'] ?? '';

$pdo = db();
$stmt = $pdo->prepare('SELECT id,name,email,password_hash FROM users WHERE email = ? AND is_active = 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($pass, $user['password_hash'])) {
    $pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')
        ->execute([$user['id']]);

    $_SESSION['user'] = [
        'id'    => $user['id'],
        'name'  => $user['name'],
        'email' => $user['email']
    ];
    header('Location: dashboard');
} else {
    header('Location: login?err=1');
}
