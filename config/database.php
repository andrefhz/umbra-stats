
<?php

$host = '127.0.0.1';
$database = 'umbrastats';
$username = 'root';
$password = ''; // Senha do MySQL, se você configurou uma.

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$database};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    exit('Erro ao conectar ao banco de dados. Verifique as configurações.');
}
