<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Apresentação — Texto em Tempo Real</title>

<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
      theme: {
        fontFamily: { sans: ['Figtree', 'ui-sans-serif', 'system-ui'] }
      }
    }
</script>

<style>
@keyframes fade { from{opacity:0;transform:translateY(4px)} to{opacity:1;transform:translateY(0)} }
</style>
</head>
<body class="min-h-screen flex flex-col items-center bg-slate-900 text-slate-50 p-8">


<div id="box"
     class="w-full max-w-3xl bg-white/5 backdrop-blur-md p-8 md:p-10 rounded-2xl shadow-2xl">
  <div id="texto"
       class="text-[clamp(1.25rem,2.8vw+0.5rem,2.2rem)] leading-snug whitespace-pre-wrap"></div>
  <div id="temp"
       class="text-[clamp(1.25rem,2.8vw+0.5rem,2.2rem)] leading-snug whitespace-pre-wrap text-slate-400"></div>
</div>

<h1 class="text-xs mt-8 text-center">Powered by <span class="text-blue-600">EVO</span></h1>

<script>
(() => {
  const room = new URL(location).searchParams.get('room');
  if (!room) { document.body.innerHTML = '<h2 class="text-xl font-semibold text-center mt-20">Sala não informada.</h2>'; return; }

  const socket = new WebSocket("ws://localhost:8080");
  socket.onopen = () => socket.send(JSON.stringify({ join: room }));

  const texto = document.getElementById('texto');
  const temp  = document.getElementById('temp');

  socket.onmessage = (e) => {
    const data = JSON.parse(e.data);
    if (!data.text) return;

    if (data.final) {
      texto.insertAdjacentText('beforeend', (texto.textContent ? ' ' : '') + data.text);
      texto.classList.add('animate-[fade_0.25s_ease-in-out]');
      temp.textContent = '';
      setTimeout(() => texto.classList.remove('animate-[fade_0.25s_ease-in-out]'), 300);
    } else {
      temp.textContent = data.text;
    }
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
  };
})();
</script>
</body>
</html>
