
<?php
require_once __DIR__ . '/config/database.php';

$racas = [
    'Slayers' => 'Slayers',
    'Ousters' => 'Ousters',
    'Vampires' => 'Vampires',
];

$racaSelecionada = $_GET['raca'] ?? 'Slayers';

if (!array_key_exists($racaSelecionada, $racas)) {
    $racaSelecionada = 'Slayers';
}

$sql = "
    SELECT
        c.name,
        c.race,
        s.position,
        s.level,
        s.title,
        s.class,
        s.rank_image,
        s.snapshot_date
    FROM ranking_snapshots s
    INNER JOIN characters c ON c.id = s.character_id
    WHERE c.race = :race
      AND s.snapshot_date = (
          SELECT MAX(s2.snapshot_date)
          FROM ranking_snapshots s2
          INNER JOIN characters c2 ON c2.id = s2.character_id
          WHERE c2.race = c.race
      )
    ORDER BY s.position ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['race' => $racaSelecionada]);
$personagens = $stmt->fetchAll();

$dataRanking = $personagens[0]['snapshot_date'] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Power Gamers | UmbraStats</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .ranking-container {
            width: min(1100px, 92%);
            margin: 40px auto;
        }

        .ranking-header {
            margin-bottom: 28px;
        }

        .ranking-header h1 {
            margin-bottom: 8px;
        }

        .race-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 24px 0;
        }

        .race-tabs a {
            display: inline-block;
            padding: 10px 18px;
            border: 1px solid #353047;
            border-radius: 8px;
            color: #ddd;
            text-decoration: none;
            background: #17151f;
        }

        .race-tabs a.active {
            background: #7651d6;
            border-color: #7651d6;
            color: #fff;
        }

        .ranking-meta {
            color: #aaa;
            margin-bottom: 16px;
            font-size: 14px;
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 10px;
            border: 1px solid #302b3d;
        }

        .ranking-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 620px;
            background: #15131c;
        }

        .ranking-table th,
        .ranking-table td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid #302b3d;
        }

        .ranking-table th {
            background: #201c2b;
            color: #b9a5ff;
            font-size: 12px;
            text-transform: uppercase;
        }

        .ranking-table td {
            color: #e7e4ee;
        }

        .ranking-table tbody tr:hover {
            background: #211c30;
        }

        .ranking-table tbody tr:last-child td {
            border-bottom: none;
        }

        .position {
            color: #b9a5ff !important;
            font-weight: bold;
        }

        .player-name {
            font-weight: 600;
        }

        .rank-icon {
            width: 28px;
            height: 28px;
            object-fit: contain;
            vertical-align: middle;
            margin-right: 8px;
        }

        .empty-ranking {
            padding: 30px;
            text-align: center;
            color: #bbb;
        }

        @media (max-width: 600px) {
            .ranking-container {
                margin: 24px auto;
            }

            .ranking-table th,
            .ranking-table td {
                padding: 12px;
            }
        }
    </style>
</head>
<body>
    <main class="ranking-container">
        <header class="ranking-header">
            <h1>Power Gamers</h1>
            <p>Ranking dos principais jogadores de DarkEden Umbra.</p>
        </header>

        <nav class="race-tabs" aria-label="Selecionar raça">
            <?php foreach ($racas as $chave => $nome): ?>
                <a
                    href="?raca=<?= htmlspecialchars($chave) ?>"
                    class="<?= $racaSelecionada === $chave ? 'active' : '' ?>"
                >
                    <?= htmlspecialchars($nome) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php if ($dataRanking): ?>
            <p class="ranking-meta">
                Última coleta:
                <?= date('d/m/Y', strtotime($dataRanking)) ?>
                · <?= count($personagens) ?> jogadores
            </p>
        <?php endif; ?>

        <div class="table-wrapper">
            <?php if ($personagens): ?>
                <table class="ranking-table">
                    <thead>
                        <tr>
                            <th>Pos.</th>
                            <th>Jogador</th>
                            <th>Nível</th>
                            <th>Classe</th>
                            <th>Título</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($personagens as $personagem): ?>
                            <tr>
                                <td class="position">
                                    <?= (int) $personagem['position'] ?>
                                </td>
                                <td class="player-name">
                                    <?= htmlspecialchars($personagem['name']) ?>
                                </td>
                                <td><?= (int) $personagem['level'] ?></td>
                                <td>
                                    <?php if (!empty($personagem['rank_image'])): ?>
                                        <img
                                            class="rank-icon"
                                            src="https://www.dkumbra.com/<?= htmlspecialchars(ltrim($personagem['rank_image'], '/')) ?>"
                                            alt=""
                                            loading="lazy"
                                        >
                                    <?php endif; ?>
                                    <?= htmlspecialchars($personagem['class']) ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($personagem['title'] ?? '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-ranking">
                    Nenhum jogador encontrado para esta raça.
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
