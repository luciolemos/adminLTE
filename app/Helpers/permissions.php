<?php

function user_has_permission($permissao) {
    $user = $_SESSION['user'] ?? null;
    if (!$user) return false;
    $cargo = $user['cargo'] ?? null;
    if (!$cargo) return false;

    // Opcional: cache em sessão pra não ficar toda hora no banco
    if (!isset($_SESSION['_permissoes'])) {
        $dsn  = $_ENV['DB_DSN'] ?? '';
        $userdb = $_ENV['DB_USER'] ?? '';
        $passdb = $_ENV['DB_PASS'] ?? '';
        $pdo = new PDO($dsn, $userdb, $passdb);
        $stmt = $pdo->prepare("
            SELECT p.nome 
            FROM cargos_permissoes cp 
            INNER JOIN permissoes p ON cp.permissao_id = p.id
            WHERE cp.cargo = ?
        ");
        $stmt->execute([$cargo]);
        $_SESSION['_permissoes'] = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'nome');
    }
    return in_array($permissao, $_SESSION['_permissoes']);
}

