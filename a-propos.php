<?php
declare(strict_types=1);
require_once "includes/functions.inc.php";

$page_title  = "EcoConso — À propos";
$page_active = "apropos";

require_once "includes/header.inc.php";
?>

<section class="bloc-recherche">
    <h2>À propos
        <span class="sous-titre-h2">EcoConso : Derrière ce projet, une équipe, Découvrez qui nous sommes !</span>
    </h2>
</section>

<div class="ligne-lateral">
    <div class="carte apropos-carte">
        <div class="titre-carte">
            <span class="material-icons">local_gas_station</span> EcoConso, <span>c'est quoi ?</span>
        </div>
        <p class="apropos-texte">Trouver le carburant le moins cher près de chez soi ne devrait pas être une corvée. EcoConso est né de ce constat simple : l'information existe, elle est publique, autant la rendre accessible en quelques clics. Sélectionnez votre région, votre département, votre ville et nous, on s'occupe du reste !</p>
    </div>

    <div class="carte apropos-carte">
        <div class="titre-carte">
            <span class="material-icons">verified</span> Des données <span>fiables</span>
        </div>
        <p class="apropos-texte">Les prix affichés sur EcoConso proviennent directement de l'API officielle du gouvernement français, mise à jour en continu par les stations-service elles-mêmes. Aucune donnée inventée, aucun intermédiaire, on affiche juste les prix tels qu'ils sont déclarés.</p>
    </div>
</div>

<div class="ligne-lateral">
    <div class="carte apropos-carte">
        <div class="titre-carte">
            <span class="material-icons">school</span> Le <span>projet</span>
        </div>
        <p class="apropos-texte">EcoConso est un projet universitaire réalisé dans le cadre de l'UE Développement Web de L2 Informatique à CY Université sous la supervision de notre professeur encadrant Monsieur Marc Lemaire. Il a été conçu et développé par EL HAJAM Ayoub et HERON Sajid, avec pour objectif de mettre en pratique les notions HTML5, CSS3 et PHP8 autour d'un cas concret.</p>
        <div class="apropos-equipe">
            <div class="apropos-membre">
                <span class="material-icons">person</span>
                <span>EL HAJAM Ayoub</span>
            </div>
            <div class="apropos-membre">
                <span class="material-icons">person</span>
                <span>HERON Sajid</span>
            </div>
        </div>
    </div>

    <div class="carte apropos-carte">
        <div class="titre-carte">
            <span class="material-icons">code</span> Technologies <span>&amp; sources</span>
        </div>
        <p class="apropos-texte">Le site s'appuie sur plusieurs APIs : les prix carburants via data.economie.gouv.fr, la géolocalisation via ipinfo.io, et un clin d'œil au studio Ghibli pour la page technique.</p>
    </div>
</div>

<div class="carte apropos-carte">
    <div class="titre-carte">
        <span class="material-icons">gavel</span> Mentions <span>légales</span>
    </div>
    <p class="apropos-texte">EcoConso est un projet non commercial à but pédagogique. Les données affichées sont issues de sources officielles et publiques. Le site ne collecte aucune donnée personnelle identifiable.</p>
</div>

<?php require_once "includes/footer.inc.php"; ?>