<?php
declare(strict_types=1);

$region_selectionnee = $_GET['region'] ?? '';
$dept_selectionne = $_GET['dept'] ?? '';
$ville_selectionnee = $_GET['ville'] ?? '';


if ($ville_selectionnee !== '' && $dept_selectionne !== '' && $region_selectionnee !== '') {
    setcookie('derniere_ville', $ville_selectionnee, time() + 2592000, '/');
    setcookie('dernier_dept', $dept_selectionne, time() + 2592000, '/');
    setcookie('derniere_region', $region_selectionnee, time() + 2592000, '/');
    header('Location: resultats.php?ville=' . urlencode($ville_selectionnee)
         . '&dept=' . urlencode($dept_selectionne)
         . '&region=' . urlencode($region_selectionnee));
    exit;
}

$page_title = "EcoConso — Accueil";
$page_active = "accueil";

require_once "includes/functions.inc.php";


$cookie_ville = $_COOKIE['derniere_ville'] ?? null;
$cookie_dept = $_COOKIE['dernier_dept'] ?? null;
$cookie_region = $_COOKIE['derniere_region'] ?? null;

$regions = get_regions_td10();
$depts = ($region_selectionnee !== '') ? get_departements_td10($region_selectionnee) : [];
$villes = ($dept_selectionne !== '') ? get_villes_par_dept($dept_selectionne) :[];

$nom_region_active = $regions[$region_selectionnee] ?? '';

$top_villes = get_top_villes(3);

require_once "includes/header.inc.php";
?>

<?php 
$cookie_cp = $_COOKIE['derniere_ville'] ?? null;
$cookie_dept = $_COOKIE['dernier_dept'] ?? null;
$cookie_nom = $_COOKIE['dernier_nom_ville'] ?? null;
$cookie_lat = $_COOKIE['derniere_lat'] ?? 0;
$cookie_lon = $_COOKIE['derniere_lon'] ?? 0;
if ($cookie_cp && $cookie_dept): ?>
<div class="barre-resume">
    <span>
        <span class="material-icons">history</span>
        Dernière recherche : <?= htmlspecialchars($cookie_nom ?? $cookie_cp) ?>
    </span>
    <a href="resultats.php?mode=insee&amp;insee=<?= urlencode($cookie_cp) ?>&amp;dept=<?= urlencode($cookie_dept) ?>&amp;lat=<?= urlencode((string)$cookie_lat) ?>&amp;lon=<?= urlencode((string)$cookie_lon) ?>"
       class="barre-resume-btn">Reprendre →</a>
</div>
<?php endif; ?>

<section class="bloc-recherche">
    <h2>Trouvez le carburant le moins cher près de chez vous !
        <span class="sous-titre-h2">Cliquez sur votre région, puis choisissez département et ville</span>
    </h2>

    <div class="ligne-carte-form">
        
        <div class="conteneur-carte">
            <?php if ($nom_region_active !== ''): ?>
            <div class="region-active">
                <span class="material-icons">place</span>
                <?php echo htmlspecialchars($nom_region_active); ?>
            </div>
            <?php endif; ?>

            <img src="images/carte-france.webp" usemap="#image-map" alt="Carte des régions de France" fetchpriority="high"/>

            <map name="image-map">
                <area alt="Hauts-de-France"          title="Hauts-de-France"          href="index.php?region=32" coords="357,4,406,32,450,59,447,125,422,167,400,149,340,143,336,99,324,70,327,20" shape="poly"/>
                <area alt="Grand Est"                 title="Grand Est"                 href="index.php?region=44" coords="464,88,571,126,659,165,627,270,595,253,547,240,515,266,489,237,446,239,423,205,435,155" shape="poly"/>
                <area alt="Bourgogne-Franche-Comté"   title="Bourgogne-Franche-Comté"   href="index.php?region=27" coords="405,226,396,279,412,333,445,337,452,373,493,355,513,350,552,359,602,278,570,250,510,275" shape="poly"/>
                <area alt="Auvergne-Rhône-Alpes"      title="Auvergne-Rhône-Alpes"      href="index.php?region=84" coords="392,338,442,377,497,381,542,375,584,362,597,442,533,484,481,512,418,467,365,473" shape="poly"/>
                <area alt="Provence-Alpes-Côte d'Azur" title="Provence-Alpes-Côte d'Azur" href="index.php?region=93" coords="578,464,594,510,625,549,583,579,540,593,492,577,466,572,490,532" shape="poly"/>
                <area alt="Corse"                     title="Corse"                     href="index.php?region=94" coords="713,607,676,636,683,685,702,709,716,659" shape="poly"/>
                <area alt="Occitanie"                 title="Occitanie"                 href="index.php?region=76" coords="239,618,244,548,327,474,431,484,481,543,416,586,378,643" shape="poly"/>
                <area alt="Nouvelle-Aquitaine"        title="Nouvelle-Aquitaine"        href="index.php?region=75" coords="216,615,164,577,190,488,193,397,213,365,249,309,301,366,364,371,353,445,277,511,231,542" shape="poly"/>
                <area alt="Centre-Val de Loire"       title="Centre-Val de Loire"       href="index.php?region=24" coords="322,176,296,207,287,253,255,304,316,353,380,336,388,276,392,232,339,221" shape="poly"/>
                <area alt="Pays de la Loire"          title="Pays de la Loire"          href="index.php?region=52" coords="209,358,162,352,125,287,192,254,203,208,247,212,281,230,251,273,201,320" shape="poly"/>
                <area alt="Bretagne"                  title="Bretagne"                  href="index.php?region=53" coords="6,198,83,174,153,196,186,235,142,256,100,266,49,243,23,235" shape="poly"/>
                <area alt="Normandie"                 title="Normandie"                 href="index.php?region=28" coords="156,107,181,194,237,194,275,211,307,166,326,139,317,86,252,133" shape="poly"/>
                <area alt="Île-de-France"             title="Île-de-France"             href="index.php?region=11" coords="338,150,399,158,419,192,394,220,371,216,346,215,334,193,325,165" shape="poly"/>
            </map>
        </div>

        <div class="bloc-formulaire">
            <!-- 1. RÉGION -->
            <form method="GET" action="index.php">
                <div class="groupe-champ">
                    <label for="region">Région</label>
                    <select name="region" id="region" onchange="this.form.submit()">
                        <option value="">— Sélectionnez une région —</option>
                        <?php foreach ($regions as $code => $nom): ?>
                        <option value="<?= htmlspecialchars((string)$code) ?>" <?= ($region_selectionnee === (string)$code) ? 'selected="selected"' : '' ?>>
                            <?= htmlspecialchars($nom) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>

            <!-- 2. DÉPARTEMENT -->
            <?php if ($region_selectionnee !== '' && !empty($depts)): ?>
            <form method="GET" action="index.php">
                <input type="hidden" name="region" value="<?= htmlspecialchars($region_selectionnee) ?>"/>
                <div class="groupe-champ">
                    <label for="dept">Département</label>
                    <select name="dept" id="dept" onchange="this.form.submit()">
                        <option value="">— Sélectionnez un département —</option>
                        <?php foreach ($depts as $d): ?>
                        <option value="<?= htmlspecialchars($d['numero']) ?>" <?= ($dept_selectionne === $d['numero']) ? 'selected="selected"' : '' ?>>
                            <?= htmlspecialchars($d['nom']) ?> (<?= htmlspecialchars($d['numero']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
            
            <!-- BOUTON : CHERCHER TOUT LE DÉPARTEMENT -->
            <?php if ($dept_selectionne !== ''): ?>
            <form method="GET" action="resultats.php" style="margin-top: 10px;">
                <input type="hidden" name="mode" value="dept"/>
                <input type="hidden" name="dept" value="<?= htmlspecialchars($dept_selectionne) ?>"/>
                <button type="submit" class="btn-recherche">
                    <span class="material-icons">map</span> Voir tout le <?= htmlspecialchars($dept_selectionne) ?>
                </button>
            </form>
            <?php endif; ?>

            <?php else: ?>
            <div class="groupe-champ">
                <label for="dept_disabled">Département</label>
                <select id="dept_disabled" disabled="disabled"><option>— Choisissez d'abord une région —</option></select>
            </div>
            <?php endif; ?>

            <!-- 3. VILLE -->
            <?php if ($dept_selectionne !== '' && !empty($villes)): ?>
            <form method="GET" action="resultats.php">
                <input type="hidden" name="mode" value="insee"/> 
                <input type="hidden" name="dept" value="<?= htmlspecialchars($dept_selectionne) ?>"/>
                
                <div class="groupe-champ">
                    <label for="insee">Ville</label>
                    <select name="insee" id="insee" required="required">
                        <option value="">— Sélectionnez une ville —</option>
                        <?php foreach ($villes as $ville): ?>
                        <option value="<?= htmlspecialchars($ville['insee']) ?>">
                            <?= htmlspecialchars($ville['nom'] . ' (' . $ville['cp'] . ')') ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn-recherche">
                    <span class="material-icons">search</span> Rechercher par Ville
                </button>
            </form>
            <?php else: ?>
            <div class="groupe-champ">
                <label for="ville_disabled">Ville</label>
                <select id="ville_disabled" disabled="disabled"><option>— Choisissez d'abord un département —</option></select>
            </div>
            <?php endif; ?>

            <?php if ($region_selectionnee !== ''): ?>
            <a href="index.php" class="btn-reinit" style="display:block; margin-top:15px;">↩ Recommencer la sélection</a>
            <?php endif; ?>

            <form method="GET" action="resultats.php" class="form-geoloc">
                <button type="submit" class="btn-recherche">
                    <input type="hidden" name="mode" value="proximite"/> 
                    <span class="material-icons">my_location</span> Me localiser (Automatique)
                </button>
            </form>
        </div>
    </div>
</section>

<div class="ligne-lateral">
    <div class="carte">
        <div class="titre-carte">
            <span class="material-icons">trending_up</span> Prix moyens <span>France</span>
        </div>
        <div class="mini-barres">
            <?php $prix = get_prix_moyens(); ?>
            <?php if (!empty($prix)): ?>
                <?php foreach ($prix as $p): ?>
                <div class="ligne-barre">
                    <span class="etiquette-barre"><?php echo $p['label']; ?></span>
                    <div class="piste-barre">
                        <div class="remplissage" style="width:<?php echo $p['pct']; ?>%; background:<?php echo $p['color']; ?>"></div>
                    </div>
                    <span class="valeur-barre">
                        <?php echo $p['val']; ?>
                        <span class="<?php echo $p['tendance_class']; ?>" style="font-size: 0.75rem;">
                        <?php echo $p['tendance_icone'] . ' ' . $p['tendance_txt']; ?>
                    </span>
                    </span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="indispo-prix">
                    <span class="material-icons">cloud_off</span>
                    <p>Données temporairement indisponibles</p>
                    <span class="sous-titre-h2">Réessayez dans quelques instants</span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="carte">
        <div class="titre-carte">
            <span class="material-icons">location_city</span> Villes les plus <span>cherchées</span>
        </div>
        <?php if (empty($top_villes)): ?>
        <div class="indispo-prix">
            <span class="material-icons">inbox</span>
            <p>Aucune donnée enregistrée pour le moment</p>
        </div>
        <?php else: ?>
        <ul class="liste-villes">
            <?php foreach ($top_villes as $i => $v): ?>
            <li class="item-ville">
                <span class="rang-ville <?php echo $i > 0 ? 'r' . ($i + 1) : ''; ?>">
                    <?php echo $i + 1; ?>
                </span>
                <span class="nom-ville"><?php echo htmlspecialchars($v['ville']); ?></span>
                <span class="nb-visites"><?php echo number_format($v['nb'], 0, ',', ' '); ?> rech.</span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <a href="stats.php" class="lien-plus">Voir toutes les stats →</a>
    </div>
</div>

<?php require_once "includes/footer.inc.php"; ?>