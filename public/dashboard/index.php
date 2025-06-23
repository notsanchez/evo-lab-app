<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login"); exit; }

require_once __DIR__.'/../../db.php';
$pdo    = db();
$userId = $_SESSION['user']['id'];

$latest = $pdo->prepare(
    'SELECT id, name, status, created_at
       FROM rooms
      WHERE owner_id = ?
   ORDER BY created_at DESC
      LIMIT 5'
  );
$latest->execute([$userId]);
$rooms = $latest->fetchAll();

$totalRooms = $pdo->prepare('SELECT COUNT(*) FROM rooms WHERE owner_id = ?');
$totalRooms->execute([$userId]);
$roomsCount = (int) $totalRooms->fetchColumn();

$totalWords  = 12847;
$totalDur    = '02:45:30';

$active = 'dashboard';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Dashboard — EVO</title>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
      theme: {
        fontFamily: { sans: ['Figtree', 'ui-sans-serif', 'system-ui'] }
      }
    }
</script>
</head>

<body class="min-h-screen flex text-slate-900 bg-slate-50">

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

  <h1 class="text-3xl font-bold mb-10">Dashboard</h1>

  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white border border-slate-200 p-6 rounded-xl">
      <p class="text-sm text-slate-500">Salas criadas</p>
      <p class="mt-2 text-3xl font-semibold"><?php echo $roomsCount; ?></p>
    </div>
    <div class="bg-white border border-slate-200 p-6 rounded-xl">
      <p class="text-sm text-slate-500">Palavras transcritas</p>
      <p class="mt-2 text-3xl font-semibold"><?php echo number_format($totalWords,0,',','.'); ?></p>
    </div>
    <div class="bg-white border border-slate-200 p-6 rounded-xl">
      <p class="text-sm text-slate-500">Duração total</p>
      <p class="mt-2 text-3xl font-semibold"><?php echo $totalDur; ?></p>
    </div>
    <div class="bg-white border border-slate-200 p-6 rounded-xl">
      <p class="text-sm text-slate-500">Plano</p>
      <p class="mt-2 text-xl font-semibold text-green-600">Gratuito</p>
    </div>
  </div>

  <h2 class="text-xl font-semibold mt-12 mb-4">Últimas salas</h2>
  <table class="w-full text-sm border-collapse bg-white rounded-xl overflow-hidden">
    <thead class="bg-slate-100 text-slate-600">
      <tr><th class="px-4 py-2 text-left">Sala</th><th class="px-4 py-2">Status</th><th class="px-4 py-2">Criada</th></tr>
    </thead>
    <tbody>
      <?php if(!$roomsCount): ?>
        <tr><td colspan="3" class="px-4 py-4 text-center text-slate-500">Nenhuma sala criada.</td></tr>
      <?php else:
        foreach (array_slice(array_reverse($rooms),0,5) as $r): ?>
        <tr class="border-t">
          <td class="px-4 py-2"><?php echo htmlspecialchars($r['name']); ?></td>
          <td class="px-4 py-2 text-center capitalize"><?php echo $r['status']; ?></td>
          <td class="px-4 py-2 text-center"><?php echo date('d/m/Y'); ?></td>
        </tr>
      <?php endforeach; endif; ?>
    </tbody>
  </table>

</main>
</body>
</html>
