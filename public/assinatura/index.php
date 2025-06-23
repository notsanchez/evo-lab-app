<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login"); exit; }

require_once __DIR__.'/../../db.php';
$pdo     = db();
$userId  = $_SESSION['user']['id'];

$sub = $pdo->prepare(
  'SELECT id, price_cents, started_at, ends_at
     FROM subscriptions
    WHERE user_id = ?
 ORDER BY ends_at DESC
    LIMIT 1'
);
$sub->execute([$userId]);
$subscription = $sub->fetch();

$today = new DateTimeImmutable();
if ($subscription && new DateTimeImmutable($subscription['ends_at']) >= $today) {
    $planName     = 'Pro';
    $planPrice    = $subscription['price_cents'] / 100;
    $planEndsAt   = (new DateTimeImmutable($subscription['ends_at']))->format('d/m/Y');
    $buttonLabel  = 'Assinatura ativa';
    $buttonClass  = 'bg-green-600 cursor-not-allowed';
    $buttonAttr   = 'disabled';
} else {
    $planName    = 'Gratuito';
    $planPrice   = 0;
    $planEndsAt  = '—';
    $buttonLabel = 'Assinar plano Pro — R$ 39,90 / mês';
    $buttonClass = 'bg-blue-600 hover:bg-blue-700';
    $buttonAttr  = '';
}

$payments = [];
if ($subscription) {
    $pay = $pdo->prepare(
      'SELECT CONCAT(YEAR(paid_at),"-",LPAD(MONTH(paid_at),2,"0"))  AS ref,
              amount_cents, paid_at, status
         FROM payments
        WHERE subscription_id = ?
     ORDER BY paid_at DESC'
    );
    $pay->execute([$subscription['id']]);
    $payments = $pay->fetchAll();
}

$active = 'assinatura';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Assinatura — EVO</title>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = { theme:{ fontFamily:{ sans:['Figtree','ui-sans-serif','system-ui'] } } }
</script>
</head>

<body class="min-h-screen flex bg-slate-50 text-slate-900">

<aside class="hidden md:block w-64 bg-white border-r">
  <div class="h-16 flex items-center px-6 text-xl font-bold text-blue-600">EVO Lab</div>

  <?php
  function nav($href, $label, $icon, $active) {
      $is = $href === $active;
      $classes = $is
        ? 'bg-blue-50 text-blue-700 font-semibold'
        : 'text-slate-700 hover:bg-slate-100';
      echo <<<HTML
        <a href="{$href}" class="group flex items-center gap-3 px-6 py-3 {$classes} rounded-lg transition">
          {$icon}
          {$label}
        </a>
      HTML;
  }
  ?>

  <nav class="px-4 pt-4 space-y-1 text-sm">
    <?php
      nav('dashboard',  'Dashboard',
          '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>',
          $active);

      nav('salas',      'Salas',
          '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>',
          $active);

      nav('assinatura', 'Assinatura',
          '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24"><path d="M12 8c-3.313 0-6 2.239-6 5s2.687 5 6 5 6-2.239 6-5-2.687-5-6-5zm0-6c-1.104 0-2 .672-2 1.5S10.896 5 12 5s2-.672 2-1.5S13.104 2 12 2z"/></svg>',
          $active);

      nav('logout.php',     'Log out',
          '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1" /></svg>',
          '');
    ?>
  </nav>
</aside>

<header class="md:hidden fixed inset-x-0 top-0 h-14 bg-white shadow flex items-center justify-between px-4 z-40">
  <span class="font-bold text-blue-600">EVO</span>
  <a href="/logout.php" class="text-sm underline text-blue-600">SAIR</a>
</header>


<main class="flex-1 px-6 py-6 pt-20 md:pt-10 max-w-7xl mx-auto">

  <h1 class="text-3xl font-bold mb-8">Assinatura</h1>

    <?php if (isset($_GET['err']) && $_GET['err']==='noplan'): ?>
        <div class="mb-6 p-4 bg-yellow-100 text-yellow-800 rounded-lg">
        Você precisa assinar o plano <span class="font-bold">Pro</span> para criar salas.
        </div>
    <?php endif; ?>

  <div class="bg-white border border-slate-200 p-6 rounded-xl mb-10">
    <h2 class="text-xl font-semibold">Seu plano</h2>
    <p class="text-slate-600 mt-1 mb-6">
      Plano <span class="font-medium"><?= $planName ?></span>
      <?php if ($planName !== 'Gratuito'): ?>
        — válido até <?= $planEndsAt ?>
      <?php endif; ?>
    </p>

    <button id="btnOpenModal"
            class="px-6 py-3 rounded-lg font-semibold text-white <?= $buttonClass ?>"
            <?= $buttonAttr ?>>
      <?= $buttonLabel ?>
    </button>
    <form action="/dev_activate_subscription.php" method="post" class="mt-4">
      <button type="submit"
              class="px-6 py-3 rounded-lg font-semibold text-blue-600 border border-blue-600">
        Ativar assinatura (DEV)
      </button>
    </form>
  </div>

  <h2 class="text-xl font-semibold mb-4">Pagamentos realizados</h2>
  <table class="w-full text-sm bg-white border border-slate-200 rounded-xl overflow-hidden">
    <thead class="bg-slate-100 text-slate-600">
      <tr><th class="px-4 py-2 text-left">Referência</th><th class="px-4 py-2">Valor</th><th class="px-4 py-2">Data</th><th class="px-4 py-2">Status</th></tr>
    </thead>
    <tbody>
      <?php if (!$payments): ?>
        <tr><td colspan="4" class="px-4 py-4 text-center text-slate-500">Nenhum pagamento registrado.</td></tr>
      <?php else: foreach ($payments as $p): ?>
        <tr class="border-t">
          <td class="px-4 py-2"><?= htmlspecialchars($p['ref']) ?></td>
          <td class="px-4 py-2 text-center">R$ <?= number_format($p['amount_cents']/100,2,',','.') ?></td>
          <td class="px-4 py-2 text-center"><?= date('d/m/Y', strtotime($p['paid_at'])) ?></td>
          <td class="px-4 py-2 text-center">
            <?php
              $badge = [
                'paid'    => 'bg-green-100 text-green-700',
                'pending' => 'bg-yellow-100 text-yellow-700',
                'failed'  => 'bg-red-100 text-red-700'
              ][$p['status']];
            ?>
            <span class="px-2 py-0.5 rounded-full text-xs <?= $badge ?>">
              <?= ucfirst($p['status']) ?>
            </span>
          </td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>
</main>

<div id="modal" class="fixed inset-0 hidden flex items-center justify-center bg-black/50 z-50 p-4">
  <div class="bg-white w-full max-w-lg rounded-xl p-8 relative">
    <button id="btnClose" class="absolute top-3 right-3 text-slate-400 hover:text-slate-600">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
           viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12"/></svg>
    </button>
    <h3 class="text-xl font-semibold mb-1">Assinar plano Pro</h3>
    <p class="text-sm text-slate-500 mb-6">Escaneie o QR Code ou copie a chave Pix para efetuar o pagamento.</p>

    <div class="flex flex-col gap-6">
      <img src="https://via.placeholder.com/200x200?text=QR+Code" class="border rounded-lg" alt="QR Code">
      <button id="btnCancel"
              class="w-full px-5 py-2.5 rounded-lg border border-slate-300 hover:bg-slate-100">
              Voltar
      </button>
    </div>
  </div>
</div>

<script>
    const btnOpen = document.getElementById('btnOpenModal');
    const modal   = document.getElementById('modal');
    if (btnOpen && !btnOpen.disabled)  btnOpen.onclick = () => modal.classList.remove('hidden');
    const close = () => modal.classList.add('hidden');
    document.getElementById('btnClose').onclick  = close;
    document.getElementById('btnCancel').onclick = close;
    modal.addEventListener('click', e => { if (e.target === modal) close(); });
</script>
</body>
</html>
