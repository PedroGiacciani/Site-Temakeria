<?php
/**
 * ============================================================
 *  db.php — Temakeria da Bia
 *  Banco de dados em arquivos JSON
 *  Módulos: Usuários, Sessão, Recuperação de senha
 * ============================================================
 */

define('DB_DIR',       __DIR__ . '/dados/');
define('DB_USUARIOS',  DB_DIR . 'usuarios.json');
define('DB_PEDIDOS',   DB_DIR . 'pedidos.json');
define('DB_ENDERECOS', DB_DIR . 'enderecos.json');

// Garante que a pasta exista com permissão de escrita
if (!is_dir(DB_DIR)) {
    mkdir(DB_DIR, 0755, true);
    file_put_contents(DB_DIR . '.htaccess', "Deny from all\n");
}

// Garante session_start() mesmo se chamado diretamente
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Helpers de leitura/gravação ────────────────────────────

function db_ler(string $arquivo): array {
    if (!file_exists($arquivo)) return [];
    $json = file_get_contents($arquivo);
    return json_decode($json, true) ?? [];
}

function db_gravar(string $arquivo, array $dados): bool {
    $resultado = file_put_contents(
        $arquivo,
        json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
        LOCK_EX   // evita corrupção em escritas simultâneas
    );
    return $resultado !== false;
}

function db_uuid(): string {
    return 'bia-' . bin2hex(random_bytes(6)) . '-' . time();
}

// ─── USUÁRIOS ───────────────────────────────────────────────

function usuarios_todos(): array {
    return db_ler(DB_USUARIOS);
}

function usuario_buscar_por_email(string $email): ?array {
    foreach (usuarios_todos() as $u) {
        if (strtolower($u['email']) === strtolower(trim($email))) return $u;
    }
    return null;
}

function usuario_buscar_por_id(string $id): ?array {
    foreach (usuarios_todos() as $u) {
        if ($u['id'] === $id) {
            unset($u['senha_hash']);
            return $u;
        }
    }
    return null;
}

function usuario_cadastrar(string $nome, string $email, string $senha, string $telefone = ''): array {
    if (!$nome || !$email || !$senha)
        return ['ok' => false, 'erro' => 'Nome, e-mail e senha são obrigatórios.'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        return ['ok' => false, 'erro' => 'E-mail inválido.'];

    if (strlen($senha) < 6)
        return ['ok' => false, 'erro' => 'Senha deve ter pelo menos 6 caracteres.'];

    if (usuario_buscar_por_email($email))
        return ['ok' => false, 'erro' => 'E-mail já cadastrado.'];

    // Verifica se consegue gravar antes de tentar
    if (!is_writable(DB_DIR))
        return ['ok' => false, 'erro' => 'Erro interno: sem permissão de escrita. Contate o administrador.'];

    $todos = usuarios_todos();

    $novo = [
        'id'         => db_uuid(),
        'nome'       => trim($nome),
        'email'      => strtolower(trim($email)),
        'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
        'telefone'   => trim($telefone),
        'criado_em'  => date('c'),
        'ativo'      => true,
    ];

    $todos[] = $novo;

    if (!db_gravar(DB_USUARIOS, $todos))
        return ['ok' => false, 'erro' => 'Não foi possível salvar o cadastro. Verifique as permissões da pasta /dados/.'];

    unset($novo['senha_hash']);
    return ['ok' => true, 'usuario' => $novo];
}

function usuario_login(string $email, string $senha): array {
    if (!$email || !$senha)
        return ['ok' => false, 'erro' => 'Preencha e-mail e senha.'];

    $usuario = usuario_buscar_por_email($email);

    if (!$usuario)
        return ['ok' => false, 'erro' => 'E-mail ou senha incorretos.'];

    if (!($usuario['ativo'] ?? true))
        return ['ok' => false, 'erro' => 'Conta desativada. Entre em contato.'];

    if (!password_verify($senha, $usuario['senha_hash']))
        return ['ok' => false, 'erro' => 'E-mail ou senha incorretos.'];

    // Regenera o ID de sessão por segurança
    session_regenerate_id(true);

    $_SESSION['usuario_id']    = $usuario['id'];
    $_SESSION['usuario_nome']  = $usuario['nome'];
    $_SESSION['usuario_email'] = $usuario['email'];

    unset($usuario['senha_hash']);
    return ['ok' => true, 'usuario' => $usuario];
}

function usuario_logado(): ?array {
    if (empty($_SESSION['usuario_id'])) return null;
    return [
        'id'    => $_SESSION['usuario_id'],
        'nome'  => $_SESSION['usuario_nome'],
        'email' => $_SESSION['usuario_email'],
    ];
}

function usuario_logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

// ─── RECUPERAÇÃO DE SENHA ────────────────────────────────────

function reset_gerar_codigo(string $email): string {
    $codigo = strval(random_int(100000, 999999));
    $_SESSION['reset_email']  = strtolower(trim($email));
    $_SESSION['reset_code']   = $codigo;
    $_SESSION['reset_expira'] = time() + 900; // 15 minutos
    return $codigo;
}

function reset_validar_codigo(string $codigo): bool {
    if (empty($_SESSION['reset_code'])) return false;
    if (time() > ($_SESSION['reset_expira'] ?? 0)) return false;
    return hash_equals($_SESSION['reset_code'], $codigo); // evita timing attack
}

function reset_nova_senha(string $nova_senha): array {
    if (!($_SESSION['reset_ok'] ?? false))
        return ['ok' => false, 'erro' => 'Sessão de recuperação inválida.'];

    if (strlen($nova_senha) < 6)
        return ['ok' => false, 'erro' => 'Senha deve ter pelo menos 6 caracteres.'];

    $email = $_SESSION['reset_email'] ?? '';
    $todos = usuarios_todos();
    $atualizado = false;

    foreach ($todos as &$u) {
        if (strtolower($u['email']) === $email) {
            $u['senha_hash']    = password_hash($nova_senha, PASSWORD_DEFAULT);
            $u['atualizado_em'] = date('c');
            $atualizado = true;
            break;
        }
    }

    if (!$atualizado)
        return ['ok' => false, 'erro' => 'Usuário não encontrado.'];

    if (!db_gravar(DB_USUARIOS, $todos))
        return ['ok' => false, 'erro' => 'Não foi possível salvar a nova senha.'];

    unset($_SESSION['reset_email'], $_SESSION['reset_code'],
          $_SESSION['reset_expira'], $_SESSION['reset_ok']);

    return ['ok' => true];
}
