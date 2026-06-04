<?php
declare(strict_types=1);
require_once "includes/functions.inc.php";

$page_title = "EcoConso — Plan du site";
$page_active = "plan";

require_once "includes/header.inc.php";
?>

<h1 class="sr-only">Plan du site EcoConso</h1>

<section class="bloc-recherche" aria-labelledby="titre-plan">
    <h2 id="titre-plan">Plan du site
        <span class="sous-titre-h2">Vue d'ensemble et arborescence du projet EcoConso</span>
    </h2>

    <div class="carte">
        <div class="titre-carte">
            <span class="material-icons">account_tree</span> Structure du <span>site</span>
        </div>
        
        <div class="apropos-texte">
            <ul class="liste-legale">
                
                <li>
                    <h3 class="texte-gras">
                        <span class="material-icons">search</span> Recherche de carburants
                    </h3>
                    <ul class="liste-legale">
                        <li><a href="index.php" class="lien-plus">Accueil (Recherche par carte de France et menus déroulants)</a></li>
                        <li><a href="resultats.php?mode=proximite" class="lien-plus">Me localiser (Détection automatique par adresse IP)</a></li>
                        <li><a href="resultats.php" class="lien-plus">Résultats de recherche (Affichage des stations et distances)</a></li>
                    </ul>
                </li>

                <li>
                    <h3 class="texte-gras">
                        <span class="material-icons">insights</span> Données et Analyses
                    </h3>
                    <ul class="liste-legale">
                        <li><a href="stats.php#villes" class="lien-plus">Statistiques d'utilisation (Villes les plus recherchées)</a></li>
                        <li><a href="stats.php#prix" class="lien-plus">Évolution des prix nationaux (Graphiques interactifs)</a></li>
                        <li><a href="stats.php#plein" class="lien-plus">Estimation du coût d'un plein</a></li>
                    </ul>
                </li>

                <li>
                    <h3 class="texte-gras">
                        <span class="material-icons">info</span> Informations Légales &amp; Projet
                    </h3>
                    <ul class="liste-legale">
                        <li><a href="a-propos.php" class="lien-plus">À propos de l'équipe (Projet L2)</a></li>
                        <li><a href="mention.php" class="lien-plus">Mentions légales</a></li>
                        <li><a href="tech.php" class="lien-plus">Page technique (Démonstration de l'API Studio Ghibli)</a></li>
                        <li><a href="plan.php" class="lien-plus">Plan du site (Page actuelle)</a></li>
                    </ul>
                </li>

            </ul>
        </div>
    </div>
</section>

<?php require_once "includes/footer.inc.php"; ?>