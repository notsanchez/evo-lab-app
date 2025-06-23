<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: login'); exit; }

require_once __DIR__.'/../db.php';
$pdo = db();

$name = trim($_POST['name'] ?? '');
$desc = trim($_POST['desc'] ?? '');
if ($name === '') { header('Location: salas'); exit; }

$roomId = substr(bin2hex(random_bytes(4)), 0, 8);
$userId = $_SESSION['user']['id'];

function hasActiveSubscription(PDO $pdo, int $userId): bool
{
    $stmt = $pdo->prepare(
        'SELECT 1
           FROM subscriptions
          WHERE user_id = ?
            AND ends_at >= CURRENT_DATE()
          LIMIT 1'
    );
    $stmt->execute([$userId]);
    return (bool) $stmt->fetchColumn();
}

if (!hasActiveSubscription($pdo, $userId)) {
    header('Location: /assinatura?err=noplan');
    exit;
}

$stmt = $pdo->prepare(
  'INSERT INTO rooms (id, owner_id, name, description, status)
         VALUES (?,?,?,?, "inactive")'
);
$stmt->execute([$roomId, $userId, $name, $desc]);

header("Location: room?room={$roomId}");
exit;
