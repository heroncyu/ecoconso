<?php
declare(strict_types=1);
require_once 'includes/functions.inc.php';

// 1. RÉCUPÉRATION ET SÉCURISATION DES DONNÉES ENTRANTES
$mode = (isset($_GET['mode']) && !empty($_GET['mode'])) ? htmlspecialchars(trim($_GET['mode'])) : 'insee'; 
$inseeCible = (isset($_GET['insee']) && !empty($_GET['insee'])) ? htmlspecialchars(trim($_GET['insee'])) : ''; 
$dept = (isset($_GET['dept']) && !empty($_GET['dept'])) ? htmlspecialchars(trim($_GET['dept'])) : ''; 

$latCible = isset($_GET['lat']) ? floatval($_GET['lat']) : 0.0;
$lonCible = isset($_GET['lon']) ? floatval($_GET['lon']) : 0.0;
$rayon = (isset($_GET['rayon']) && !empty($_GET['rayon'])) ? intval($_GET['rayon']) : 15;
$carburantFiltre = (isset($_GET['carburant']) && !empty($_GET['carburant'])) ? htmlspecialchars(trim($_GET['carburant'])) : 'tout';
$page = (isset($_GET['page']) && !empty($_GET['page'])) ? intval($_GET['page']) : 1;
$limiteParPage = 25;

$nomVilleTrouvee = "INCONNU";
$cpCible = "";

// 2. LOGIQUE DE LOCALISATION (INSEE -> COORDONNÉES)

if ($mode === 'insee' && !empty($inseeCible)) {
   $dept = substr($inseeCible, 0, 2);
    
    
    $coords = getCoordonneesINSEE($inseeCible);
    $latCible = $coords['lat'];
    $lonCible = $coords['lon'];
    $nomVilleTrouvee = $coords['nomVille'];
    $cpCible = $coords['cp'];
}


if ($mode === 'proximite') {
    
    if ($latCible === 0.0 && $lonCible === 0.0) {
        $ip = $_SERVER['REMOTE_ADDR'];
    
        $xml = @simplexml_load_file('http://ip-api.com/xml/'.$ip);
        
        if ($xml && (string)$xml->status === 'success') {
            $latCible = (float)$xml->lat;
            $lonCible = (float)$xml->lon;
            $cpXml = (string)$xml->zip;
            $nomVilleTrouvee = (string)$xml->city;
            
            $dept = substr($cpXml, 0, 2);
            if ($dept === '20') $dept = '2A'; 
        } else {
            $nomVilleTrouvee = "Erreur de géolocalisation";
        }
    } else {
        
        $nomVilleTrouvee = (isset($_GET['ville']) && !empty($_GET['ville'])) ? htmlspecialchars(trim($_GET['ville'])) : "Position GPS";
    }
    
}

// 3. SAUVEGARDE DES COOKIES ET STATISTIQUES
if ($mode === 'insee' && !empty($cpCible)) {

    $cheminCookie =  '/'; 
    $expiration = time() + (86400 * 30); // 30 jours
    
    
    setcookie('dernier_dept', $dept, $expiration, $cheminCookie);
    setcookie('derniere_ville', $inseeCible, $expiration, $cheminCookie);
    setcookie('dernier_nom_ville', $nomVilleTrouvee, $expiration, $cheminCookie);
    setcookie('derniere_lat', (string)$latCible, $expiration, $cheminCookie);
    setcookie('derniere_lon', (string)$lonCible, $expiration, $cheminCookie);
}

if (($mode === 'insee' || $mode === 'dept') && $nomVilleTrouvee !== 'INCONNU') {
    enregistrer_ville($nomVilleTrouvee,$dept,"");
}




// 4. RÉCUPÉRATION ET FILTRAGE DES STATIONS

$toutesStations =[];

if ($mode === 'dept') {
    $toutesStations = getStationsDepartement($dept, false);
} else {
    $deptsAChercher = array_merge([$dept], getDepartementsLimitrophes($dept));
    
    foreach ($deptsAChercher as $d) {
        $stationsDuDept = getStationsDepartement($d, false);
        $toutesStations = array_merge($toutesStations, $stationsDuDept);
    }
}

$stationsDansVille = [];
$stationsProximite =[];

// 5. RÉPARTITION ET CALCULS DES DISTANCES
foreach ($toutesStations as $s) {
    if ($carburantFiltre !== 'tout' && empty($s[$carburantFiltre.'_prix'])) continue;

    
    if ($mode !== 'dept' && $latCible !== 0.0 && $lonCible !== 0.0 && isset($s['geom']['lat']) && isset($s['geom']['lon'])) {
        $s['distance_km'] = calculerDistance($latCible, $lonCible, (float)$s['geom']['lat'], (float)$s['geom']['lon']);
    } else {
        $s['distance_km'] = 999;
    }

    if ($mode === 'dept') {
        $stationsProximite[] = $s; 
    } elseif ($mode === 'insee' || $mode === 'proximite') {
        
        if (($s['cp'] ?? '') === $cpCible) {
            $stationsDansVille[] = $s;
        } elseif ($s['distance_km'] <= $rayon) {
            $stationsProximite[] = $s;
        }
    }
}


trierStations($stationsDansVille, $carburantFiltre);
trierStations($stationsProximite, $carburantFiltre);

$listeFinale = ($mode === 'insee' || $mode === 'proximite') ? array_merge($stationsDansVille, $stationsProximite) : $stationsProximite;
$totalStations = count($listeFinale);
$stationsAAfficher = array_slice($listeFinale, 0, $page * $limiteParPage);


$page_title = "Prix Carburants - $nomVilleTrouvee";
require_once 'includes/header.inc.php'; 
?>

<div class="container-resultats">

    <section aria-labelledby="titre-filtres">
        <h2 id="titre-filtres" style="display:none;">Filtres de recherche</h2>
        
        <form action="resultats.php" method="GET" class="filtres-bar">
            <fieldset>
                <legend>Affiner les résultats</legend>
                
                <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>" />
                <input type="hidden" name="dept" value="<?= htmlspecialchars($dept) ?>" />
                <?php if ($mode === 'insee'): ?>
                    <input type="hidden" name="insee" value="<?= htmlspecialchars($inseeCible) ?>" />
                <?php endif; ?>
                <?php if ($mode === 'proximite'): ?>
                    <input type="hidden" name="ville" value="<?= htmlspecialchars($nomVilleTrouvee) ?>" />
                <?php endif; ?>
                <input type="hidden" name="lat" value="<?= htmlspecialchars(strval($latCible)) ?>" />
                <input type="hidden" name="lon" value="<?= htmlspecialchars(strval($lonCible)) ?>" />

                <div class="boutons-carburant">
                    <?php 
                    $listeCarbs =['tout', 'gazole', 'sp95', 'e10', 'sp98', 'e85', 'gplc'];
                    foreach($listeCarbs as $c): 
                        $classe = ($carburantFiltre === $c) ? 'active' : '';
                    ?>
                        <button type="submit" name="carburant" value="<?= $c ?>" class="<?= $classe ?>">
                            <?= strtoupper($c) ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <?php if ($mode !== 'dept'): ?>
                <div class="slider-rayon" style="margin-top:15px;">
                    <label for="rayon">Rayon de recherche (km) : </label>
                    <output id="valeur-rayon"><?= $rayon ?></output>
                    <input type="range" id="rayon" name="rayon" min="1" max="50" value="<?= $rayon ?>" oninput="document.getElementById('valeur-rayon').value = this.value" />
                    <button type="submit">Actualiser</button>
                </div>
                <?php endif; ?>
            </fieldset>
        </form>
    </section>

    <section aria-labelledby="titre-resultats" class="liste-resultats">
        <h2 id="titre-resultats">Résultats trouvés : <?= $totalStations ?></h2>
        
        <?php if ($mode === 'insee' && count($stationsDansVille) > 0): ?>
            <h3 class="section-titre">Stations à <?= htmlspecialchars($nomVilleTrouvee) ?> (<?= htmlspecialchars($cpCible) ?>)</h3>
        <?php endif; ?>

        <div class="grille-stations">
            <?php
            $affichageProximiteCommence = false;
            foreach ($stationsAAfficher as $index => $station) {
                if (($mode === 'insee' || $mode === 'proximite') && !$affichageProximiteCommence && !in_array($station, $stationsDansVille, true)) {
                    echo "<h3 class='section-titre'>Stations à proximité (Rayon de {$rayon}km)</h3>\n";
                    $affichageProximiteCommence = true;
                }
                echo genererHtmlStation($station, $index, $carburantFiltre) . "\n";
            }
            ?>
        </div>

        <?php if (($page * $limiteParPage) < $totalStations): ?>
            <form action="resultats.php#station-<?= ($page * $limiteParPage) - 1 ?>" method="GET" class="form-voir-plus" style="text-align:center; margin-top:20px;">
                <fieldset style="border:none; padding:0;">
                    <legend class="sr-only">Voir plus de stations</legend>
                    <input type="hidden" name="mode" value="<?= htmlspecialchars($mode) ?>" />
                    <input type="hidden" name="dept" value="<?= htmlspecialchars($dept) ?>" />
                    <?php if ($mode === 'insee'): ?>
                        <input type="hidden" name="insee" value="<?= htmlspecialchars($inseeCible) ?>" />
                    <?php endif; ?>
                    <?php if ($mode === 'proximite'): ?>
                        <input type="hidden" name="ville" value="<?= htmlspecialchars($nomVilleTrouvee) ?>" />
                    <?php endif; ?>
                    <input type="hidden" name="lat" value="<?= htmlspecialchars(strval($latCible)) ?>" />
                    <input type="hidden" name="lon" value="<?= htmlspecialchars(strval($lonCible)) ?>" />
                    <input type="hidden" name="carburant" value="<?= htmlspecialchars($carburantFiltre) ?>" />
                    <input type="hidden" name="rayon" value="<?= htmlspecialchars(strval($rayon)) ?>" />
                    
                    <input type="hidden" name="page" value="<?= $page + 1 ?>" />
                    
                    <button type="submit" class="btn-voir-plus">Voir plus de stations</button>
                </fieldset>
            </form>
        <?php endif; ?>
    </section>
</div>

<?php require("./includes/footer.inc.php"); ?>