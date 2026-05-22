<?php
session_start();
require_once 'db.php';

// Se já está logado, redireciona
if (usuario_logado()) {
    header('Location: dashboard.php');
    exit;
}

$erro   = '';
$sucesso= '';
$campos = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $senha    = $_POST['senha']         ?? '';
    $confirma = $_POST['confirma']      ?? '';
    $termos   = isset($_POST['termos']);

    $campos = compact('nome', 'email', 'telefone');

    if (!$nome || !$email || !$senha || !$confirma) {
        $erro = 'Preencha todos os campos obrigatórios.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirma) {
        $erro = 'As senhas não coincidem.';
    } elseif (!$termos) {
        $erro = 'Você precisa aceitar os termos de uso.';
    } else {
        $resultado = usuario_cadastrar($nome, $email, $senha, $telefone);
        if ($resultado['ok']) {
            // Loga automaticamente após cadastro
            usuario_login($email, $senha);
            header('Location: dashboard.php');
            exit;
        } else {
            $erro = $resultado['erro'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Criar conta — Temakeria da Bia</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@300;400;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --vermelho: #c8102e;
    --vermelho-escuro: #9b0b22;
    --vermelho-hover: #e63950;
    --preto: #0f0f0f;
    --branco: #ffffff;
    --cinza-bg: #f7f7f7;
    --cinza-texto: #555555;
    --borda: rgba(0,0,0,0.1);
    --sucesso: #1a7a45;
  }

  html, body { height: 100%; font-family: 'DM Sans', sans-serif; background: var(--branco); color: var(--preto); }

  .bg {
    position: fixed; inset: 0; z-index: 0;
    background:
      radial-gradient(ellipse 60% 50% at 10% 20%, rgba(200,16,46,.05) 0%, transparent 55%),
      radial-gradient(ellipse 70% 60% at 90% 80%, rgba(200,16,46,.04) 0%, transparent 55%),
      var(--branco);
  }

  .kanji-deco {
    position: fixed; font-family: 'Noto Serif JP', serif;
    font-weight: 300; color: rgba(200,16,46,0.04);
    user-select: none; pointer-events: none; z-index: 0;
  }
  .kanji-deco.k1 { font-size: 20vw; top: -2%; left: -2%; }
  .kanji-deco.k2 { font-size: 10vw; bottom: 5%; right: 3%; }

  .page {
    position: relative; z-index: 1;
    min-height: 100vh;
    display: flex; align-items: flex-start; justify-content: center;
    padding: 3rem 1.5rem;
  }

  .container {
    width: 100%; max-width: 960px;
    display: grid;
    grid-template-columns: 1fr 1.6fr;
    gap: 3rem;
    align-items: start;
  }

  /* ── SIDEBAR ── */
  .brand { display: flex; align-items: center; gap: .75rem; text-decoration: none; margin-bottom: 3rem; }
  .brand-icon {
    width: 44px; height: 44px; background: var(--vermelho);
    border-radius: 12px; display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; box-shadow: 0 4px 16px rgba(200,16,46,.35);
  }
  .brand-name { font-family: 'Noto Serif JP', serif; font-size: 1.2rem; font-weight: 600; color: var(--preto); }
  .brand-sub  { font-size: .62rem; color: var(--cinza-texto); letter-spacing: .18em; text-transform: uppercase; }

  .sidebar-title {
    font-family: 'Noto Serif JP', serif;
    font-size: 2.1rem; font-weight: 300;
    color: var(--preto); line-height: 1.3; margin-bottom: 1rem;
  }
  .sidebar-title strong { font-weight: 600; color: var(--vermelho); }
  .sidebar-desc { font-size: .9rem; color: var(--cinza-texto); line-height: 1.7; margin-bottom: 2rem; }

  .perks { list-style: none; display: flex; flex-direction: column; gap: .75rem; }
  .perk { display: flex; align-items: flex-start; gap: .75rem; font-size: .87rem; color: var(--cinza-texto); }
  .perk-icon {
    width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
    background: rgba(200,16,46,.08);
    display: flex; align-items: center; justify-content: center; font-size: .9rem;
  }
  .perk strong { display: block; color: var(--preto); font-size: .87rem; font-weight: 500; }

  /* ── CARD ── */
  .card {
    background: var(--branco);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 16px 50px rgba(0,0,0,.07);
    border: 1px solid var(--borda);
    animation: slideUp .45s ease both;
  }
  @keyframes slideUp {
    from { opacity:0; transform: translateY(20px); }
    to   { opacity:1; transform: translateY(0); }
  }

  .card-title    { font-family: 'Noto Serif JP', serif; font-size: 1.5rem; font-weight: 600; color: var(--preto); margin-bottom: .3rem; }
  .card-subtitle { font-size: .87rem; color: var(--cinza-texto); margin-bottom: 1.75rem; }

  /* SOCIAL */
  .social-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: .6rem; margin-bottom: 1.5rem; }
  .btn-social {
    display: flex; align-items: center; justify-content: center; gap: .4rem;
    padding: .65rem .4rem;
    border: 1.5px solid var(--borda); border-radius: 9px;
    background: var(--cinza-bg); font-size: .78rem; font-weight: 500; color: var(--preto);
    cursor: pointer; transition: all .2s; text-decoration: none; font-family: 'DM Sans', sans-serif;
  }
  .btn-social:hover { border-color: var(--vermelho); transform: translateY(-2px); box-shadow: 0 5px 14px rgba(200,16,46,.1); }

  .divider { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; color: var(--cinza-texto); font-size: .8rem; }
  .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--borda); }

  /* FORM */
  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .9rem; }
  .form-grid .full { grid-column: 1 / -1; }
  .form-group { display: flex; flex-direction: column; gap: .4rem; }
  .form-label { font-size: .8rem; font-weight: 500; color: var(--preto); letter-spacing: .02em; }
  .form-label .req { color: var(--vermelho); }
  .input-wrap { position: relative; }
  .input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa; pointer-events: none; }
  .form-input {
    width: 100%; padding: .8rem .9rem .8rem 2.4rem;
    border: 1.5px solid var(--borda); border-radius: 9px;
    font-size: .9rem; font-family: 'DM Sans', sans-serif;
    background: var(--cinza-bg); color: var(--preto); outline: none; transition: all .2s;
  }
  .form-input:focus { border-color: var(--vermelho); background: var(--branco); box-shadow: 0 0 0 3px rgba(200,16,46,.08); }
  .form-input::placeholder { color: #bbb; }
  .form-input.error { border-color: var(--vermelho); }

  .senha-strength { height: 3px; border-radius: 2px; margin-top: .5rem; background: var(--borda); overflow: hidden; }
  .senha-strength-bar { height: 100%; width: 0; border-radius: 2px; transition: all .3s; }
  .senha-hint { font-size: .74rem; color: var(--cinza-texto); margin-top: .3rem; }

  .toggle-senha {
    position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; color: #aaa; padding: 0; display: flex; align-items: center;
  }

  .input-with-prefix { display: flex; }
  .prefix {
    padding: .8rem .7rem;
    border: 1.5px solid var(--borda); border-right: none;
    border-radius: 9px 0 0 9px;
    background: #efefef; font-size: .86rem; color: var(--cinza-texto);
    display: flex; align-items: center; white-space: nowrap;
  }
  .input-with-prefix .form-input { border-radius: 0 9px 9px 0; padding-left: .9rem; }

  .termos-wrap {
    display: flex; align-items: flex-start; gap: .6rem;
    margin: 1.25rem 0; font-size: .82rem; color: var(--cinza-texto); line-height: 1.5;
  }
  .termos-wrap input[type=checkbox] { width: 16px; height: 16px; margin-top: 2px; accent-color: var(--vermelho); flex-shrink: 0; }
  .termos-wrap a { color: var(--vermelho); text-decoration: none; }
  .termos-wrap a:hover { text-decoration: underline; }

  .alert {
    padding: .85rem 1rem; border-radius: 10px; font-size: .87rem;
    margin-bottom: 1.2rem; display: flex; align-items: center; gap: .55rem;
  }
  .alert-erro    { background: rgba(200,16,46,.07); color: #9b0b22; border: 1px solid rgba(200,16,46,.18); }
  .alert-sucesso { background: rgba(26,122,69,.08); color: var(--sucesso); border: 1px solid rgba(26,122,69,.2); }

  .btn-primary {
    width: 100%; padding: .95rem;
    background: var(--vermelho); color: var(--branco); border: none; border-radius: 10px;
    font-size: 1rem; font-weight: 500; font-family: 'DM Sans', sans-serif;
    cursor: pointer; transition: all .25s; letter-spacing: .03em;
  }
  .btn-primary:hover { background: var(--vermelho-escuro); transform: translateY(-2px); box-shadow: 0 10px 28px rgba(200,16,46,.3); }
  .btn-primary:active { transform: translateY(0); }

  .card-footer { text-align: center; margin-top: 1.25rem; font-size: .85rem; color: var(--cinza-texto); }
  .card-footer a { color: var(--vermelho); text-decoration: none; font-weight: 500; }

  @media (max-width: 768px) {
    .container { grid-template-columns: 1fr; gap: 1.5rem; }
    .sidebar-title { font-size: 1.6rem; }
    .card { padding: 1.75rem; }
    .form-grid { grid-template-columns: 1fr; }
    .form-grid .full { grid-column: 1; }
  }
</style>
</head>
<body>
<div class="bg"></div>
<div class="kanji-deco k1">新</div>
<div class="kanji-deco k2">始</div>

<div class="page">
  <div class="container">

    <!-- SIDEBAR -->
    <div class="sidebar">
      <a href="login.php" class="brand">
        <div class="brand-icon">🍣</div>
        <div>
          <div class="brand-name">Temakeria da Bia</div>
          <div class="brand-sub">Restaurante Japonês</div>
        </div>
      </a>

      <h1 class="sidebar-title">Crie sua<br><strong>conta grátis</strong><br>hoje mesmo</h1>
      <p class="sidebar-desc">Faça parte da nossa comunidade e aproveite benefícios exclusivos.</p>

      <ul class="perks">
        <li class="perk"><div class="perk-icon">🎁</div><div><strong>Pontos fidelidade</strong>Acumule e troque por temakis.</div></li>
        <li class="perk"><div class="perk-icon">🚀</div><div><strong>Pedidos mais rápidos</strong>Seus dados salvos para checkout ágil.</div></li>
        <li class="perk"><div class="perk-icon">🍱</div><div><strong>Ofertas exclusivas</strong>Promoções só para membros.</div></li>
        <li class="perk"><div class="perk-icon">📍</div><div><strong>Histórico completo</strong>Acompanhe todos os seus pedidos.</div></li>
      </ul>
    </div>

    <!-- CARD -->
    <div class="card">
      <div class="card-title">Criar sua conta</div>
      <div class="card-subtitle">Preencha os dados abaixo para começar</div>

      <div class="social-row">
        <a href="#" class="btn-social">
          <svg width="15" height="15" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
          Google
        </a>
        <a href="#" class="btn-social">
          <svg width="15" height="15" viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
          Facebook
        </a>
        <a href="#" class="btn-social">
          <svg width="15" height="15" viewBox="0 0 24 24"><path fill="#000" d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
          Apple
        </a>
      </div>

      <div class="divider">ou crie com e-mail</div>

      <?php if ($erro): ?>
      <div class="alert alert-erro">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
        <?= htmlspecialchars($erro) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="" novalidate>
        <div class="form-grid">

          <div class="form-group">
            <label class="form-label" for="nome">Nome completo <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="input-icon"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
              <input type="text" id="nome" name="nome" class="form-input <?= $erro ? 'error':'' ?>"
                placeholder="Seu nome"
                value="<?= htmlspecialchars($campos['nome'] ?? '') ?>"
                required autocomplete="name">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="telefone">Telefone</label>
            <div class="input-with-prefix">
              <span class="prefix">🇧🇷 +55</span>
              <input type="tel" id="telefone" name="telefone" class="form-input"
                placeholder="(11) 99999-9999"
                value="<?= htmlspecialchars($campos['telefone'] ?? '') ?>"
                autocomplete="tel" oninput="mascaraTel(this)">
            </div>
          </div>

          <div class="form-group full">
            <label class="form-label" for="email">E-mail <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="input-icon"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></span>
              <input type="email" id="email" name="email" class="form-input <?= $erro ? 'error':'' ?>"
                placeholder="seu@email.com"
                value="<?= htmlspecialchars($campos['email'] ?? '') ?>"
                required autocomplete="email">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="senha">Senha <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="input-icon"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
              <input type="password" id="senha" name="senha" class="form-input"
                placeholder="Mín. 6 caracteres"
                required autocomplete="new-password"
                oninput="checkStrength(this.value)">
              <button type="button" class="toggle-senha" onclick="toggleField('senha','eye1')">
                <svg id="eye1" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              </button>
            </div>
            <div class="senha-strength"><div class="senha-strength-bar" id="strengthBar"></div></div>
            <div class="senha-hint" id="strengthHint">Use letras, números e símbolos</div>
          </div>

          <div class="form-group">
            <label class="form-label" for="confirma">Confirmar senha <span class="req">*</span></label>
            <div class="input-wrap">
              <span class="input-icon"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></span>
              <input type="password" id="confirma" name="confirma" class="form-input"
                placeholder="Repita a senha"
                required autocomplete="new-password"
                oninput="checkMatch()">
              <button type="button" class="toggle-senha" onclick="toggleField('confirma','eye2')">
                <svg id="eye2" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
              </button>
            </div>
            <div class="senha-hint" id="matchHint"></div>
          </div>

        </div>

        <label class="termos-wrap">
          <input type="checkbox" name="termos" required>
          Concordo com os <a href="#">Termos de Uso</a> e a <a href="#">Política de Privacidade</a> da Temakeria da Bia.
        </label>

        <button type="submit" class="btn-primary">Criar minha conta</button>
      </form>

      <div class="card-footer">
        Já tem uma conta? <a href="login.php">Entrar</a>
      </div>
    </div>

  </div>
</div>

<script>
function toggleField(id) {
  const input = document.getElementById(id);
  input.type = input.type === 'password' ? 'text' : 'password';
}

function checkStrength(val) {
  const bar  = document.getElementById('strengthBar');
  const hint = document.getElementById('strengthHint');
  let score = 0;
  if (val.length >= 6) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const cores  = ['#c8102e','#e67e00','#c9a84c','#1a7a45'];
  const labels = ['Fraca','Razoável','Boa','Forte'];
  bar.style.width      = (score * 25) + '%';
  bar.style.background = cores[score - 1] || '#ddd';
  hint.textContent     = val.length ? (labels[score - 1] || '') : 'Use letras, números e símbolos';
  hint.style.color     = cores[score - 1] || '#888';
}

function checkMatch() {
  const s    = document.getElementById('senha').value;
  const c    = document.getElementById('confirma').value;
  const hint = document.getElementById('matchHint');
  if (!c) { hint.textContent = ''; return; }
  if (s === c) { hint.textContent = '✓ Senhas coincidem';    hint.style.color = '#1a7a45'; }
  else         { hint.textContent = '✗ Senhas não coincidem'; hint.style.color = '#c8102e'; }
}

function mascaraTel(input) {
  let v = input.value.replace(/\D/g, '');
  if (v.length > 11) v = v.slice(0, 11);
  if      (v.length > 6) v = `(${v.slice(0,2)}) ${v.slice(2,7)}-${v.slice(7)}`;
  else if (v.length > 2) v = `(${v.slice(0,2)}) ${v.slice(2)}`;
  input.value = v;
}
</script>
</body>
</html>