<?php
declare(strict_types=1);
require_once "includes/functions.inc.php";

$page_title  = "EcoConso — Statistiques";
$page_active = "stats";

$top_villes = get_top_villes(20);
$max_nb = !empty($top_villes) ? $top_villes[0]['nb'] : 1;
$evolution = get_donnees_evolution(150);

$datesJs  = json_encode($evolution['dates'] ??[]);
$gazoleJs = json_encode($evolution['gazole'] ??[]);
$sp95Js   = json_encode($evolution['sp95'] ??[]);
$e10Js    = json_encode($evolution['e10'] ?? []);
$sp98Js   = json_encode($evolution['sp98'] ?? []);
$e85Js    = json_encode($evolution['e85'] ??[]);
$gplcJs   = json_encode($evolution['gplc'] ??[]);
$labelsJs = json_encode(array_column($top_villes, 'ville'));
$dataJs = json_encode(array_column($top_villes, 'nb'));

$prix = get_prix_moyens();

require_once "includes/header.inc.php";
?>

<section class="bloc-recherche">
    <h2>Statistiques d'utilisation
        <span class="sous-titre-h2">Consultation en temps réel des données enregistrées sur le serveur</span>
    </h2>
    <div class="ligne-lateral" id="villes">
        <div class="carte">
            <div class="titre-carte">
                <span class="material-icons">people</span> Visites <span>totales</span>
            </div>
            <p class="stat-gros-chiffre"><?php echo number_format((int)file_get_contents(__DIR__ . '/data/hits.txt'), 0, ',', ' '); ?></p>
            <p class="stat-sous-texte">chargements de pages depuis l'ouverture du site</p>
        </div>
        <div class="carte">
            <div class="titre-carte">
                <span class="material-icons">search</span> Recherches <span>effectuées</span>
            </div>
            <p class="stat-gros-chiffre"><?php echo number_format(array_sum(array_column($top_villes, 'nb')), 0, ',', ' '); ?></p>
            <p class="stat-sous-texte">stations recherchées par les utilisateurs</p>
        </div>
    </div>
</section>

<div class="carte">
    <div class="titre-carte">
        <span class="material-icons">bar_chart</span> Villes les plus <span>recherchées</span>
    </div>
    <?php if (empty($top_villes)): ?>
        <div class="indispo-prix">
            <span class="material-icons">inbox</span>
            <p>Aucune donnée enregistrée pour le moment</p>
            <span class="sous-titre-h2">Les recherches apparaîtront ici au fur et à mesure</span>
        </div>
    <?php else: ?>
        <div style="height: 500px; width: 100%; border-top: 1px solid #E2DAD3; padding-top: 20px;">
            <canvas id="graphVilles"></canvas>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('graphVilles').getContext('2d');
            
            const estThemeNuit = document.body.style.backgroundColor === 'rgb(26, 28, 30)' || window.location.href.includes('nuit');
            const couleurTexte = estThemeNuit ? '#E2E2E2' : '#6B6560';
            const couleurGrille = estThemeNuit ? '#3E4247' : '#E2DAD3';

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= $labelsJs ?>,
                    datasets:[{
                        label: 'Nombre de recherches',
                        data: <?= $dataJs ?>,
                        backgroundColor: '#C44D00',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { 
                                stepSize: 1,
                                color: couleurTexte 
                            },
                            grid: { color: couleurGrille }
                        },
                        x: {
                            ticks: { color: couleurTexte },
                            grid: { display: false }
                        }
                    }
                }
            });
        });
        </script>
    <?php endif; ?>
</div>

<div class="carte" style="margin-top: 20px;" id="prix">
    <div class="titre-carte">
        <span class="material-icons">show_chart</span> Évolution des prix
    </div>
    
    <div style="position: relative; height: 580px; width: 800px; margin: 0 auto; padding-top: 15px;">
        <canvas id="graphEvolution" aria-label="Graphique de l'évolution des prix" role="img"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        
        
        const rawLabels = <?= $datesJs ?>;
        const rawGazole = <?= $gazoleJs ?>;
        const rawSP95 = <?= $sp95Js ?>;
        const rawE10 = <?= $e10Js ?>;
        const rawSP98 = <?= $sp98Js ?>;
        const rawE85 = <?= $e85Js ?>;
        const rawGPLc = <?= $gplcJs ?>;

        
        const filterEvery3 = (arr) => arr.filter((_, index) => index % 3 === 0);

        
        const formatLabels = filterEvery3(rawLabels).map(dateStr => {
            const parts = dateStr.split('-');
            return parts[2] + '/' + parts[1];
        });

        
        const estThemeNuit = document.body.style.backgroundColor === 'rgb(26, 28, 30)' || window.location.href.includes('nuit');
        const couleurTexte = estThemeNuit ? '#E2E2E2' : '#6B6560';
        const couleurGrille = estThemeNuit ? '#3E4247' : '#E2DAD3';

        
        const ctx = document.getElementById('graphEvolution').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: formatLabels,
                datasets:[
                    { label: 'Gazole', data: filterEvery3(rawGazole), borderColor: '#E67E22', backgroundColor: '#E67E22', tension: 0, pointRadius: 0, pointHoverRadius: 5, pointHitRadius: 15, borderWidth: 2 },
                    { label: 'SP95', data: filterEvery3(rawSP95), borderColor: '#2ECC71', backgroundColor: '#2ECC71', tension: 0, pointRadius: 0, pointHoverRadius: 5, pointHitRadius: 15, borderWidth: 2 },
                    { label: 'E10', data: filterEvery3(rawE10), borderColor: '#27AE60', backgroundColor: '#27AE60', tension: 0, pointRadius: 0, pointHoverRadius: 5, pointHitRadius: 15, borderWidth: 2 },
                    { label: 'SP98', data: filterEvery3(rawSP98), borderColor: '#1E8449', backgroundColor: '#1E8449', tension: 0, pointRadius: 0, pointHoverRadius: 5, pointHitRadius: 15, borderWidth: 2 },
                    { label: 'E85', data: filterEvery3(rawE85), borderColor: '#3498DB', backgroundColor: '#3498DB', tension: 0, pointRadius: 0, pointHoverRadius: 5, pointHitRadius: 15, borderWidth: 2 },
                    { label: 'GPLc', data: filterEvery3(rawGPLc), borderColor: '#00BCD4', backgroundColor: '#00BCD4', tension: 0, pointRadius: 0, pointHoverRadius: 5, pointHitRadius: 15, borderWidth: 2 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { 
                        position: 'bottom', 
                        labels: { color: couleurTexte, boxWidth: 8 } 
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) { return context.dataset.label + ' : ' + context.parsed.y.toFixed(3) + ' €'; }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: { color: couleurTexte },
                        grid: { color: couleurGrille }
                    },
                    x: {
                        ticks: { color: couleurTexte },
                        grid: { display: false }
                    }
                }
            }
        });
    });
    </script>
</div>

<div class="carte" id="plein">
    <div class="titre-carte">
        <span class="material-icons">opacity</span> Coût du plein <span>estimé</span>
    </div>
    <?php if (empty($prix)): ?>
        <div class="indispo-prix">
            <span class="material-icons">cloud_off</span>
            <p>Données temporairement indisponibles</p>
        </div>
    <?php else: ?>
        <table class="tableau-plein">
            <thead>
                <tr>
                    <th>Réservoir</th>
                    <?php foreach ($prix as $p): ?>
                    <th><?php echo $p['label']; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                $reservoirs = ['30L' => 30, '50L' => 50, '70L' => 70];
                foreach ($reservoirs as $label => $litres):
                ?>
                <tr>
                    <td class="reservoir-label"><?php echo $label; ?></td>
                    <?php foreach ($prix as $p):
                        $prix_num = (float) str_replace([',', ' €'], ['.', ''], $p['val']);
                        $total    = number_format($prix_num * $litres, 2, ',', '');
                    ?>
                    <td><?php echo $total; ?> €</td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="stat-sous-texte">Estimations basées sur les prix moyens nationaux du moment.</p>
    <?php endif; ?>
</div>

<?php require_once "includes/footer.inc.php"; ?>