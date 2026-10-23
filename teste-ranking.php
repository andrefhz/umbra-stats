
<?php

$url = 'https://www.dkumbra.com/ranking.php';

if (!function_exists('curl_init')) {
    exit('Erro: habilite a extensão cURL no PHP.');
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
    exit('Erro ao acessar o ranking. HTTP: '
        . (int) $status . ' - ' . htmlspecialchars($error));
}

libxml_use_internal_errors(true);

$dom = new DOMDocument();
$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET);

$xpath = new DOMXPath($dom);
$tables = $xpath->query('//table');

$races = ['Slayers', 'Ousters', 'Vampires'];
$characters = [];

foreach ($tables as $tableIndex => $table) {
    $race = $races[$tableIndex] ?? 'Desconhecida';
    $rows = $xpath->query('.//tr', $table);

    foreach ($rows as $row) {
        $cells = $xpath->query('./th | ./td', $row);

        if ($cells->length < 5) {
            continue;
        }

        $name = trim($cells->item(0)->textContent);
        $title = trim($cells->item(1)->textContent);
        $levelText = trim($cells->item(2)->textContent);

        if ($name === '' || !ctype_digit($levelText)) {
            continue;
        }

        $classImage = $xpath->query('.//img', $cells->item(3))->item(0);
        $rankImage = $xpath->query('.//img', $cells->item(4))->item(0);

        $classPath = $classImage
            ? str_replace('\\', '/', $classImage->getAttribute('src'))
            : '';

        $rankPath = $rankImage
            ? str_replace('\\', '/', $rankImage->getAttribute('src'))
            : '';

        $class = $classPath !== ''
            ? pathinfo(basename($classPath), PATHINFO_FILENAME)
            : 'Não identificada';

        $rankCategory = 'Não identificada';
        $rankImageFile = '';

        if ($rankPath !== '') {
            $rankImageFile = basename($rankPath);

            $pathParts = explode('/', trim($rankPath, '/'));

            $rankCategory = count($pathParts) >= 2
                ? $pathParts[count($pathParts) - 2]
                : 'Não identificada';
        }

        $characters[] = [
            'race' => $race,
            'name' => $name,
            'title' => $title !== '' ? $title : '—',
            'level' => (int) $levelText,
            'class' => $class,
            'rank_category' => $rankCategory,
            'rank_image' => $rankImageFile,
        ];
    }
}

libxml_clear_errors();

function escapeHtml($value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

$total = count($characters);
$counts = array_count_values(array_column($characters, 'race'));

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coletor de ranking | UmbraStats</title>

    <style>
        body {
            margin: 0;
            padding: 28px;
            background: #0b0b10;
            color: #eeeeF5;
            font-family: Arial, sans-serif;
        }

        h1 {
            margin-bottom: 8px;
        }

        p {
            color: #aaaab8;
        }

        .stats {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 24px 0;
        }

        .stat {
            min-width: 130px;
            padding: 18px;
            background: #171720;
            border: 1px solid #2b2b37;
            border-radius: 10px;
        }

        .stat strong {
            display: block;
            margin-top: 8px;
            color: #ff5364;
            font-size: 25px;
        }

        .table-wrap {
            overflow-x: auto;
            border: 1px solid #2b2b37;
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            white-space: nowrap;
        }

        th, td {
            padding: 12px 14px;
            border-bottom: 1px solid #292934;
            text-align: left;
        }

        th {
            background: #191923;
            color: #b9b9c8;
        }

        td {
            background: #111118;
        }

        .level {
            color: #ff6474;
            font-weight: bold;
        }

        .success {
            color: #58d6a0;
        }

        @media (max-width: 600px) {
            body {
                padding: 16px;
            }
        }
    </style>
</head>
<body>

    <h1>UmbraStats — Coletor de ranking</h1>
    <p>Prévia dos personagens coletados diretamente do ranking oficial.</p>

    <p class="success">
        Conexão concluída. Nenhum dado foi salvo no banco de dados.
    </p>

    <div class="stats">
        <div class="stat">
            Total de personagens
            <strong><?= $total ?></strong>
        </div>

        <div class="stat">
            Slayers
            <strong><?= $counts['Slayers'] ?? 0 ?></strong>
        </div>

        <div class="stat">
            Ousters
            <strong><?= $counts['Ousters'] ?? 0 ?></strong>
        </div>

        <div class="stat">
            Vampires
            <strong><?= $counts['Vampires'] ?? 0 ?></strong>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Raça</th>
                    <th>Personagem</th>
                    <th>Título</th>
                    <th>Nível</th>
                    <th>Classe</th>
                    <th>Categoria da imagem de rank</th>
                    <th>Arquivo de rank</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($characters as $character): ?>
                    <tr>
                        <td><?= escapeHtml($character['race']) ?></td>
                        <td><?= escapeHtml($character['name']) ?></td>
                        <td><?= escapeHtml($character['title']) ?></td>
                        <td class="level">
                            <?= $character['level'] ?>
                        </td>
                        <td><?= escapeHtml($character['class']) ?></td>
                        <td>
                            <?= escapeHtml($character['rank_category']) ?>
                        </td>
                        <td>
                            <?= escapeHtml($character['rank_image']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total === 0): ?>
        <p>Não foi possível identificar personagens nas tabelas.</p>
    <?php endif; ?>

</body>
</html>
