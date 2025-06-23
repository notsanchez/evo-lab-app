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
   ORDER BY created_at DESC'
  );
$latest->execute([$userId]);
$rooms = $latest->fetchAll();
$active  = 'salas';
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

<body class="text-slate-900 bg-slate-50 min-h-screen flex">

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
  <span class="text-lg font-bold text-blue-600">EVO</span>
  <a href="/logout.php" class="text-sm underline text-blue-600">SAIR</a>
</header>


<main class="flex-1 px-6 py-6 pt-20 md:pt-10 max-w-7xl mx-auto">
  <h1 class="text-3xl font-bold">Suas salas</h1>
  <p class="text-slate-600 mt-1 mb-8">Crie e gerencie sessões de transcrição ao vivo para seus eventos e reuniões.</p>

  <div class="flex flex-col sm:flex-row sm:items-center gap-4">
  <button id="btnOpenModal"
            class="flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg font-semibold
                   text-white bg-blue-600 hover:bg-blue-700">
      +
      Criar sala
    </button>  
  <div class="relative flex-1">
      <span class="absolute inset-y-0 left-3 flex items-center text-slate-400">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
             viewBox="0 0 24 24"><path d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/></svg>
      </span>
      <input id="search" type="text" placeholder="Buscar sala..."
             class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300
                    focus:ring-2 focus:ring-blue-500 outline-none bg-white">
    </div>

    
  </div>

  <div id="grid" class="grid mt-10 gap-6 sm:grid-cols-2 xl:grid-cols-3">
    <?php foreach ($rooms as $r): ?>
      <?php
        $badge = [
          'active'    => ['text'=>'Ativo','clr'=>'bg-green-100 text-green-700'],
          'inactive'  => ['text'=>'Inativo','clr'=>'bg-slate-200 text-slate-700'],
          'scheduled' => ['text'=>'Agendado','clr'=>'bg-blue-100 text-blue-700']
        ][$r['status']];
      ?>
      <div class="bg-white border border-slate-200 rounded-xl p-5 flex flex-col justify-between">
        <div>
          <div class="flex justify-between mb-2">
            <h2 class="text-lg font-semibold"><?php echo htmlspecialchars($r['name']); ?></h2>
            <button class="text-slate-400 hover:text-slate-600">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 13a1 1 0 100-2 1 1 0 000 2zm0 5a1 1 0 100-2 1 1 0 000 2zm0-10a1 1 0 100-2 1 1 0 000 2z"/>
              </svg>
            </button>
          </div>

          <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs <?php echo $badge['clr']; ?>">
            <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
            <?php echo $badge['text']; ?>
          </span>

          <div class="flex items-center text-sm text-slate-500 gap-4 mt-4">
            <span class="flex items-center gap-1">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                   viewBox="0 0 24 24"><path d="M17 20h5V10H2v10h5m5-6l-3 3m0 0l3 3m-3-3h12"/></svg>
              0 espectadores
            </span>
          </div>
        </div>

        <a href="/room?room=<?php echo $r['id']; ?>"
           class="mt-6 flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg font-semibold
                  bg-blue-600 text-white hover:bg-blue-700">
           <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
           Gerenciar
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</main>

<div id="modal" class="fixed inset-0 hidden flex items-center justify-center bg-zinc-900/50 z-50">
  <div class="bg-white w-full max-w-lg rounded-xl p-8 relative">
    <button id="btnClose" class="absolute top-3 right-3 text-slate-400 hover:text-slate-600">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
           viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    <div class="mb-6">
      <h3 class="text-xl font-semibold">Criar sala de transcrição</h3>
      <p class="text-sm text-slate-500">Crie uma nova sala para transcrição ao vivo</p>
    </div>

    <form action="create_room.php" method="post" class="space-y-6">
      <div>
        <label class="block text-sm font-medium mb-1">Nome da sala <span class="text-red-600">*</span></label>
        <input name="name" required
               class="w-full px-4 py-3 rounded-lg border border-slate-300
                      focus:ring-2 focus:ring-blue-500 outline-none">
      </div>

      <div>
        <label class="block text-sm font-medium mb-1">Descrição (Opcional)</label>
        <textarea name="desc" rows="3"
                  class="w-full px-4 py-3 rounded-lg border border-slate-300
                         focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
      </div>

      <div class="flex justify-end gap-3">
        <button type="button" id="btnCancel"
                class="px-4 py-2 rounded-lg border border-slate-300 hover:bg-slate-100">Voltar</button>
        <button type="submit"
                class="px-5 py-2.5 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700">Criar sala</button>
      </div>
    </form>
  </div>
</div>

<script>
    const modal = document.getElementById('modal');
    document.getElementById('btnOpenModal').onclick = () => modal.classList.remove('hidden');
    const close = () => modal.classList.add('hidden');
    document.getElementById('btnClose').onclick = close;
    document.getElementById('btnCancel').onclick = close;
    modal.addEventListener('click', e => { if (e.target === modal) close(); });

    const search = document.getElementById('search');
    search.addEventListener('input', () => {
    const term = search.value.toLowerCase();
    document.querySelectorAll('#grid > div').forEach(card => {
        card.style.display = card.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
    });
</script>
</body>
</html>
