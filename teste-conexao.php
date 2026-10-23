
<?php

require_once __DIR__ . '/config/database.php';

try {
    $stmt = $pdo->query('SELECT DATABASE() AS banco');
    $resultado = $stmt->fetch();

    echo '<!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <title>Teste de conexão | UmbraStats</title>
    </head>
    <body style="background:#0b0b10;color:#eee;font-family:Arial;padding:30px">';

    echo '<h1>UmbraStats</h1>';
    echo '<p style="color:#58d6a0">Conexão com MySQL realizada com sucesso!</p>';
    echo '<p>Banco conectado: <strong>'
        . htmlspecialchars($resultado['banco'])
        . '</strong></p>';

    echo '</body></html>';

} catch (PDOException $e) {
    echo 'Erro ao testar o banco de dados.';
}
