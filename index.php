
<?php
$pageTitle = "Power Gamers | UmbraStats";
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Acompanhe a evolução dos personagens do DarkEden Umbra com o UmbraStats.">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="header">
    <a href="index.php" class="logo" aria-label="UmbraStats - Página inicial">
        <span class="logo-icon">U</span>
        <span>UMBRA<span class="logo-highlight">STATS</span></span>
    </a>

    <nav class="nav">
        <a href="#ranking" class="active">Power Gamers</a>
        <a href="#sobre">Sobre</a>
    </nav>

    <a
        href="https://www.dkumbra.com/ranking.php"
        target="_blank"
        rel="noopener noreferrer"
        class="server-link"
    >
        DarkEden Umbra ↗
    </a>
</header>

<main class="container">

    <section class="hero">
        <div class="eyebrow">
            <span class="status-dot"></span>
            DARKEDEN UMBRA
        </div>

        <h1>Power <span>Gamers</span></h1>

        <p class="hero-description">
            Acompanhe a evolução dos personagens,
            descubra quem está crescendo mais rapidamente
            e consulte o histórico de progresso.
        </p>

        <div class="stats">
            <div class="stat-card">
                <span class="stat-label">Personagens</span>
                <strong>--</strong>
                <small>Aguardando coleta</small>
            </div>

            <div class="stat-card">
                <span class="stat-label">Maior evolução</span>
                <strong>--</strong>
                <small>Últimas 24 horas</small>
            </div>

            <div class="stat-card">
                <span class="stat-label">Última coleta</span>
                <strong>--</strong>
                <small>Dados indisponíveis</small>
            </div>
        </div>
    </section>

    <section class="ranking-section" id="ranking">
        <div class="section-heading">
            <div>
                <h2>Ranking de evolução</h2>
                <p>Os personagens que mais progrediram.</p>
            </div>

            <span class="period-badge">ÚLTIMAS 24H</span>
        </div>

        <div class="filters">
            <input
                type="search"
                id="search"
                placeholder="Buscar personagem..."
                aria-label="Buscar personagem"
            >

            <select id="race" aria-label="Filtrar por raça">
                <option value="all">Todas as raças</option>
                <option value="Vampire">Vampire</option>
                <option value="Slayer">Slayer</option>
                <option value="Ouster">Ouster</option>
            </select>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Personagem</th>
                        <th>Raça</th>
                        <th>Nível</th>
                        <th>Progresso</th>
                    </tr>
                </thead>

                <tbody id="ranking-body">
                    <tr data-race="Vampire">
                        <td class="position">01</td>
                        <td>
                            <a href="#" class="player-name"
                               onclick="return false;">BloodKnight</a>
                        </td>
                        <td><span class="race vampire">Vampire</span></td>
                        <td>350</td>
                        <td><span class="progress">+3 níveis</span></td>
                    </tr>

                    <tr data-race="Slayer">
                        <td class="position">02</td>
                        <td>
                            <a href="#" class="player-name"
                               onclick="return false;">HolyAvenger</a>
                        </td>
                        <td><span class="race slayer">Slayer</span></td>
                        <td>342</td>
                        <td><span class="progress">+2 níveis</span></td>
                    </tr>

                    <tr data-race="Ouster">
                        <td class="position">03</td>
                        <td>
                            <a href="#" class="player-name"
                               onclick="return false;">DarkNature</a>
                        </td>
                        <td><span class="race ouster">Ouster</span></td>
                        <td>338</td>
                        <td><span class="progress">+1 nível</span></td>
                    </tr>

                    <tr data-race="Vampire">
                        <td class="position">04</td>
                        <td>
                            <a href="#" class="player-name"
                               onclick="return false;">NightWalker</a>
                        </td>
                        <td><span class="race vampire">Vampire</span></td>
                        <td>330</td>
                        <td><span class="no-progress">Sem alteração</span></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="disclaimer">
            Dados fictícios para demonstração da interface.
            O ranking real será integrado posteriormente.
        </p>
    </section>

    <section class="about" id="sobre">
        <span class="eyebrow">SOBRE O PROJETO</span>
        <h2>A evolução não para.</h2>

        <p>
            O UmbraStats registrará os rankings diariamente
            para construir um histórico de evolução dos
            personagens do DarkEden Umbra.
        </p>
    </section>

</main>

<footer class="footer">
    <span>UMBRA<span class="logo-highlight">STATS</span></span>
    <span>Projeto independente de estatísticas.</span>
</footer>

<script>
const search = document.getElementById('search');
const race = document.getElementById('race');
const rows = document.querySelectorAll('#ranking-body tr');

function filterRanking() {
    const query = search.value.toLowerCase().trim();
    const selectedRace = race.value;

    rows.forEach(row => {
        const name = row.querySelector('.player-name')
            .textContent.toLowerCase();

        const matchesName = name.includes(query);
        const matchesRace = selectedRace === 'all'
            || row.dataset.race === selectedRace;

        row.hidden = !(matchesName && matchesRace);
    });
}

search.addEventListener('input', filterRanking);
race.addEventListener('change', filterRanking);
</script>

</body>
</html>
