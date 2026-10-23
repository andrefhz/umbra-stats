
<?php

date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/config/database.php';

$url = 'https://www.dkumbra.com/ranking.php';

function responder(string $mensagem): void
{
    echo '<p>' . htmlspecialchars(
        $mensagem,
        ENT_QUOTES,
        'UTF-8'
    ) . '</p>';
}

if (!function_exists('curl_init')) {
    exit('Erro: a extensão cURL não está habilitada no PHP.');
}

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_USERAGENT => 'UmbraStats/1.0',
]);

$html = curl_exec($ch);
$error = curl_error($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($html === false || $status !== 200) {
    exit(
        'Falha ao consultar o ranking. HTTP: '
        . (int) $status . '. '
        . htmlspecialchars($error, ENT_QUOTES, 'UTF-8')
    );
}

libxml_use_internal_errors(true);

$dom = new DOMDocument();

if (!$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET)) {
    exit('Não foi possível interpretar o HTML do ranking.');
}

$xpath = new DOMXPath($dom);
$tables = $xpath->query('//table');

$races = ['Slayers', 'Ousters', 'Vampires'];

if ($tables->length < 3) {
    exit('O ranking não apresentou as três tabelas esperadas. Nada foi salvo.');
}

$date = date('Y-m-d');

$charactersFound = 0;
$snapshotsSaved = 0;
$raceCounts = [
    'Slayers' => 0,
    'Ousters' => 0,
    'Vampires' => 0,
];

try {
    $pdo->beginTransaction();

    $saveCharacter = $pdo->prepare(
        'INSERT INTO characters (race, name)
         VALUES (:race, :name)
         ON DUPLICATE KEY UPDATE
            id = LAST_INSERT_ID(id),
            last_seen = CURRENT_TIMESTAMP'
    );

    $findCharacter = $pdo->prepare(
        'SELECT id FROM characters
         WHERE race = :race AND name = :name'
    );

    $saveSnapshot = $pdo->prepare(
        'INSERT INTO ranking_snapshots
            (
                character_id,
                snapshot_date,
                position,
                level,
                title,
                class,
                rank_category,
                rank_image
            )
         VALUES
            (
                :character_id,
                :snapshot_date,
                :position,
                :level,
                :title,
                :class,
                :rank_category,
                :rank_image
            )
         ON DUPLICATE KEY UPDATE
            position = VALUES(position),
            level = VALUES(level),
            title = VALUES(title),
            class = VALUES(class),
            rank_category = VALUES(rank_category),
            rank_image = VALUES(rank_image),
            collected_at = CURRENT_TIMESTAMP'
    );

    foreach ($tables as $tableIndex => $table) {
        if (!isset($races[$tableIndex])) {
            continue;
        }

        $race = $races[$tableIndex];
        $rows = $xpath->query('.//tr', $table);
        $position = 0;

        foreach ($rows as $row) {
            $cells = $xpath->query('./th | ./td', $row);

            if ($cells->length < 5) {
                continue;
            }

            $name = trim($cells->item(0)->textContent);
            $title = trim($cells->item(1)->textContent);
            $levelText = trim($cells->item(2)->textContent);

            if (
                $name === ''
                || !ctype_digit($levelText)
            ) {
                continue;
            }

            $classImages = $xpath->query(
                './/img',
                $cells->item(3)
            );

            $rankImages = $xpath->query(
                './/img',
                $cells->item(4)
            );

            $classPath = $classImages->length > 0
                ? str_replace(
                    '\\',
                    '/',
                    $classImages->item(0)->getAttribute('src')
                )
                : '';

            $rankPath = $rankImages->length > 0
                ? str_replace(
                    '\\',
                    '/',
                    $rankImages->item(0)->getAttribute('src')
                )
                : '';

            $class = $classPath !== ''
                ? pathinfo(basename($classPath), PATHINFO_FILENAME)
                : 'Não identificada';

            $rankImage = $rankPath !== ''
                ? basename($rankPath)
                : '';

            $pathParts = explode('/', trim($rankPath, '/'));

            $rankCategory = count($pathParts) >= 2
                ? $pathParts[count($pathParts) - 2]
                : 'Não identificada';

            $position++;

            // Cadastra o personagem ou atualiza sua última aparição.
            $saveCharacter->execute([
                ':race' => $race,
                ':name' => $name,
            ]);

            $findCharacter->execute([
                ':race' => $race,
                ':name' => $name,
            ]);

            $character = $findCharacter->fetch();

            if (!$character) {
                throw new RuntimeException(
                    'Não foi possível recuperar o personagem: ' . $name
                );
            }

            // Salva o histórico do dia ou atualiza a coleta de hoje.
            $saveSnapshot->execute([
                ':character_id' => $character['id'],
                ':snapshot_date' => $date,
                ':position' => $position,
                ':level' => (int) $levelText,
                ':title' => $title !== '' ? $title : null,
                ':class' => $class,
                ':rank_category' => $rankCategory,
                ':rank_image' => $rankImage,
            ]);

            $charactersFound++;
            $snapshotsSaved++;
            $raceCounts[$race]++;
        }
    }

    if ($charactersFound === 0) {
        throw new RuntimeException(
            'Nenhum personagem válido foi encontrado. Nada deve ser gravado.'
        );
    }

    $pdo->commit();

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(500);

    exit(
        'A coleta falhou e a transação foi desfeita. '
        . 'Detalhe: '
        . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
    );
}

libxml_clear_errors();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coleta concluída | UmbraStats</title>
    <style>
        body {
            margin: 0;
            padding: 30px;
            background: #0b0b10;
            color: #eee;
            font-family: Arial, sans-serif;
        }

        .panel {
            max-width: 650px;
            padding: 24px;
            background: #171720;
            border: 1px solid #30303b;
            border-radius: 12px;
        }

        .success {
            color: #58d6a0;
        }

        li {
            margin: 12px 0;
        }

        a {
            color: #ff6474;
        }
    </style>
</head>
<body>
    <div class="panel">
        <h1>UmbraStats</h1>
        <h2 class="success">Coleta concluída!</h2>

        <p>Data da coleta: <?= htmlspecialchars($date) ?></p>

        <ul>
            <li>
                Personagens processados:
                <strong><?= $charactersFound ?></strong>
            </li>
            <li>
                Slayers: <strong><?= $raceCounts['Slayers'] ?></strong>
            </li>
            <li>
                Ousters: <strong><?= $raceCounts['Ousters'] ?></strong>
            </li>
            <li>
                Vampires: <strong><?= $raceCounts['Vampires'] ?></strong>
            </li>
            <li>
                Registros diários processados:
                <strong><?= $snapshotsSaved ?></strong>
            </li>
        </ul>

        <p>
            Os registros anteriores foram preservados.
            Uma nova execução no mesmo dia atualiza os registros
            daquele dia, sem criar duplicatas.
        </p>

        <p><a href="index.php">Voltar ao UmbraStats</a></p>
    </div>
</body>
</html>
