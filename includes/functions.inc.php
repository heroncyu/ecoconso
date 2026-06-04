<?php
/**
 * @author EL HAJAM Ayoub, HERON Sajid
 * @version 1.2
 * @date Mai 2026
 * @description Fichier regroupant les fonctions principales du projet EcoConso : 
 * gestion du cache, calculs de distance, lectures CSV et appels aux API.
 */

declare(strict_types=1);
define('DATA_DIR', __DIR__ . '/../data/');

date_default_timezone_set('Europe/Paris');

/**
 * Récupère les stations d'un département donné depuis le cache ou l'API.
 * Si le cache a moins de 4 heures, on l'utilise. Sinon, on télécharge les nouvelles données
 * depuis l'API Opendatasoft et on met le cache à jour.
 * 
 * @param string $codeDept Le code du département (ex: "75", "2A")
 * @param bool $forceRefresh Si true, ignore le cache et force un nouvel appel à l'API
 * @return array Un tableau associatif contenant toutes les stations trouvées
 */
function getStationsDepartement(string $codeDept, bool $forceRefresh = false): array {
    $codeDept = str_replace([' ', '-', '_'], '', $codeDept);
    
    $fichierCache = __DIR__ . '/../data/cache/dept_' . $codeDept . '.json';
    $dureeVieCache = 4 * 3600;
    
    
    if (!$forceRefresh && file_exists($fichierCache) && (time() - filemtime($fichierCache)) < $dureeVieCache) {
        return json_decode(file_get_contents($fichierCache), true) ??[];
    }
    
    
    $url = 'https://data.economie.gouv.fr/api/explore/v2.1/catalog/datasets/prix-des-carburants-en-france-flux-instantane-v2/exports/json?where=' . urlencode('code_departement="' . $codeDept . '"');
    $contexte = stream_context_create(['http' =>['method' => 'GET', 'timeout' => 8]]);
    $jsonApi = @file_get_contents($url, false, $contexte);
    
    if ($jsonApi !== false) {
        $stations = json_decode($jsonApi, true);
        if (!empty($stations)) {
            
            if (!is_dir(dirname($fichierCache))) mkdir(dirname($fichierCache), 0777, true);
            file_put_contents($fichierCache, json_encode($stations));
            return $stations;
        }
    }
    
    
    if (file_exists($fichierCache)) {
        return json_decode(file_get_contents($fichierCache), true) ?? [];
    }
    return[];
}

/**
 * Cherche les coordonnées GPS et le vrai nom d'une ville à partir de son code postal.
 * Parcours le fichier clean_postcodes.csv ligne par ligne.
 * 
 * @param string $cpCible Le code INSEE de la ville recherchée (nommé cpCible historiquement)
 * @return array Tableau contenant 'lat', 'lon' et 'nomVille'
 */
function getCoordonneesCP(string $cpCible): array {
    $fichier = "__DIR__ . '/../data/clean_postcodes.csv";
    

    if ($fichier && ($handle = fopen($fichier, "r")) !== false) {
        fgetcsv($handle, 1000, ",",'"','"');
        
        while (($data = fgetcsv($handle, 1000, ",",'"','"')) !== false) {
            $cpCsv = trim($data[2] ?? '');
            
            
            if ($cpCsv === $cpCible) {
                fclose($handle);
                return [
                    'lat' => (float)$data[3], 
                    'lon' => (float)$data[4], 
                    'nomVille' => strtoupper(trim($data[1]))
                ];
            }
        }
        fclose($handle);
    }
    return ['lat' => 0.0, 'lon' => 0.0, 'nomVille' => 'INCONNU'];
}

/**
 * Calcule la distance en kilomètres entre deux points GPS en utilisant la formule de Haversine.
 * 
 * @param float $lat1 Latitude du premier point
 * @param float $lon1 Longitude du premier point
 * @param float $lat2 Latitude du deuxième point
 * @param float $lon2 Longitude du deuxième point
 * @return float La distance calculée en kilomètres
 */
function calculerDistance(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $rayon = 6371; 
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $rayon * $c;
}

/**
 * Trie un tableau de stations par rapport à un carburant précis.
 * Si un carburant est choisi, le tri se fait du moins cher au plus cher.
 * Sinon (carburantFiltre = 'tout'), le tri se fait par distance du plus proche au plus loin.
 * Modifie directement le tableau passé en paramètre (passage par référence).
 * 
 * @param array &$stations Le tableau de stations à trier
 * @param string $carburantFiltre Le nom du carburant (ex: 'gazole') ou 'tout'
 * @return void
 */
function trierStations(array &$stations, string $carburantFiltre) {
    usort($stations, function($a, $b) use ($carburantFiltre) {
        if ($carburantFiltre !== 'tout') {
            $prixA = $a[$carburantFiltre.'_prix'] ?? 999;
            $prixB = $b[$carburantFiltre.'_prix'] ?? 999;
            if ($prixA !== $prixB) { return $prixA <=> $prixB; }
        }
        $distA = $a['distance_km'] ?? 999;
        $distB = $b['distance_km'] ?? 999;
        return $distA <=> $distB;
    });
}

/**
 * Analyse les horaires d'une station pour déterminer si elle est ouverte à l'heure actuelle.
 * Vérifie d'abord s'il y a un automate 24/24. Sinon, compare l'heure locale avec les tranches horaires.
 * 
 * @param array $station Le tableau contenant les infos d'une station spécifique
 * @return string Le code HTML du badge à afficher (Ouvert, Fermé, 24/24 ou Inconnu)
 */
function getBadgeOuverture(array $station): string {
    if (($station['horaires_automate_24_24'] ?? 'Non') === 'Oui') {
        return "<span class='badge-ouvert'>🟢 24h/24</span>";
    }

    $horaires = $station['horaires_jour'] ?? '';
    if (empty($horaires)) {
        return "<span class='badge-horaires'>🕒 Horaires inconnus</span>";
    }

    $joursFr =[1=>'Lundi', 2=>'Mardi', 3=>'Mercredi', 4=>'Jeudi', 5=>'Vendredi', 6=>'Samedi', 7=>'Dimanche'];
    $jourActuel = $joursFr[(int)date('N')];
    $heureActuelle = date('H.i');

    $tranches = explode(',', $horaires);

    foreach ($tranches as $tranche) {
        $tranche = trim($tranche);
        
        if (str_starts_with($tranche, $jourActuel)) {
            $heures = str_replace($jourActuel, '', $tranche);
            
            $bornes = explode('-', $heures);
            
            if (count($bornes) === 2) {
                $debut = trim($bornes[0]);
                $fin = trim($bornes[1]);
                
                if ($heureActuelle >= $debut && $heureActuelle <= $fin) {
                    return "<span class='badge-ouvert'>🟢 Ouvert actuellement</span>";
                }
            }
        }
    }

    return "<span class='badge-ferme'>🔴 Fermé actuellement</span>";
}

/**
 * Génère tout le code HTML (la carte/fiche) pour afficher une station sur la page des résultats.
 * Formate les prix, ajoute l'icône Google Maps et gère l'affichage du menu déroulant (details).
 * 
 * @param array $station Les données de la station à afficher
 * @param int $index L'index de la station dans la boucle (sert à créer un ID d'ancre unique)
 * @param string $carburantFiltre Permet de n'afficher que le prix du carburant recherché
 * @return string Le code HTML formaté de la station
 */
function genererHtmlStation(array $station, int $index, string $carburantFiltre = 'tout'): string {
    $ville = htmlspecialchars(strtoupper($station['ville'] ?? 'INCONNU'));
    $cp = htmlspecialchars((string)($station['cp'] ?? ''));
    $adresse = htmlspecialchars((string)($station['adresse'] ?? ''));
    
    $distanceHtml = (isset($station['distance_km']) && $station['distance_km'] < 999) 
        ? "<span class='badge-dist'>À " . round($station['distance_km'], 1) . " km</span>" 
        : "";
    
    $badgeOuvert = getBadgeOuverture($station);
    
    $lat = $station['geom']['lat'] ?? '';
    $lon = $station['geom']['lon'] ?? '';
    $lienMaps = "https://www.google.com/maps/search/?api=1&amp;query={$lat},{$lon}";

    $derniereMaj = 'Inconnue';
    $couleurs =['gazole'=>'gazole', 'sp95'=>'sp95', 'e10'=>'e10', 'sp98'=>'sp98', 'e85'=>'e85', 'gplc'=>'gplc'];

    $prixHtml = "<div class='prix-liste'>";
    foreach ($couleurs as $carb => $classeCss) {
        if ($carburantFiltre !== 'tout' && $carb !== $carburantFiltre) { continue; }

        if (isset($station[$carb.'_prix']) && $station[$carb.'_prix'] > 0) {
            $prix = number_format((float)$station[$carb.'_prix'], 3, ',', ' '); 
            $prixHtml .= "
                <div class='prix-item {$classeCss}'>
                    <span class='carb-nom'>" . strtoupper($carb) . "</span> 
                    <div class='ecran-noir'>
                        <span class='carb-valeur police-prix'>{$prix}</span>
                    </div>
                </div>";
            $derniereMaj = date("d/m à H:i", strtotime($station[$carb.'_maj'])); 
        }
    }
    $prixHtml .= "</div>";

    $services = is_array($station['services_service'] ?? '') ? implode(', ', $station['services_service']) : ($station['services_service'] ?? 'Aucun');
    $ruptures = is_array($station['carburants_rupture_definitive'] ?? '') ? implode(', ', $station['carburants_rupture_definitive']) : ($station['carburants_rupture_definitive'] ?? 'Aucune');
    $horaires = htmlspecialchars((string)($station['horaires_jour'] ?? 'Non communiqués'));
    $idAncre = "station-" . $index;

    return "
    <article class='station-card' id='{$idAncre}'>
        <div class='station-gauche'>
            <div class='station-en-tete'>
                <h3>{$ville} ({$cp})</h3>
                {$distanceHtml}
                {$badgeOuvert}
            </div>
            <p class='adresse'>{$adresse}</p>
            <p class='maj'>Dernière mise à jour : {$derniereMaj}</p>
        </div>
        <div class='station-droite'>
            {$prixHtml}
        </div>
        <div class='station-actions'>
            <details>
                <summary>Plus d'informations (Services, Horaires...)</summary>
                <p><span class='texte-gras'>Horaires :</span> {$horaires}</p>
                <p><span class='texte-gras'>Services :</span> " . htmlspecialchars($services) . "</p>
                <p><span class='texte-gras'>Ruptures :</span> " . htmlspecialchars($ruptures) . "</p>
            </details>
            <!-- LE NOUVEAU BOUTON MAPS AVEC IMAGE -->
            <a href='{$lienMaps}' target='_blank' class='btn-maps' title='Voir sur Google Maps'>
                <img src='images/map.webp' alt='Google Maps' class='icon-maps'/>
            </a>
        </div>
    </article>
    ";
}

/**
 * Extrait les régions et leurs départements en croisant les fichiers CSV du TD10.
 * Lit d'abord les régions, puis associe chaque département à sa région correspondante.
 * 
 * @return array Tableau associatif structuré : [ "Nom Region" => [["numero" => "01", "nom" => "Ain"], ... ] ]
 */
function extraireRegionsDepartements(): array
{
    $fichierRegions = __DIR__ . '/../data/v_region_2024.csv';
    $fichierDepartements = __DIR__ . '/../data/v_departement_2024.csv';

    $regions = [];
    $resultat = [];

    
    if (($fp = @fopen($fichierRegions, "r")) !== false) {
        fgetcsv($fp, 1000, ",", "\"", "\\");
        while (($ligne = fgetcsv($fp, 1000, ",", "\"", "\\")) !== false) {
            $codeRegion = $ligne[0];
            $nomRegion = $ligne[5];
            $regions[$codeRegion] = $nomRegion;
            $resultat[$nomRegion] = [];
        }
        fclose($fp);
    }

    
    if (($fp = @fopen($fichierDepartements, "r")) !== false) {
        fgetcsv($fp, 1000, ",", "\"", "\\");
        while (($ligne = fgetcsv($fp, 1000, ",", "\"", "\\")) !== false) {
            $numDep = $ligne[0];
            $codeRegion = $ligne[1];
            $nomDep = $ligne[6];

            if (isset($regions[$codeRegion])) {
                $nomRegion = $regions[$codeRegion];
                $resultat[$nomRegion][] = [
                    "numero" => $numDep,
                    "nom" => $nomDep
                ];
            }
        }
        fclose($fp);
    }
    
    
    ksort($resultat);
    return $resultat;
}

/**
 * Lit le fichier CSV des régions du TD10 et retourne un tableau associatif avec le code et le nom.
 * Utilisé pour alimenter le premier menu déroulant de la page d'accueil.
 * 
 * @return array Tableau associatif sous la forme ['Code' => 'Nom de la région'] (ex:['11' => 'Île-de-France'])
 */
function get_regions_td10(): array {
    $fichierRegions = __DIR__ . '/../data/v_region_2024.csv';
    $regions =[];
    
    if (($fp = @fopen($fichierRegions, "r")) !== false) {
        fgetcsv($fp, 1000, ",", "\"", "\\");
        while (($ligne = fgetcsv($fp, 1000, ",", "\"", "\\")) !== false) {
            $codeRegion = $ligne[0];
            $nomRegion = $ligne[5];
            $regions[$codeRegion] = $nomRegion;
        }
        fclose($fp);
    }
    asort($regions);
    return $regions;
}

/**
 * Cherche tous les départements qui appartiennent à une région précise via le CSV du TD10.
 * 
 * @param string $codeRegionCible Le code de la région sélectionnée par l'utilisateur (ex: "11")
 * @return array Liste des départements triés par numéro, format [["numero" => "75", "nom" => "Paris"], ...]
 */
function get_departements_td10(string $codeRegionCible): array {
    $fichierDepartements = __DIR__ . '/../data/v_departement_2024.csv';
    $depts =[];
    
    if (($fp = @fopen($fichierDepartements, "r")) !== false) {
        fgetcsv($fp, 1000, ",", "\"", "\\");
        while (($ligne = fgetcsv($fp, 1000, ",", "\"", "\\")) !== false) {
            $numDep = $ligne[0];
            $codeRegion = $ligne[1];
            $nomDep = $ligne[6];

            
            if ($codeRegion === $codeRegionCible) {
                $depts[] =[
                    "numero" => $numDep,
                    "nom" => $nomDep
                ];
            }
        }
        fclose($fp);
    }
    
    usort($depts, function($a, $b) { return $a['numero'] <=> $b['numero']; });
    return $depts;
}

/**
 * Parcourt le fichier des communes pour trouver toutes les villes d'un département.
 * 
 * @param string $codeDept Le code du département ciblé (ex: "95")
 * @return array Tableau contenant pour chaque ville son 'insee', son 'cp' et son 'nom'
 */
function get_villes_par_dept(string $codeDept): array {
    $villes = [];
    $fichier = __DIR__ . '/../data/clean_postcodes.csv';
    
    if (($handle = @fopen($fichier, "r")) !== false) {
        fgetcsv($handle, 1000, ",", "\"", "\\"); 
        
        while (($data = fgetcsv($handle, 1000, ",", "\"", "\\")) !== false) {
            $insee = trim($data[0] ?? '');
            $cp = trim($data[2] ?? '');
            
            
            if (str_starts_with($insee, $codeDept)) {
                $nom = strtoupper(trim($data[1] ?? ''));
                if ($nom !== '') {
                    $villes[] =[
                        'insee' => $insee,
                        'cp' => $cp,
                        'nom' => $nom
                    ];
                }
            }
        }
        fclose($handle);
    }
    
    
    
    return $villes;
}

/**
 * Cherche une ville spécifique dans le CSV grâce à son code INSEE pour récupérer ses coordonnées GPS.
 * 
 * @param string $inseeCible Le code INSEE unique de la ville à chercher
 * @return array Tableau contenant la latitude ('lat'), longitude ('lon'), le nom ('nomVille') et le code postal ('cp')
 */
function getCoordonneesINSEE(string $inseeCible): array {
    $fichier = __DIR__."/../data/clean_postcodes.csv";
    

    if ($fichier && ($handle = fopen($fichier, "r")) !== false) {
        fgetcsv($handle, 1000, ",", "\"", "\\"); 
        
        while (($data = fgetcsv($handle, 1000, ",", "\"", "\\")) !== false) {
            $inseeCsv = trim($data[0] ?? '');
            
            if ($inseeCsv === $inseeCible) {
                fclose($handle);
                return[
                    'lat' => (float)$data[3], 
                    'lon' => (float)$data[4], 
                    'nomVille' => strtoupper(trim($data[1])),
                    'cp' => trim($data[2])
                ];
            }
        }
        fclose($handle);
    }
    return['lat' => 0.0, 'lon' => 0.0, 'nomVille' => 'INCONNU', 'cp' => ''];
}

/**
 * Retourne la liste des départements voisins pour élargir la recherche
 * (Évite les problèmes de frontières lors d'une recherche par rayon)
 */
function getDepartementsLimitrophes(string $dept): array {
    $voisins = [
        '01' => ['39','71','73','74','69','38'], '02' => ['59','80','60','60','80','77','51','08'],
        '03' => ['18','58','71','42','63','23'], '04' => ['05','26','26','84','83','83','06'],
        '05' => ['73','38','26','04'], '06' => ['83','04'], '07' => ['26','84','30','48','43','42'],
        '08' => ['02','51','55'], '09' => ['31','31','11','11','66'], '10' => ['51','52','52','89','89','89','77'],
        '11' => ['09','31','81','34','66'], '12' => ['46','48','30','34','81','81','81','81','81','46'],
        '13' => ['30','84','83'], '14' => ['50','27','61'], '15' => ['19','63','43','48','12','46'],
        '16' => ['17','87','24','24'], '17' => ['85','79','79','16','33'], '18' => ['41','45','58','58','03','23','36'],
        '19' => ['23','63','15','46','24','87'], '2A' => ['2B'], '2B' => ['2A'],
        '21' => ['10','52','70','39','71','58','89'], '22' => ['29','22','35'], '23' => ['36','18','03','63','19','87'],
        '24' => ['16','87','19','46','47','33','17'], '25' => ['70','90','68','39','39','71','21'],
        '26' => ['07','38','05','04','84'], '27' => ['76','60','95','78','28','61','14'],
        '28' => ['27','78','91','45','41','72','61'], '29' => ['22','56'], '30' => ['07','26','84','13','34','12','48'],
        '31' => ['32','32','82','81','11','09','65'], '32' => ['40','82','31','65','64'], '33' => ['17','24','47','40'],
        '34' => ['30','12','81','11'], '35' => ['22','50','53','44','44','56'], '36' => ['37','41','18','23','87','86'],
        '37' => ['72','41','36','86','49','49'], '38' => ['01','73','05','26','07','69'], '39' => ['70','25','25','71','21','01'],
        '40' => ['33','47','32','64'], '41' => ['28','45','18','36','37','72'], '42' => ['71','69','38','07','43','63','03'],
        '43' => ['42','07','48','48','15','63'], '44' => ['35','49','85','85','85','56'], '45' => ['28','91','77','89','58','18','41'],
        '46' => ['19','15','12','82','47','24'], '47' => ['24','46','82','32','40','33'], '48' => ['15','43','07','30','12'],
        '49' => ['53','72','37','86','79','85','44'], '50' => ['14','61','53','35'], '51' => ['08','55','52','10','77','02'],
        '52' => ['51','55','88','70','21','10'], '53' => ['50','61','72','49','49','35'], '54' => ['57','57','67','67','88','55'],
        '55' => ['08','54','88','52','51'], '56' => ['29','22','35','44'], '57' => ['54','67','54','54'],
        '58' => ['89','21','71','03','18','45'], '59' => ['02','80','62'], '60' => ['80','02','77','95','27','76'],
        '61' => ['14','27','28','72','53','50'], '62' => ['59','80'], '63' => ['03','42','43','15','19','23'],
        '64' => ['40','32','65'], '65' => ['64','32','31'], '66' => ['09','11'], '67' => ['57','54','88','68'],
        '68' => ['67','88','90'], '69' => ['71','01','38','42','42','42'], '70' => ['88','90','25','39','21','52'],
        '71' => ['21','39','01','69','42','03','58'], '72' => ['61','28','41','37','49','53'], '73' => ['74','01','38','05'],
        '74' => ['01','73'], '75' => ['92','93','94'], '76' => ['80','60','27'], '77' => ['02','51','10','89','45','91','94','93','95','60'],
        '78' => ['95','92','91','28','27'], '79' => ['49','86','16','17','85'], '80' => ['62','59','02','60','76'],
        '81' => ['12','34','11','31','31','31','31','82'], '82' => ['46','81','31','32','47'], '83' => ['04','06','13','84'],
        '84' => ['26','04','83','13','30','07'], '85' => ['44','49','79','17'], '86' => ['37','36','87','16','79','49'],
        '87' => ['36','23','19','24','16','86'], '88' => ['54','67','68','90','70','52','55'], '89' => ['77','10','21','58','45'],
        '90' => ['88','68','25','70'], '91' => ['78','92','94','77','45','28'], '92' => ['95','93','75','94','91','78'],
        '93' => ['95','77','94','75','92'], '94' => ['93','77','91','92','75'], '95' => ['60','77','93','92','78','27']
    ];
    return $voisins[$dept] ?? [];
}

function incrementer_hits(string $fichier): int {
    $hits = 0;
    if (file_exists($fichier)) {
        $hits = (int) file_get_contents($fichier);
    }
    $hits++;
    file_put_contents($fichier, $hits);
    return $hits;
}

/**
 * Enregistre une consultation de ville dans le fichier CSV de statistiques.
 * Chaque ligne est horodatée au format Y-m-d H:i:s.
 * @param string $ville  nom de la ville consultée
 * @param string $dept   code du département
 * @param string $region code de la région
 * @return void
 */
function enregistrer_ville(string $ville, string $dept, string $region): void {
    $fichier = __DIR__ . '/../data/stats_villes.csv';
    $horodatage = date('Y-m-d H:i:s');
    $ligne = implode(',', [
        $horodatage,
        $ville,
        $dept,
        $region
    ]) . "\n";
    file_put_contents($fichier, $ligne, FILE_APPEND | LOCK_EX);
}

/**
 * Retourne les N villes les plus consultées sur le site.
 * @param int $nb nombre de villes à retourner (défaut : 3)
 * @return array tableau [ ['ville' => 'Paris', 'nb' => 42], ... ]
 */
function get_top_villes(int $nb = 3): array {
    $fichier = __DIR__ . '/../data/stats_villes.csv';
    if (!file_exists($fichier)) return [];

    $compteur = [];
    $f = fopen($fichier, 'r');
    while (($ligne = fgetcsv($f, 0, ',', '"', '')) !== false) {
        $ville = $ligne[1] ?? '';
        if ($ville !== '') {
            $compteur[$ville] = ($compteur[$ville] ?? 0) + 1;
        }
    }
    fclose($f);

    arsort($compteur);
    $resultat = [];
    foreach (array_slice($compteur, 0, $nb) as $ville => $count) {
        $resultat[] = ['ville' => $ville, 'nb' => $count];
    }
    return $resultat;
}

/**
 * Récupère les prix moyens nationaux avec mise en cache fichier (1h).
 * @return array tableau [ ['label' => 'SP95', 'val' => '1,720 €', 'pct' => 68, 'color' => '#E8651A'], ... ]
 */
function get_prix_moyens(): array {
    $fichierStats = __DIR__ . '/../data/historique_moyen.csv';
    if (!file_exists($fichierStats)) return[];

    $lignes = file($fichierStats, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (count($lignes) < 2) return[];

    $ligneAujourdhui = str_getcsv(array_pop($lignes),',','"',"");
    $lignePrecedente = count($lignes) > 1 ? str_getcsv(array_pop($lignes),',','"',"") : $ligneAujourdhui;

    $carbs = [['nom' => 'Gazole', 'idx' => 1, 'color' => '#E67E22'],['nom' => 'SP95', 'idx' => 2, 'color' => '#2ECC71'],['nom' => 'E10', 'idx' => 3, 'color' => '#27AE60'],['nom' => 'SP98', 'idx' => 4, 'color' => '#1F894B'],['nom' => 'E85', 'idx' => 5, 'color' => '#3498DB'],['nom' => 'GPLc', 'idx' => 6, 'color' => '#00BCD4']
    ];

    $resultat =[];

    foreach ($carbs as $carb) {
        $prixActuel = (float)$ligneAujourdhui[$carb['idx']];
        $prixPrecedent = (float)$lignePrecedente[$carb['idx']];

        if ($prixActuel == 0) continue;

        $diff = $prixActuel - $prixPrecedent;
        $tendance_txt = ($diff >= 0 ? '+' : '') . number_format($diff, 3, ',', '') . ' €';
        
        $resultat[] = [
            'label' => $carb['nom'],
            'val' => number_format($prixActuel, 3, ',', '') . ' €',
            'pct' => min(100, (int)(($prixActuel / 2.5) * 100)),
            'color' => $carb['color'],
            'tendance_txt' => $tendance_txt,
            'tendance_class' => ($diff >= 0) ? 'hausse' : 'baisse',
            'tendance_icone' => ($diff >= 0) ? '↑' : '↓'
        ];
    }

    return $resultat;
}

/**
 * Échappe les caractères spéciaux HTML pour sécuriser l'affichage.
 * @param string $str chaîne à échapper
 * @return string chaîne échappée
 */
function echapper(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Récupère l'adresse IP réelle du visiteur.
 * @return string adresse IP
 */
function get_ip_visiteur(): string {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Géolocalise une IP via l'API ipinfo.io (format JSON).
 * @param string $ip adresse IP à géolocaliser
 * @return array|false tableau avec city, region, country, postal, loc ou false si erreur
 */
function get_geoloc_ip(string $ip): array|false {
    $url = 'https://ipinfo.io/' . urlencode($ip) . '/geo';
    $json = @file_get_contents($url);
    if ($json === false) return false;

    $reponse = json_decode($json, true);
    if (!is_array($reponse)) return false;

    return $reponse;
}

/**
 * Appelle une API et retourne la réponse parsée en SimpleXMLElement.
 * @param string $url URL de l'API XML
 * @return SimpleXMLElement|false objet XML ou false si erreur
 */
function appel_api_xml(string $url): SimpleXMLElement|false {
    $xml = file_get_contents($url);
    if ($xml === false) return false;

    libxml_use_internal_errors(true);
    $xml_parse = simplexml_load_string($xml);
    if ($xml_parse === false) return false;

    return $xml_parse;
}

/**
 * Récupère un film aléatoire depuis l'API Ghibli.
 * @return array|false tableau avec title, original_title, original_title_romanised,
 *                     release_date, image, movie_banner, description ou false si erreur
 */
function get_film_ghibli_aleatoire(): array|false {
    $url = 'https://ghibliapi.vercel.app/films';
    $json = file_get_contents($url);
    if ($json === false) return false;

    $liste_films = json_decode($json, true);
    if (!is_array($liste_films) || count($liste_films) === 0) return false;

    return $liste_films[array_rand($liste_films)];
}

/**
 * Lit le fichier CSV d'historique des prix nationaux pour alimenter le graphique.
 * On découpe le fichier pour ne garder que les X dernières lignes (les X derniers jours).
 * 
 * @param int $joursMax Le nombre de jours maximum à afficher sur le graphique (défaut : 150 jours)
 * @return array Tableau formaté contenant les dates et les listes de prix pour chaque type de carburant
 */
function get_donnees_evolution(int $joursMax = 150): array {
    $fichierStats = __DIR__ . '/../data/historique_moyen.csv';
    if (!file_exists($fichierStats)) return[];
    
    $lignes = file($fichierStats, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (count($lignes) < 2) return[];

    array_shift($lignes);
    $lignes = array_slice($lignes, -$joursMax);

    $donnees = [
        'dates' => [], 'gazole' =>[], 'sp95' => [], 
        'e10' =>[], 'sp98' => [], 'e85' =>[], 'gplc' =>[]
    ];

    foreach ($lignes as $ligne) {
        $cols = str_getcsv($ligne,',','"',"");
        if (count($cols) < 7) continue;

        $donnees['dates'][]  = $cols[0];
        $donnees['gazole'][] = (float)$cols[1];
        $donnees['sp95'][]   = (float)$cols[2];
        $donnees['e10'][]    = (float)$cols[3];
        $donnees['sp98'][]   = (float)$cols[4];
        $donnees['e85'][]    = (float)$cols[5];
        $donnees['gplc'][]   = (float)$cols[6];
    }
    
    return $donnees;
}

