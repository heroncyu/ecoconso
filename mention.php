<?php
declare(strict_types=1);
require_once "includes/functions.inc.php";

$page_title = "EcoConso — Mentions légales";


require_once "includes/header.inc.php";
?>

<h1 class="sr-only">Mentions légales du projet EcoConso</h1>

<section class="bloc-recherche" aria-labelledby="titre-apropos">
    <h2 id="titre-apropos">Mentions Légales
        <span class="sous-titre-h2">Projet universitaire CY Cergy Paris Université — 2025/2026</span>
    </h2>

    <div class="grille-legale">
        
        <div class="carte">
            <div class="titre-carte"><span class="material-icons">school</span> Éditeur du <span>site</span></div>
            <p class="texte-legal">
                Le site <span class="texte-gras">EcoConso</span> est un projet pédagogique réalisé dans le cadre de l'UE <span class="texte-italique">Développement web</span> en Licence 2 Informatique (Semestre 4) à l'université <span class="texte-gras">CY Cergy Paris Université</span>.
            </p>
            <ul class="liste-legale">
                <li><span class="texte-gras">Auteurs :</span> EL HAJAM Ayoub &amp; HERON Sajid</li>
                <li><span class="texte-gras">Contact :</span> 
                    <a href="mailto:sajid.heron@etu.cyu.fr" class="lien-legal">sajid.heron@etu.cyu.fr</a> —
                <a href="mailto:ayoub.el-hajam1@etu.cyu.fr" class="lien-legal">ayoub.el-hajam1@etu.cyu.fr</a></li>
                <li><span class="texte-gras">Année universitaire :</span> 2025–2026</li>
                <li><span class="texte-gras">Établissement :</span> CY Cergy Paris Université</li>
            </ul>
        </div>

        <div class="carte">
            <div class="titre-carte"><span class="material-icons">dns</span> Hébergement &amp; <span>Propriété</span></div>
            <p class="texte-legal">
                Ce site est hébergé par <span class="texte-gras">Alwaysdata</span> :
                <a href="https://www.alwaysdata.com" target="_blank" rel="noopener noreferrer" class="lien-legal">www.alwaysdata.com</a>
            </p>
            <p class="texte-legal">
                L'ensemble du code source, de l'architecture et des styles graphiques sont la propriété de leurs auteurs et sont produits dans un cadre purement académique.
            </p>
        </div>

        <div class="carte">
            <div class="titre-carte"><span class="material-icons">api</span> Sources des <span>données</span></div>
            <p class="texte-legal">Les informations affichées sur EcoConso proviennent d'interfaces de programmation (API) et de données ouvertes :</p>
            <ul class="liste-legale">
                <li><span class="texte-gras">Prix des carburants :</span> API Flux instantané v2 (<a href="https://data.economie.gouv.fr" target="_blank" rel="noopener noreferrer" class="lien-legal">data.economie.gouv.fr</a>) - Licence Ouverte v2.0 (Etalab).</li>
                <li><span class="texte-gras">Villes et Codes Postaux :</span> Base de données La Poste / INSEE.</li>
                <li><span class="texte-gras">Géolocalisation IP :</span> API XML <a href="http://ip-api.com" target="_blank" rel="noopener noreferrer" class="lien-legal">ip-api.com</a> (utilisée uniquement pour le calcul de proximité).</li>
                <li><span class="texte-gras">Géolocalisation IP (Page technique) :</span> API XML <a href="https://www.whatismyip.com/" target="_blank" rel="noopener noreferrer" class="lien-legal">whatismyip.com</a> (utilisée uniquement pour le calcul de proximité).</li>
                <li><span class="texte-gras">Géolocalisation IP (Page technique) :</span> API JSON <a href="https://ipinfo.io/" target="_blank" rel="noopener noreferrer" class="lien-legal">ipinfo.io</a> (utilisée uniquement pour le calcul de proximité).</li>
                <li><span class="texte-gras">Page technique :</span> API publique non officielle Studio Ghibli.</li>
            </ul>
        </div>

        <div class="carte">
            <div class="titre-carte"><span class="material-icons">cookie</span> Données <span>personnelles (RGPD)</span></div>
            <p class="texte-legal">
                Ce site utilise des <span class="texte-gras">cookies</span> à des fins strictement fonctionnelles. Aucun traceur publicitaire n'est utilisé.
            </p>
            <ul class="liste-legale">
                <li><span class="texte-gras">Thème :</span> Mémorise le choix du mode clair ou sombre (Durée : 30 jours).</li>
                <li><span class="texte-gras">Dernière recherche :</span> Mémorise les paramètres géographiques de votre dernière consultation pour un accès rapide (Durée : 30 jours).</li>
            </ul>
            <p class="texte-legal">
                Les statistiques de consultation des villes sont enregistrées côté serveur de manière totalement anonymisée (aucune adresse IP n'est conservée). Conformément au RGPD, vous pouvez supprimer ces cookies fonctionnels depuis les paramètres de votre navigateur.
            </p>
        </div>

        <div class="carte carte-large">
            <div class="titre-carte"><span class="material-icons">gavel</span> Limite de <span>responsabilité</span></div>
            <p class="texte-legal">
                Les informations affichées sur ce site (prix, localisations, services) proviennent de sources tierces et sont susceptibles de ne pas être à jour. Ce site étant un prototype académique, il ne saurait être tenu responsable d'éventuelles erreurs, omissions ou différences de prix constatées physiquement en station. L'utilisation de la géolocalisation par adresse IP est approximative.
            </p>
        </div>

    </div>
</section>

<?php require_once "includes/footer.inc.php"; ?>