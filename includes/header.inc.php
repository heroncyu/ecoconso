<?php
declare(strict_types=1);
$hits_count = incrementer_hits("data/hits.txt");

$page_title       = $page_title       ?? "EcoConso — Prix des carburants";
$page_description = $page_description ?? "EcoConso — Trouvez le carburant le moins cher près de chez vous. EL HAJAM Ayoub &amp; HERON Sajid, L2 Informatique, UE Développement Web, CY Université.";
$page_active      = $page_active      ?? "";

$theme = "jour";
if (isset($_GET["theme"]) && in_array($_GET["theme"], ["jour", "nuit"])) {
    $theme = $_GET["theme"];
    setcookie("theme", $theme, time() + 30 * 24 * 3600, "/");
} elseif (isset($_COOKIE["theme"])) {
    if (in_array($_COOKIE["theme"], ["jour", "nuit"])) {
        $theme = $_COOKIE["theme"];
    } else {
        setcookie("theme", "", time() - 3600, "/");
    }
}

$theme_suivant = ($theme === "jour") ? "nuit" : "jour";
$icone_theme   = ($theme === "jour") ? "images/lune.svg" : "images/soleil.svg";
$alt_theme     = ($theme === "jour") ? "Passer en mode nuit" : "Passer en mode jour";
$css           = ($theme === "nuit") ? "style-nuit.css" : "style-jour.css";

$params = $_GET;
$params["theme"] = $theme_suivant;
$toggle_url = "?" . http_build_query($params, '', '&amp;');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport"    content="width=device-width, initial-scale=1.0" />
    <meta name="author"      content="EL HAJAM Ayoub, HERON Sajid" />
    <meta name="description" content="<?php echo $page_description; ?>" />
    <title><?php echo $page_title; ?></title>
    <link rel="icon"       type="image/x-icon" href="images/favicon.ico" />
    <link rel="stylesheet" href="<?php echo $css; ?>" />
</head>
<body>
<header class="entete">
    <a href="index.php" class="logo">
        <img src="images/logo.webp" alt="Logo EcoConso" class="logo-img" />
        <span class="logo-nom">Eco<span>Conso</span></span>
    </a>
    <nav class="nav-principale">
        <ul>
            <li><a href="stats.php"      <?php if ($page_active === "stats")     echo 'class="actif"'; ?>>Statistiques</a></li>
            <li><a href="a-propos.php"   <?php if ($page_active === "apropos")   echo 'class="actif"'; ?>>À propos</a></li>
        </ul>
    </nav>
    <a href="<?php echo $toggle_url; ?>" class="btn-theme" title="<?php echo $alt_theme; ?>">
        <img src="<?php echo $icone_theme; ?>" alt="<?php echo $alt_theme; ?>" />
    </a>
</header>
<main>
    <h1 class="sr-only">EcoConso : Comparateur des prix des carburants</h1>