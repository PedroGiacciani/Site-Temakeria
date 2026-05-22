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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email  = trim($_POST['email'] ?? '');
    $senha  = $_POST['senha'] ?? '';
    $lembrar= isset($_POST['lembrar']);

    $resultado = usuario_login($email, $senha);

    if ($resultado['ok']) {
        if ($lembrar) {
            setcookie('lembrar_email', $email, time() + (86400 * 30), '/');
        }
        header('Location: dashboard.php');
        exit;
    } else {
        $erro = $resultado['erro'];
    }
}

$email_cookie = $_COOKIE['lembrar_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Entrar — Temakeria da Bia</title>
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
  }

  html, body {
    height: 100%;
    font-family: 'DM Sans', sans-serif;
    background: var(--branco);
    color: var(--preto);
    overflow-x: hidden;
  }

  .bg {
    position: fixed; inset: 0; z-index: 0;
    background:
      radial-gradient(ellipse 70% 50% at 70% 20%, rgba(200,16,46,.05) 0%, transparent 60%),
      radial-gradient(ellipse 50% 60% at 10% 90%, rgba(200,16,46,.03) 0%, transparent 55%),
      var(--branco);
  }
  .bg::before {
    content: '';
    position: absolute; inset: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23c8102e' fill-opacity='0.025'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
  }

  .kanji-deco {
    position: fixed;
    font-family: 'Noto Serif JP', serif;
    font-weight: 300;
    color: rgba(200,16,46,0.04);
    user-select: none; pointer-events: none; z-index: 0;
  }
  .kanji-deco.k1 { font-size: 22vw; top: -5%; right: -3%; }
  .kanji-deco.k2 { font-size: 12vw; bottom: 5%; left: 2%; }

  .wrapper {
    position: relative; z-index: 1;
    min-height: 100vh;
    display: grid;
    grid-template-columns: 1fr 1fr;
  }

  /* ── PAINEL ESQUERDO ── */
  .panel-left {
    background: var(--preto);
    display: flex; flex-direction: column;
    justify-content: space-between;
    padding: 3rem;
    position: relative;
    overflow: hidden;
  }
  .panel-left::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100'%3E%3Ccircle cx='50' cy='50' r='40' fill='none' stroke='rgba(200,16,46,0.12)' stroke-width='1'/%3E%3Ccircle cx='50' cy='50' r='25' fill='none' stroke='rgba(200,16,46,0.07)' stroke-width='1'/%3E%3C/svg%3E") repeat;
  }
  .panel-left-inner { position: relative; z-index: 1; }

  .brand { display: flex; align-items: center; gap: .75rem; text-decoration: none; }
  .brand-icon {
    width: 44px; height: 44px;
    background: var(--vermelho);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    box-shadow: 0 4px 16px rgba(200,16,46,.4);
  }
  .brand-name {
    font-family: 'Noto Serif JP', serif;
    font-size: 1.2rem; font-weight: 600;
    color: var(--branco); letter-spacing: .04em;
  }
  .brand-sub {
    font-size: .62rem; color: rgba(255,255,255,.4);
    letter-spacing: .18em; text-transform: uppercase;
  }

  .panel-hero {
    flex: 1;
    display: flex; flex-direction: column;
    justify-content: center;
    padding: 3rem 0;
  }
  .hero-kanji {
    font-family: 'Noto Serif JP', serif;
    font-size: 8rem; font-weight: 300;
    color: rgba(200,16,46,.18);
    line-height: 1; margin-bottom: 1.5rem;
  }
  .panel-hero h1 {
    font-family: 'Noto Serif JP', serif;
    font-size: clamp(1.8rem, 3vw, 2.8rem);
    font-weight: 300; color: var(--branco);
    line-height: 1.35; margin-bottom: 1rem;
  }
  .panel-hero h1 span { color: var(--vermelho); font-weight: 600; }
  .panel-hero p {
    font-size: .9rem;
    color: rgba(255,255,255,.4);
    line-height: 1.7; max-width: 300px;
  }

  .panel-footer { display: flex; gap: 1.5rem; flex-wrap: wrap; }
  .panel-footer a {
    font-size: .78rem; color: rgba(255,255,255,.25);
    text-decoration: none; transition: color .2s;
  }
  .panel-footer a:hover { color: var(--vermelho); }

  /* ── PAINEL DIREITO ── */
  .panel-right {
    display: flex; align-items: center; justify-content: center;
    padding: 2rem;
    background: var(--branco);
  }

  .card {
    width: 100%; max-width: 420px;
    animation: slideUp .45s ease both;
  }
  @keyframes slideUp {
    from { opacity:0; transform: translateY(20px); }
    to   { opacity:1; transform: translateY(0); }
  }

  .card-header { margin-bottom: 2rem; }
  .card-header h2 {
    font-family: 'Noto Serif JP', serif;
    font-size: 1.9rem; font-weight: 600;
    color: var(--preto); margin-bottom: .3rem;
  }
  .card-header p { font-size: .9rem; color: var(--cinza-texto); }

  /* ── SOCIAL ── */
  .social-grid {
    display: grid; grid-template-columns: 1fr 1fr 1fr;
    gap: .65rem; margin-bottom: 1.5rem;
  }
  .btn-social {
    display: flex; align-items: center; justify-content: center; gap: .4rem;
    padding: .7rem .5rem;
    border: 1.5px solid var(--borda); border-radius: 10px;
    background: var(--cinza-bg);
    font-size: .8rem; font-weight: 500; color: var(--preto);
    cursor: pointer; transition: all .2s; text-decoration: none;
    font-family: 'DM Sans', sans-serif;
  }
  .btn-social:hover {
    border-color: var(--vermelho);
    background: rgba(200,16,46,.04);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(200,16,46,.1);
  }

  .divider {
    display: flex; align-items: center; gap: 1rem;
    margin-bottom: 1.5rem; color: var(--cinza-texto); font-size: .8rem;
  }
  .divider::before, .divider::after {
    content: ''; flex: 1; height: 1px; background: var(--borda);
  }

  /* ── FORM ── */
  .form-group { margin-bottom: 1.1rem; }
  .form-label {
    display: block; font-size: .8rem; font-weight: 500;
    color: var(--preto); margin-bottom: .45rem; letter-spacing: .02em;
  }
  .input-wrap { position: relative; }
  .input-icon {
    position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
    color: #aaa; pointer-events: none;
  }
  .form-input {
    width: 100%;
    padding: .85rem 1rem .85rem 2.5rem;
    border: 1.5px solid var(--borda); border-radius: 10px;
    font-size: .95rem; font-family: 'DM Sans', sans-serif;
    background: var(--cinza-bg); color: var(--preto);
    transition: all .2s; outline: none;
  }
  .form-input:focus {
    border-color: var(--vermelho); background: var(--branco);
    box-shadow: 0 0 0 3px rgba(200,16,46,.08);
  }
  .form-input::placeholder { color: #bbb; }
  .form-input.error { border-color: var(--vermelho); }

  .toggle-senha {
    position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer;
    color: #aaa; padding: 0; display: flex; align-items: center;
  }
  .toggle-senha:hover { color: var(--preto); }

  .form-row {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 1.5rem; font-size: .85rem;
  }
  .checkbox-wrap { display: flex; align-items: center; gap: .5rem; cursor: pointer; }
  .checkbox-wrap input[type=checkbox] {
    width: 16px; height: 16px;
    accent-color: var(--vermelho); cursor: pointer;
  }
  .checkbox-wrap span { color: var(--cinza-texto); }
  .link-esqueceu {
    color: var(--vermelho); text-decoration: none;
    font-weight: 500; transition: color .2s;
  }
  .link-esqueceu:hover { color: var(--vermelho-hover); }

  /* ── ALERTS ── */
  .alert {
    padding: .85rem 1rem; border-radius: 10px; font-size: .87rem;
    margin-bottom: 1.2rem; display: flex; align-items: center; gap: .55rem;
  }
  .alert-erro    { background: rgba(200,16,46,.07); color: #9b0b22; border: 1px solid rgba(200,16,46,.18); }
  .alert-sucesso { background: rgba(30,140,60,.08); color: #155724; border: 1px solid rgba(30,140,60,.2); }

  /* ── BOTÃO ── */
  .btn-primary {
    width: 100%; padding: 1rem;
    background: var(--vermelho);
    color: var(--branco); border: none; border-radius: 10px;
    font-size: 1rem; font-weight: 500; font-family: 'DM Sans', sans-serif;
    cursor: pointer; transition: all .25s; letter-spacing: .03em;
    position: relative; overflow: hidden;
  }
  .btn-primary:hover {
    background: var(--vermelho-escuro);
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(200,16,46,.3);
  }
  .btn-primary:active { transform: translateY(0); }
  .btn-primary:disabled { opacity: .6; cursor: not-allowed; transform: none; }

  .card-footer {
    text-align: center; margin-top: 1.5rem;
    font-size: .87rem; color: var(--cinza-texto);
  }
  .card-footer a { color: var(--vermelho); text-decoration: none; font-weight: 500; }
  .card-footer a:hover { text-decoration: underline; }

  @media (max-width: 768px) {
    .wrapper { grid-template-columns: 1fr; }
    .panel-left { display: none; }
    .panel-right { padding: 2rem 1.5rem; align-items: flex-start; padding-top: 3rem; }
  }
</style>
</head>
<body>
<div class="bg"></div>
<div class="kanji-deco k1">食</div>
<div class="kanji-deco k2">愛</div>

<div class="wrapper">

  <!-- ESQUERDO -->
  <div class="panel-left">
    <div class="panel-left-inner">
      <a href="#" class="brand">
        <div class="brand-icon">🍣</div>
        <div>
          <div class="brand-name">Temakeria da Bia</div>
          <div class="brand-sub">Restaurante Japonês</div>
        </div>
      </a>
    </div>

    <div class="panel-hero">
      <div class="hero-kanji">旨</div>
      <h1>Sabor que<br><span>a Bia</span><br>faz com amor.</h1>
      <p>Temakis fresquinhos, ingredientes selecionados e muito carinho em cada pedido.</p>
    </div>

    <div class="panel-footer panel-left-inner">
      <a href="#">Sobre nós</a>
      <a href="#">Cardápio</a>
      <a href="#">Reservas</a>
      <a href="#">Contato</a>
    </div>
  </div>

  <!-- DIREITO -->
  <div class="panel-right">
    <div class="card">
      <div class="card-header">
        <h2>Bem-vindo de volta</h2>
        <p>Entre na sua conta para continuar</p>
      </div>

      <!-- SOCIAL -->
      <div class="social-grid">
        <a href="#" class="btn-social">
          <svg width="17" height="17" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
          Google
        </a>
        <a href="#" class="btn-social">
          <svg width="17" height="17" viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
          Facebook
        </a>
        <a href="#" class="btn-social">
          <svg width="17" height="17" viewBox="0 0 24 24"><path fill="#000" d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
          Apple
        </a>
      </div>

      <div class="divider">ou entre com e-mail</div>

      <!-- ALERTS -->
      <div id="alerta" style="display:none" class="alert"></div>

      <?php if ($erro): ?>
      <div class="alert alert-erro">
        <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
        <?= htmlspecialchars($erro) ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="">
        <div class="form-group">
          <label class="form-label" for="email">E-mail</label>
          <div class="input-wrap">
            <span class="input-icon">
              <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </span>
            <input type="email" id="email" name="email"
              class="form-input <?= $erro ? 'error' : '' ?>"
              placeholder="seu@email.com"
              value="<?= htmlspecialchars($email_cookie ?: ($_POST['email'] ?? '')) ?>"
              autocomplete="email" required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="senha">Senha</label>
          <div class="input-wrap">
            <span class="input-icon">
              <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </span>
            <input type="password" id="senha" name="senha"
              class="form-input <?= $erro ? 'error' : '' ?>"
              placeholder="Sua senha"
              autocomplete="current-password" required>
            <button type="button" class="toggle-senha" onclick="toggleSenha()">
              <svg id="eyeIcon" width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="form-row">
          <label class="checkbox-wrap">
            <input type="checkbox" name="lembrar" <?= $email_cookie ? 'checked' : '' ?>>
            <span>Lembrar de mim</span>
          </label>
          <a href="esqueceu-senha.php" class="link-esqueceu">Esqueceu a senha?</a>
        </div>

        <button type="submit" class="btn-primary">Entrar</button>
      </form>

      <div class="card-footer">
        Não tem uma conta? <a href="cadastro.php">Cadastre-se grátis</a>
      </div>
    </div>
  </div>

</div>

<script>
function toggleSenha() {
  const input = document.getElementById('senha');
  const icon  = document.getElementById('eyeIcon');
  const visivel = input.type === 'password';
  input.type = visivel ? 'text' : 'password';
  icon.innerHTML = visivel
    ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>'
    : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
}
</script>
</body>
</html>