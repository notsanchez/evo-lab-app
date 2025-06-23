<?php
session_start();
if (!isset($_SESSION['user'])) { header('Location: /login'); exit; }

require_once __DIR__.'/../db.php';
$pdo = db();

$userId = $_SESSION['user']['id'];
$today  = new DateTimeImmutable();
$ends   = $today->add(new DateInterval('P30D'))->format('Y-m-d');

$pdo->prepare(
    'INSERT INTO subscriptions (user_id, price_cents, started_at, ends_at)
           VALUES (?,?,?,?)'
)->execute([$userId, 0, $today->format('Y-m-d'), $ends]);

$subId = $pdo->lastInsertId();


$pdo->prepare(
    'INSERT INTO payments
            (subscription_id, amount_cents, method, pix_qr_id, status, paid_at, created_at)
     VALUES (?,               ?,            ?,      ?,         ?,      ?,       ?)'
)->execute([
    $subId,
    0,
    'pix',
    null,
    'paid',
    $today->format('Y-m-d'),
    $today->format('Y-m-d'),
]);

header('Location: /assinatura');
