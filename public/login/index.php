<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: dashboard");
    exit;
}
$err = isset($_GET['err']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Entrar — EVO</title>

<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
tailwind.config = {
  theme: { fontFamily:{ sans:['Figtree','ui-sans-serif','system-ui'] } }
}
</script>
</head>

<body class="min-h-screen flex items-center justify-center bg-slate-50">

<div class="w-full max-w-6xl bg-white/80 backdrop-blur-sm rounded-2xl shadow-xl
            flex flex-col md:flex-row overflow-hidden">

  <section class="hidden md:flex flex-col justify-center gap-8 flex-1 px-14 py-16">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M9 4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v16a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1V4Z"/></svg>
      </div>
      <h1 class="text-3xl font-extrabold">EVO Lab</h1>
    </div>

    <p class="text-lg leading-relaxed text-slate-600 max-w-md">
      Transcrição em tempo real para eventos e reuniões on-line.  
      Crie legendas acessíveis instantaneamente.
    </p>

    <ul class="space-y-5 text-slate-700">
      <li class="flex items-start gap-4">
        <span class="w-9 h-9 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
               viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </span>
        <div>
          <h3 class="font-semibold">Processamento em tempo real</h3>
          <p class="text-sm">Alta precisão e baixa latência.</p>
        </div>
      </li>
      <li class="flex items-start gap-4">
        <span class="w-9 h-9 bg-green-100 text-green-600 rounded-lg flex items-center justify-center">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
               viewBox="0 0 24 24"><path d="M17 20h5V10H2v10h5m5-6l-3 3m0 0l3 3m-3-3h12"/></svg>
        </span>
        <div>
          <h3 class="font-semibold">Acesso público</h3>
          <p class="text-sm">Compartilhe salas sem limite de espectadores.</p>
        </div>
      </li>
    </ul>
  </section>

  <section class="flex-1 p-8 sm:p-12">
    <h2 class="text-2xl font-bold text-center mb-2">Bem-vindo</h2>
    <p class="text-sm text-slate-600 text-center mb-6">
    Entre na sua conta ou crie uma nova
    </p>

    <div class="grid grid-cols-2 rounded-lg overflow-hidden border border-slate-200 mb-6">
      <button id="tabSignIn"  class="py-2 text-sm font-medium bg-slate-100">Entrar</button>
      <button id="tabSignUp"  class="py-2 text-sm font-medium bg-white border-l">Criar nova conta</button>
    </div>

    <form id="formSignIn" action="/auth.php" method="post" class="space-y-4 transition-opacity">
      <?php if ($err): ?>
        <p class="text-red-600 text-sm text-center">Credenciais inválidas</p>
      <?php endif; ?>
      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input name="user" type="text" required
               class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Senha</label>
        <input name="pass" type="password" required
               class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
      </div>
      <button type="submit"
              class="w-full py-3 rounded-lg font-semibold text-white bg-blue-600 hover:bg-blue-700">
              Entrar
      </button>
      <a href="#" class="block text-center text-xs text-blue-600 hover:underline">Esqueceu sua senha?</a>
    </form>

    <form id="formSignUp" action="/register.php" method="post" class="space-y-4 hidden transition-opacity">
        <div>
          <label class="block text-sm font-medium mb-1">Nome completo</label>
          <input name="first" required
                 class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
        </div>
      <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input name="email" type="email" required
               class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
      </div>
      <div>
        <label class="block text-sm font-medium mb-1">Senha</label>
        <input name="password" type="password" required
               class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-blue-500 outline-none">
      </div>
      <button type="submit"
              class="w-full py-3 rounded-lg font-semibold text-white bg-slate-900 hover:bg-slate-800">
              Criar conta
      </button>
    </form>
  </section>
</div>

<script>
    const tabIn  = document.getElementById('tabSignIn');
    const tabUp  = document.getElementById('tabSignUp');
    const formIn = document.getElementById('formSignIn');
    const formUp = document.getElementById('formSignUp');

    function activate(tab){
    const isIn = tab === 'in';
    tabIn.classList.toggle('bg-slate-100',  isIn);
    tabUp.classList.toggle('bg-slate-100', !isIn);
    tabUp.classList.toggle('bg-white',  isIn);
    tabIn.classList.toggle('bg-white', !isIn);
    formIn.classList.toggle('hidden', !isIn);
    formUp.classList.toggle('hidden',  isIn);
    }
    tabIn.onclick = () => activate('in');
    tabUp.onclick = () => activate('up');
</script>
</body>
</html>
