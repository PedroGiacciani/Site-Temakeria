<?php
session_start();
require_once 'db.php';

$etapa = $_GET['etapa'] ?? 'email'; // email | codigo | nova-senha | sucesso
$erro  = '';

// ── ETAPA 1: Enviar e-mail ──────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $etapa === 'email') {
    $email = trim($_POST['email'] ?? '');
    if (!$email) {
        $erro = 'Informe seu e-mail.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif (!usuario_buscar_por_email($email)) {
        $erro = 'E-mail não cadastrado.';
    } else {
        $codigo = reset_gerar_codigo($email);
        // Em produção: envie o $codigo por e-mail real (PHPMailer, SendGrid etc.)
        // Por enquanto armazenamos na sessão para demonstração
        header('Location: ?etapa=codigo');
        exit;
    }
}

// ── ETAPA 2: Validar código ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $etapa === 'codigo') {
    $codigo = implode('', $_POST['d'] ?? []);
    if (strlen($codigo) !== 6) {
        $erro = 'Digite o código de 6 dígitos.';
    } elseif (!reset_validar_codigo($codigo)) {
        $erro = 'Código incorreto ou expirado. Tente novamente.';
    } else {
        $_SESSION['reset_ok'] = true;
        header('Location: ?etapa=nova-senha');
        exit;
    }
}

// ── ETAPA 3: Nova senha ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $etapa === 'nova-senha') {
    if (!($_SESSION['reset_ok'] ?? false)) {
        header('Location: ?etapa=email'); exit;
    }
    $senha   = $_POST['senha']   ?? '';
    $confirma= $_POST['confirma']?? '';
    if (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirma) {
        $erro = 'As senhas não coincidem.';
    } else {
        $resultado = reset_nova_senha($senha);
        if ($resultado['ok']) {
            header('Location: ?etapa=sucesso');
            exit;
        } else {
            $erro = $resultado['erro'];
        }
    }
}

$email_session = htmlspecialchars($_SESSION['reset_email'] ?? '');
$demo_codigo   = $_SESSION['reset_code'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar senha — Temakeria da Bia</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@300;400;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --vermelho: #c8102e;
    --vermelho-escuro: #9b0b22;
    --preto: #0f0f0f;
    --branco: #ffffff;
    --cinza-bg: #f7f7f7;
    --cinza-texto: #555555;
    --borda: rgba(0,0,0,0.1);
    --sucesso: #1a7a45;
  }

  html, body { min-height: 100vh; font-family: 'DM Sans', sans-serif; background: var(--branco); color: var(--preto); }

  .bg {
    position: fixed; inset: 0; z-index: 0;
    background:
      radial-gradient(ellipse 70% 50% at 50% 0%, rgba(200,16,46,.06) 0%, transparent 60%),
      radial-gradient(ellipse 50% 40% at 80% 90%, rgba(200,16,46,.04) 0%, transparent 50%),
      var(--branco);
  }

  .kanji-deco {
    position: fixed; font-family: 'Noto Serif JP', serif;
    font-weight: 300; color: rgba(200,16,46,.04);
    user-select: none; pointer-events: none; z-index: 0;
  }
  .kanji-deco.k1 { font-size: 18vw; top: 5%; right: 2%; }
  .kanji-deco.k2 { font-size: 10vw; bottom: 8%; left: 2%; }

  .page {
    position: relative; z-index: 1;
    min-height: 100vh;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    padding: 2rem;
  }

  .brand {
    display: flex; align-items: center; gap: .75rem;
    text-decoration: none; margin-bottom: 2.5rem;
  }
  .brand-icon {
    width: 42px; height: 42px; background: var(--vermelho);
    border-radius: 11px; display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; box-shadow: 0 4px 14px rgba(200,16,46,.35);
  }
  .brand-name { font-family: 'Noto Serif JP', serif; font-size: 1.2rem; font-weight: 600; color: var(--preto); }
  .brand-sub  { font-size: .6rem; color: var(--cinza-texto); letter-spacing: .18em; text-transform: uppercase; }

  .card {
    background: var(--branco);
    border-radius: 20px;
    padding: 2.5rem;
    box-shadow: 0 20px 60px rgba(0,0,0,.08);
    border: 1px solid var(--borda);
    width: 100%; max-width: 430px;
    animation: pop .4s cubic-bezier(.34,1.56,.64,1) both;
  }
  @keyframes pop {
    from { opacity:0; transform: scale(.95) translateY(12px); }
    to   { opacity:1; transform: scale(1) translateY(0); }
  }

  /* STEPS */
  .steps {
    display: flex; align-items: center; justify-content: center;
    gap: .5rem; margin-bottom: 2rem;
  }
  .step {
    width: 28px; height: 28px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .74rem; font-weight: 500;
    border: 2px solid var(--borda); color: var(--cinza-texto); transition: all .3s;
  }
  .step.active { background: var(--vermelho); border-color: var(--vermelho); color: var(--branco); }
  .step.done   { background: var(--sucesso);  border-color: var(--sucesso);  color: var(--branco); }
  .step-line   { flex: 1; height: 2px; background: var(--borda); max-width: 40px; transition: background .3s; }
  .step-line.done { background: var(--sucesso); }

  .icon-circle {
    width: 62px; height: 62px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 1.25rem; font-size: 1.7rem;
  }
  .icon-circle.red   { background: rgba(200,16,46,.08); }
  .icon-circle.blue  { background: rgba(66,133,244,.1); }
  .icon-circle.green { background: rgba(26,122,69,.1); }

  .card-title { font-family: 'Noto Serif JP', serif; font-size: 1.5rem; font-weight: 600; color: var(--preto); text-align: center; margin-bottom: .4rem; }
  .card-desc  { text-align: center; font-size: .87rem; color: var(--cinza-texto); line-height: 1.6; margin-bottom: 1.75rem; }
  .card-desc strong { color: var(--preto); }

  .form-group { margin-bottom: 1.1rem; }
  .form-label { display: block; font-size: .8rem; font-weight: 500; color: var(--preto); margin-bottom: .45rem; }
  .input-wrap { position: relative; }
  .input-icon { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: #aaa; pointer-events: none; }
  .form-input {
    width: 100%; padding: .85rem 1rem .85rem 2.5rem;
    border: 1.5px solid var(--borda); border-radius: 10px;
    font-size: .95rem; font-family: 'DM Sans', sans-serif;
    background: var(--cinza-bg); color: var(--preto); outline: none; transition: all .2s;
  }
  .form-input:focus { border-color: var(--vermelho); background: var(--branco); box-shadow: 0 0 0 3px rgba(200,16,46,.08); }
  .form-input::placeholder { color: #bbb; }

  .toggle-senha {
    position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; color: #aaa; padding: 0; display: flex;
  }

  /* OTP */
  .otp-wrap { display: flex; gap: .6rem; justify-content: center; margin-bottom: 1.75rem; }
  .otp-input {
    width: 50px; height: 56px;
    text-align: center; font-size: 1.5rem; font-weight: 600;
    border: 2px solid var(--borda); border-radius: 10px;
    background: var(--cinza-bg); color: var(--preto); outline: none;
    font-family: 'DM Sans', sans-serif; transition: all .2s;
  }
  .otp-input:focus  { border-color: var(--vermelho); background: var(--branco); box-shadow: 0 0 0 3px rgba(200,16,46,.08); }
  .otp-input.filled { border-color: var(--vermelho); background: rgba(200,16,46,.04); }
  .otp-input.error  { border-color: var(--vermelho); }

  .reenviar { text-align: center; font-size: .82rem; color: var(--cinza-texto); margin-bottom: 1.5rem; }
  .reenviar button {
    color: var(--vermelho); background: none; border: none;
    cursor: pointer; font-size: .82rem; font-family: 'DM Sans', sans-serif; font-weight: 500;
  }
  .reenviar button:hover { text-decoration: underline; }

  .senha-strength { height: 3px; border-radius: 2px; margin-top: .5rem; background: var(--borda); overflow: hidden; }
  .senha-strength-bar { height: 100%; width: 0; border-radius: 2px; transition: all .3s; }
  .senha-hint { font-size: .74rem; color: var(--cinza-texto); margin-top: .3rem; }

  .alert {
    padding: .8rem 1rem; border-radius: 10px; font-size: .86rem;
    margin-bottom: 1.2rem; display: flex; align-items: center; gap: .55rem;
  }
  .alert-erro { background: rgba(200,16,46,.07); color: #9b0b22; border: 1px solid rgba(200,16,46,.18); }

  .btn-primary {
    width: 100%; padding: .95rem;
    background: var(--vermelho); color: var(--branco); border: none; border-radius: 10px;
    font-size: 1rem; font-weight: 500; font-family: 'DM Sans', sans-serif;
    cursor: pointer; transition: all .25s; letter-spacing: .03em; margin-bottom: 1rem;
  }
  .btn-primary:hover { background: var(--vermelho-escuro); transform: translateY(-2px); box-shadow: 0 10px 28px rgba(200,16,46,.3); }
  .btn-primary.verde { background: var(--sucesso); }
  .btn-primary.verde:hover { background: #145e34; box-shadow: 0 10px 28px rgba(26,122,69,.3); }

  .btn-ghost {
    width: 100%; padding: .85rem;
    background: none; color: var(--cinza-texto);
    border: 1.5px solid var(--borda); border-radius: 10px;
    font-size: .9rem; font-family: 'DM Sans', sans-serif;
    cursor: pointer; transition: all .2s; text-decoration: none; display: block; text-align: center;
  }
  .btn-ghost:hover { border-color: var(--vermelho); color: var(--preto); }

  /* SUCESSO */
  .success-check {
    width: 76px; height: 76px; border-radius: 50%;
    background: var(--sucesso);
    margin: 0 auto 1.5rem;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 10px 28px rgba(26,122,69,.3);
    animation: popIn .5s cubic-bezier(.34,1.56,.64,1) both .1s;
  }
  @keyframes popIn { from { transform: scale(0); opacity:0; } to { transform: scale(1); opacity:1; } }

  .demo-hint {
    text-align: center; font-size: .75rem; color: #aaa; margin-top: .75rem;
    background: var(--cinza-bg); padding: .5rem .75rem; border-radius: 8px;
  }
  .demo-hint strong { color: var(--preto); }

  @media (max-width: 480px) {
    .card { padding: 2rem 1.5rem; }
    .otp-input { width: 42px; height: 50px; font-size: 1.3rem; }
    .otp-wrap { gap: .4rem; }
  }
</style>
</head>
<body>
<div class="bg"></div>
<div class="kanji-deco k1">鍵</div>
<div class="kanji-deco k2">安</div>

<div class="page">
  <a href="login.php" class="brand">
    <div class="brand-icon">🍣</div>
    <div>
      <div class="brand-name">Temakeria da Bia</div>
      <div class="brand-sub">Restaurante Japonês</div>
    </div>
  </a>

  <div class="card">

    <?php if ($etapa !== 'sucesso'): ?>
    <div class="steps">
      <div class="step <?= $etapa === 'email' ? 'active' : 'done' ?>"><?= $etapa === 'email' ? '1' : '✓' ?></div>
      <div class="step-line <?= in_array($etapa, ['codigo','nova-senha']) ? 'done' : '' ?>"></div>
      <div class="step <?= $etapa === 'codigo' ? 'active' : ($etapa === 'nova-senha' ? 'done' : '') ?>"><?= $etapa === 'nova-senha' ? '✓' : '2' ?></div>
      <div class="step-line <?= $etapa === 'nova-senha' ? 'done' : '' ?>"></div>
      <div class="step <?= $etapa === 'nova-senha' ? 'active' : '' ?>">3</div>
    </div>
    <?php endif; ?>

    <?php if ($erro): ?>
    <div class="alert alert-erro">
      <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-width="2" d="M12 8v4m0 4h.01"/></svg>
      <?= htmlspecialchars($erro) ?>
    </div>
    <?php endif; ?>

    <?php if ($etapa === 'email'): ?>
    <!-- ═══ ETAPA 1 ═══ -->
    <div class="icon-circle red">📧</div>
    <h1 class="card-title">Esqueceu sua senha?</h1>
    <p class="card-desc">Informe o e-mail cadastrado e enviaremos um código de verificação.</p>
    <form method="POST" action="">
      <div class="form-group">
        <label class="form-label" for="email">E-mail</label>
        <div class="input-wrap">
          <span class="input-icon"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></span>
          <input type="email" id="email" name="email" class="form-input"
            placeholder="seu@email.com"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            required autocomplete="email">
        </div>
      </div>
      <button type="submit" class="btn-primary">Enviar código</button>
    </form>
    <a href="login.php" class="btn-ghost">Voltar ao login</a>

    <?php elseif ($etapa === 'codigo'): ?>
    <!-- ═══ ETAPA 2 ═══ -->
    <div class="icon-circle blue">🔐</div>
    <h1 class="card-title">Verifique seu e-mail</h1>
    <p class="card-desc">Enviamos um código de 6 dígitos para<br><strong><?= $email_session ?></strong></p>
    <form method="POST" action="?etapa=codigo" id="otpForm">
      <div class="otp-wrap">
        <?php for($i = 1; $i <= 6; $i++): ?>
        <input type="text" name="d[]" class="otp-input"
          maxlength="1" inputmode="numeric" pattern="[0-9]"
          id="otp<?= $i ?>"
          autocomplete="<?= $i === 1 ? 'one-time-code' : 'off' ?>">
        <?php endfor; ?>
      </div>
      <div class="reenviar">
        Não recebeu? <button type="button" onclick="reenviar()">Reenviar código</button>
        <span id="timer"></span>
      </div>
      <button type="submit" class="btn-primary" id="btnVerify" disabled>Verificar código</button>
    </form>
    <a href="esqueceu-senha.php" class="btn-ghost">Usar outro e-mail</a>
    <?php if ($demo_codigo): ?>
    <div class="demo-hint">💡 Código de demonstração: <strong><?= $demo_codigo ?></strong></div>
    <?php endif; ?>

    <?php elseif ($etapa === 'nova-senha'): ?>
    <!-- ═══ ETAPA 3 ═══ -->
    <div class="icon-circle red">🔑</div>
    <h1 class="card-title">Crie nova senha</h1>
    <p class="card-desc">Escolha uma senha forte para proteger sua conta.</p>
    <form method="POST" action="?etapa=nova-senha">
      <div class="form-group">
        <label class="form-label" for="senha">Nova senha</label>
        <div class="input-wrap">
          <span class="input-icon"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg></span>
          <input type="password" id="senha" name="senha" class="form-input"
            placeholder="Mín. 6 caracteres" required autocomplete="new-password"
            oninput="checkStrength(this.value)">
          <button type="button" class="toggle-senha" onclick="toggleField('senha')">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          </button>
        </div>
        <div class="senha-strength"><div class="senha-strength-bar" id="strengthBar"></div></div>
        <div class="senha-hint" id="strengthHint">Use letras, números e símbolos</div>
      </div>
      <div class="form-group">
        <label class="form-label" for="confirma">Confirmar nova senha</label>
        <div class="input-wrap">
          <span class="input-icon"><svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></span>
          <input type="password" id="confirma" name="confirma" class="form-input"
            placeholder="Repita a nova senha" required autocomplete="new-password"
            oninput="checkMatch()">
          <button type="button" class="toggle-senha" onclick="toggleField('confirma')">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          </button>
        </div>
        <div class="senha-hint" id="matchHint"></div>
      </div>
      <button type="submit" class="btn-primary">Salvar nova senha</button>
    </form>

    <?php elseif ($etapa === 'sucesso'): ?>
    <!-- ═══ SUCESSO ═══ -->
    <div style="text-align:center;padding:1rem 0">
      <div class="success-check">
        <svg width="34" height="34" fill="none" viewBox="0 0 24 24" stroke="white">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
      </div>
      <h1 class="card-title">Senha redefinida!</h1>
      <p class="card-desc" style="margin-bottom:2rem">
        Sua senha foi atualizada com sucesso.<br>Agora você pode fazer login.
      </p>
      <a href="login.php" class="btn-primary verde" style="display:block;text-align:center;text-decoration:none;padding:.95rem;border-radius:10px;color:#fff;font-weight:500;">
        Ir para o login
      </a>
    </div>
    <?php endif; ?>

  </div>
</div>

<script>
// ── OTP ──
document.addEventListener('DOMContentLoaded', () => {
  const inputs = document.querySelectorAll('.otp-input');
  if (!inputs.length) return;

  inputs.forEach((inp, i) => {
    inp.addEventListener('input', e => {
      inp.value = inp.value.replace(/\D/g, '');
      inp.classList.toggle('filled', !!inp.value);
      if (inp.value && i < inputs.length - 1) inputs[i + 1].focus();
      checkOtp();
    });
    inp.addEventListener('keydown', e => {
      if (e.key === 'Backspace' && !inp.value && i > 0) inputs[i - 1].focus();
    });
    inp.addEventListener('paste', e => {
      e.preventDefault();
      const texto = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
      [...texto].forEach((ch, j) => {
        if (inputs[i + j]) { inputs[i + j].value = ch; inputs[i + j].classList.add('filled'); }
      });
      checkOtp();
      inputs[Math.min(i + texto.length, inputs.length - 1)]?.focus();
    });
  });
  inputs[0]?.focus();

  let secs = 60;
  const timerEl = document.getElementById('timer');
  const iv = setInterval(() => {
    secs--;
    if (timerEl) timerEl.textContent = `(${secs}s)`;
    if (secs <= 0) { clearInterval(iv); if (timerEl) timerEl.textContent = ''; }
  }, 1000);
});

function checkOtp() {
  const inputs = document.querySelectorAll('.otp-input');
  const btn    = document.getElementById('btnVerify');
  if (btn) btn.disabled = ![...inputs].every(i => i.value.match(/\d/));
}

function reenviar() {
  alert('Em produção, o código seria reenviado por e-mail.');
}

function checkStrength(val) {
  const bar  = document.getElementById('strengthBar');
  const hint = document.getElementById('strengthHint');
  if (!bar) return;
  let score = 0;
  if (val.length >= 6) score++;
  if (/[A-Z]/.test(val)) score++;
  if (/[0-9]/.test(val)) score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const cores  = ['#c8102e','#e67e00','#c9a84c','#1a7a45'];
  const labels = ['Fraca','Razoável','Boa','Forte'];
  bar.style.width      = (score * 25) + '%';
  bar.style.background = cores[score - 1] || '#ddd';
  if (hint) { hint.textContent = val.length ? (labels[score - 1] || '') : 'Use letras, números e símbolos'; hint.style.color = cores[score - 1] || '#888'; }
}

function checkMatch() {
  const s    = document.getElementById('senha')?.value;
  const c    = document.getElementById('confirma')?.value;
  const hint = document.getElementById('matchHint');
  if (!hint || !c) return;
  if (s === c) { hint.textContent = '✓ Senhas coincidem';    hint.style.color = '#1a7a45'; }
  else         { hint.textContent = '✗ Senhas não coincidem'; hint.style.color = '#c8102e'; }
}

function toggleField(id) {
  const input = document.getElementById(id);
  if (input) input.type = input.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>