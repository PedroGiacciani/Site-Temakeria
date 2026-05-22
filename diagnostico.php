<?php
// ── Arquivo temporário de diagnóstico — REMOVA após resolver ──
session_start();

$db_dir = __DIR__ . '/dados/';
$json   = $db_dir . 'usuarios.json';

echo "<h2>Diagnóstico — Temakeria da Bia</h2><pre>";

// 1. Pasta dados/
echo "Pasta dados/ existe? " . (is_dir($db_dir) ? "✅ SIM" : "❌ NÃO") . "\n";
if (is_dir($db_dir)) {
    echo "Pasta dados/ tem escrita? " . (is_writable($db_dir) ? "✅ SIM" : "❌ NÃO — esse é o problema!") . "\n";
}

// 2. Arquivo usuarios.json
echo "usuarios.json existe? " . (file_exists($json) ? "✅ SIM" : "❌ NÃO") . "\n";
if (file_exists($json)) {
    echo "usuarios.json tem escrita? " . (is_writable($json) ? "✅ SIM" : "❌ NÃO") . "\n";
    $conteudo = file_get_contents($json);
    $usuarios = json_decode($conteudo, true);
    echo "Usuários cadastrados: " . count($usuarios ?? []) . "\n";
    foreach (($usuarios ?? []) as $u) {
        echo "  - {$u['email']} | ativo: " . ($u['ativo'] ? 'sim' : 'não') . "\n";
    }
}

// 3. Sessão
echo "\nSessão atual:\n";
echo "  usuario_id: "    . ($_SESSION['usuario_id']    ?? '(vazio)') . "\n";
echo "  usuario_nome: "  . ($_SESSION['usuario_nome']  ?? '(vazio)') . "\n";
echo "  usuario_email: " . ($_SESSION['usuario_email'] ?? '(vazio)') . "\n";

// 4. Teste de escrita
echo "\nTeste de escrita na pasta dados/: ";
if (!is_dir($db_dir)) {
    mkdir($db_dir, 0755, true);
}
$teste = @file_put_contents($db_dir . 'teste.tmp', 'ok');
if ($teste !== false) {
    unlink($db_dir . 'teste.tmp');
    echo "✅ Funcionou!\n";
} else {
    echo "❌ Falhou — verifique as permissões da pasta\n";
}

echo "</pre>";
