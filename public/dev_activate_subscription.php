<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: /login'); exit; }

$devHosts = ['localhost','127.0.0.1'];
if (!in_array($_SERVER['SERVER_NAME'], $devHosts) &&
    !str_contains($_SERVER['HTTP_HOST'], '.local')) {
    http_response_code(403);
    exit('Forbidden');
}

require_once __DIR__.'/../db.php';
$pdo = db();

$userId = $_SESSION['user']['id'];
$today  = new DateTimeImmutable();
$ends   = $today->add(new DateInterval('P30D'))->format('Y-m-d');

$pdo->prepare('DELETE FROM subscriptions WHERE user_id = ?')->execute([$userId]);

$pdo->prepare(
  'INSERT INTO subscriptions (user_id, price_cents, started_at, ends_at)
         VALUES (?,?,?,?)'
)->execute([$userId, 0, $today->format('Y-m-d'), $ends]);

header('Location: /assinatura');
