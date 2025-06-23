<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login"); exit; }
require_once __DIR__.'/../../db.php';
$pdo    = db();
$userId = $_SESSION['user']['id'];

$id    = $_GET['room'] ?? '';

$latest = $pdo->prepare(
    "SELECT id,
               name,
               description AS `desc`,
               status,
               created_at
        FROM rooms
        WHERE owner_id = ?
        ORDER BY created_at DESC"
  );
$latest->execute([$userId]);
$rooms = $latest->fetchAll();

$room  = null;
foreach ($rooms as $r) if ($r['id'] === $id) $room = $r;

if (!$room) { header("Location: salas"); exit; }
$active = 'salas';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Room — <?php echo htmlspecialchars($room['name']); ?></title>
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
  function nav($href,$label,$icon,$active){
      $is=$href===$active;
      $cls=$is?'bg-blue-50 text-blue-700 font-semibold':'text-slate-700 hover:bg-slate-100';
      echo <<<HTML
      <a href="{$href}" class="group flex items-center gap-3 px-6 py-3 {$cls} rounded-lg transition">
        {$icon}{$label}
      </a>
    HTML;
  }
  ?>
  <nav class="px-4 pt-4 space-y-1 text-sm">
    <?php
      nav('dashboard','Dashboard',
          '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>',
          $active);
      nav('salas','Salas',
          '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24"><path d="M8 7V3m8 4V3m-9 8h10M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>',
          $active);
      nav('assinatura','Assinatura',
          '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24"><path d="M12 8c-3.313 0-6 2.239-6 5s2.687 5 6 5 6-2.239 6-5-2.687-5-6-5zm0-6c-1.104 0-2 .672-2 1.5S10.896 5 12 5s2-.672 2-1.5S13.104 2 12 2z"/></svg>',
          $active);
      nav('logout.php','Log out',
          '<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                viewBox="0 0 24 24"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/></svg>',
          '');
    ?>
  </nav>
</aside>

<header class="md:hidden fixed inset-x-0 top-0 h-14 bg-white shadow flex
               items-center justify-between px-4 z-40">
  <a href="dashboard" class="font-bold text-blue-600">EVO</a>
  <a href="/logout.php"   class="text-sm underline text-blue-600">SAIR</a>
</header>

<main class="flex-1 px-6 py-6 pt-20 md:pt-10 max-w-7xl mx-auto">

    <div class="flex items-center justify-between mb-8">
    <a href="salas" class="text-blue-600 flex items-center gap-1 hover:text-blue-700">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
           viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
      Voltar às salas
    </a>

  </div>

    <?php
        $publicUrl = "https://{$_SERVER['HTTP_HOST']}/apresentacao?room={$room['id']}";
    ?>

  <section class="grid lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2 bg-slate-50 border border-slate-200 p-8 rounded-xl space-y-8 relative">
    <h1 class="text-xl"><?php echo htmlspecialchars($room['name']); ?></h1>  
    <span class="absolute top-6 right-6 inline-flex items-center gap-1 px-3 py-0.5 rounded-full bg-slate-200 text-xs">

        <span class="w-2 h-2 rounded-full bg-slate-400"></span> INATIVO
      </span>

      <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-4">
        <?php
        $stats = [
          ['Espectadores','0 ativos'],
          ['Duração','0:00:00'],
        ];
        foreach ($stats as $s): ?>
          <div class="flex flex-col items-center justify-center h-24 bg-white border border-slate-200 rounded-lg text-sm">
            <span class="font-medium"><?php echo $s[0]; ?></span>
            <span class="text-slate-500 text-xs"><?php echo $s[1]; ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <button
            id="btnStart"
            class="w-full mt-4 py-3 rounded-lg font-semibold text-white bg-green-600 hover:bg-green-700 transition">
        Iniciar transcrição
      </button>

    </div>

    <div class="bg-slate-50 border border-slate-200 p-8 rounded-xl space-y-4">
      <h3 class="font-semibold">Acesso Público</h3>
      <div class="flex gap-2">
        <input readonly value="https://<?php echo $_SERVER['HTTP_HOST']; ?>/apresentacao?room=<?php echo $room['id']; ?>"
               class="flex-1 px-3 py-2 rounded-lg border border-slate-300 text-sm bg-white">
      </div>
      <a href="../apresentacao?room=<?php echo $room['id']; ?>" target="_blank"
         class="w-full flex justify-center py-2.5 rounded-lg font-semibold bg-blue-600 text-white hover:bg-blue-700 text-sm">
         Abrir pagina
      </a>
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($publicUrl) ?>"
       alt="QR Code da sala" class="mx-auto mt-2 border rounded-lg">
    </div>
  </section>

  <section class="grid lg:grid-cols-3 gap-8 mt-8">
    <div class="lg:col-span-2 bg-slate-50 border border-slate-200 p-8 rounded-xl">
      <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold">Configurações da sala</h3>
      </div>
      <dl class="divide-y divide-slate-200 text-sm">
        <div class="py-3 flex justify-between"><dt class="text-blue-600">Nome da sala</dt><dd><?php echo htmlspecialchars($room['name']); ?></dd></div>
        <div class="py-3 flex justify-between"><dt class="text-blue-600">Descrição</dt><dd><?php echo htmlspecialchars($room['desc'] ?: '—'); ?></dd></div>
        <div class="py-3 flex justify-between"><dt class="text-blue-600">Criada em</dt><dd><?php echo date('Y-m-d'); ?></dd></div>
      </dl>
    </div>

    <div class="bg-slate-50 border border-slate-200 p-8 rounded-xl">
      <h3 class="font-semibold mb-4">Status</h3>
      <dl class="space-y-2 text-sm">
        <div class="flex justify-between"><dt>Total de sessões</dt><dd>0</dd></div>
        <div class="flex justify-between"><dt>Duração total</dt><dd>0:00:00</dd></div>
        <div class="flex justify-between"><dt>Pico de espectadores</dt><dd>0</dd></div>
      </dl>
    </div>
  </section>
</main>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const room = "<?php echo $room['id']; ?>";

  const btn   = document.getElementById('btnStart');
  const stat  = document.getElementById('status');

  const socket = new WebSocket("wss://evo-lab-evo-ws.gn1cmm.easypanel.host");
  socket.addEventListener('open', () => socket.send(JSON.stringify({ join: room })));

  const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (!SR) { alert('Browser sem Web Speech API'); btn.disabled = true; return; }

  const rec = new SR();
  rec.lang = 'pt-BR';
  rec.interimResults = true;
  rec.continuous = true;

  let isRecording = false;

  btn.addEventListener('click', () => {
    if (!isRecording) {
      if (socket.readyState !== 1) { alert('WebSocket conectando…'); return; }
      rec.start();
      isRecording = true;
      btn.textContent = 'Parar transcrição';
      btn.classList.replace('bg-green-600', 'bg-red-600');
      btn.classList.replace('hover:bg-green-700', 'hover:bg-red-700');
      stat && (stat.textContent = 'Capturando áudio — fale à vontade');
    } else {
      rec.stop();
      finish();
    }
  });

  rec.addEventListener('result', e => {
    for (let i = e.resultIndex; i < e.results.length; i++) {
      const r = e.results[i];
      const text = r[0].transcript.trim();
      if (text && socket.readyState === 1) {
        socket.send(JSON.stringify({ text, final: r.isFinal }));
      }
    }
  });

  rec.addEventListener('end', () => {
    if (isRecording) rec.start();
  });

  function finish() {
    isRecording = false;
    btn.textContent = 'Iniciar transcrição';
    btn.classList.replace('bg-red-600', 'bg-green-600');
    btn.classList.replace('hover:bg-red-700', 'hover:bg-green-700');
    stat && (stat.textContent = 'Captura encerrada');
  }

});
</script>
</body>
</html>
