<?php
session_start();
if (!isset($_SESSION['user'])) { header("Location: login"); exit; }
require_once __DIR__.'/../../db.php';
$pdo    = db();

$id    = $_GET['room'] ?? '';

$latest = $pdo->prepare(
    "SELECT id,
               name,
               description AS `desc`,
               status,
               created_at
        FROM rooms
        WHERE id = ?
        ORDER BY created_at DESC"
  );
$latest->execute([$id]);
$rooms = $latest->fetchAll();

$room  = null;
foreach ($rooms as $r) if ($r['id'] === $id) $room = $r;

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Apresentação — Texto em Tempo Real</title>
  <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        fontFamily: {
          sans: ['Figtree', 'ui-sans-serif', 'system-ui']
        },
        extend: {
          keyframes: {
            fade: {
              '0%': { opacity: '0', transform: 'translateY(4px)' },
              '100%': { opacity: '1', transform: 'translateY(0)' }
            }
          },
          animation: {
            fade: 'fade 0.25s ease-in-out'
          }
        }
      }
    }
  </script>
</head>

<body class="min-h-dvh flex flex-col items-center bg-slate-900 text-slate-50 p-4 sm:p-8">
   <h1 class="my-4 text-2xl"><?php echo $room['name']; ?></h1>
  <div id="box" class="w-full max-w-4xl bg-white/5 backdrop-blur-md p-4 sm:p-8 md:p-10 rounded-2xl shadow-2xl">
    <div id="texto"
         class="whitespace-pre-wrap leading-relaxed sm:leading-snug text-[clamp(1.25rem,5vw,2.25rem)]"
         role="log" aria-live="polite"></div>
    <div id="temp" class="leading-relaxed sm:leading-snug text-slate-400 text-[clamp(1.25rem,5vw,2.25rem)]"></div>
  </div>

  <h1 class="text-xs mt-6 text-center">Powered by <span class="text-blue-600">EVO</span></h1>

  <script>
    (() => {
      const room = new URL(location).searchParams.get('room');
      if (!room) {
        document.body.innerHTML = '<h2 class="text-xl font-semibold text-center mt-20">Sala não informada.</h2>';
        return;
      }

  const socket = new WebSocket("wss://evo-lab-evo-ws.gn1cmm.easypanel.host");
  socket.onopen = () => socket.send(JSON.stringify({ join: room }));

      const texto = document.getElementById('texto');
      const temp  = document.getElementById('temp');

      const PAUSE_MS = 1200;               
      let lastFinalTime = Date.now();

      socket.addEventListener('message', (e) => {
        const data = JSON.parse(e.data);
        if (!data.text) return;

        const now       = Date.now();
        const longPause = now - lastFinalTime >= PAUSE_MS;

        if (data.final) {
          lastFinalTime = now;

          const p = document.createElement('p');
          p.textContent = data.text;
          p.className = 'animate-fade';
          texto.appendChild(p);

          if (longPause) {
            const br = document.createElement('br');
            texto.appendChild(br);
          }

          temp.textContent = '';
        } else {
          temp.textContent = data.text;
        }

        window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
      });
    })();
  </script>
</body>
</html>
