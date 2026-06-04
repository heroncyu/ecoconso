<?php
declare(strict_types=1);

if (!isset($_GET['token']) || $_GET['token'] !== 'ecoconso_secret_2026') {
    die("Accès refusé.");
}


set_time_limit(120);


$urlCsv = 'https://data.economie.gouv.fr/api/explore/v2.1/catalog/datasets/prix-des-carburants-en-france-flux-instantane-v2/exports/csv?delimiter=%3B';
$fichierStats = __DIR__ . '/data/historique_moyen.csv';

$totaux =['gazole' => 0.0, 'sp95' => 0.0, 'e10' => 0.0, 'sp98' => 0.0, 'e85' => 0.0, 'gplc' => 0.0];
$comptes =['gazole' => 0, 'sp95' => 0, 'e10' => 0, 'sp98' => 0, 'e85' => 0, 'gplc' => 0];


if (($handle = @fopen($urlCsv, "r")) !== false) {
    $header = fgetcsv($handle, 0, ";", "\"", "\\");
    
    $index =[];
    $carbs =['gazole_prix', 'sp95_prix', 'e10_prix', 'sp98_prix', 'e85_prix', 'gplc_prix'];
    foreach ($carbs as $carb) {
        $idx = array_search($carb, $header);
        if ($idx !== false) {
            $index[str_replace('_prix', '', $carb)] = $idx;
        }
    }

    while (($data = fgetcsv($handle, 0, ";", "\"", "\\")) !== false) {
        foreach ($index as $carb => $i) {
            $prix = isset($data[$i]) ? (float)str_replace(',', '.', $data[$i]) : 0;
            if ($prix > 0) {
                $totaux[$carb] += $prix;
                $comptes[$carb]++;
            }
        }
    }
    fclose($handle);
} else {
    die("Erreur de connexion à l'API de l'État.");
}

$dateAujourdhui = date('Y-m-d');

$ligneCsv = [$dateAujourdhui]; 

foreach (['gazole', 'sp95', 'e10', 'sp98', 'e85', 'gplc'] as $carb) {
    if ($comptes[$carb] > 0) {
        $ligneCsv[] = round($totaux[$carb] / $comptes[$carb], 3);
    } else {
        $ligneCsv[] = 0;
    }
}

$fp = fopen($fichierStats, 'a');

if (filesize($fichierStats) === 0) {
    fputcsv($fp,['date', 'gazole', 'sp95', 'e10', 'sp98', 'e85', 'gplc']);
}

fputcsv($fp, $ligneCsv);
fclose($fp);

echo "Succès : Moyennes du $dateAujourdhui enregistrées !";
?>