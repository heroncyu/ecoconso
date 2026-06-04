# EcoConso — Comparateur des prix des carburants

EcoConso est un site web qui vous aide à trouver les stations-service les moins chères en France métropolitaine. Nous avons créé ce site dans le cadre de l'UE "Développement Web" de notre deuxième année de Licence Informatique. 

Nous l'avons pensé pour être rapide (100 de performances sur pagespeed.web.dev), accessible (100% sur WAVE) et respectueux de l'environnement (A sur EcoIndex).

## Que pouvez-vous faire sur ce site ?

* **Rechercher des stations :** Naviguez facilement par région, département et ville pour voir les prix autour de vous.
* **Vous localiser :** Cliquez sur un bouton pour que le site trouve les stations proches de votre position actuelle.
* **Suivre les tendances :** Consultez les prix moyens nationaux et l'évolution des tarifs sur 150 jours grâce à des graphiques interactifs.
* **Personnaliser l'affichage :** Basculez entre le mode clair et le mode sombre. Le site mémorise votre choix pour votre prochaine visite.
* **Gagner du temps :** Le site se souvient de votre dernière ville consultée et vous propose un raccourci dès l'accueil.

## Comment fonctionne le site en coulisse ?

Pour que le site reste très rapide et ne sature pas le serveur, nous avons fait des choix techniques stricts. 

Nous utilisons 3 API externes et des fichiers locaux :

* **L'API des carburants (JSON) :** Au lieu de télécharger la base nationale de 30 Mo à chaque visite, nous téléchargeons uniquement les données du département que vous cherchez. Nous stockons ensuite ce fichier sur notre serveur (en cache) pendant 4 heures. 
* **L'API de Géolocalisation (XML) :** Nous utilisons le service *ip-api.com* pour identifier votre département à partir de votre adresse IP.
* **L'API Studio Ghibli (JSON) :** Nous l'utilisons sur notre page technique pour afficher un film aléatoire.
* **Les fichiers de données (CSV) :** Nous utilisons des fichiers de l'INSEE stockés sur notre serveur pour trouver instantanément les coordonnées GPS des villes, sans faire d'appels réseau inutiles.

## Comment installer et tester le projet ?

Vous n'avez pas besoin d'une base de données complexe pour faire fonctionner ce projet.

1. Téléchargez tous les fichiers du projet.
2. Placez-les sur un serveur web local (comme XAMPP ou WAMP) ou distant (comme Alwaysdata) qui supporte **PHP 8**.
3. Assurez-vous que le dossier `data/` et son sous-dossier `data/cache/` ont les permissions d'écriture (chmod 777 ou 755). Le serveur en a besoin pour y sauvegarder les données temporaires.
4. Ouvrez le fichier `index.php` dans votre navigateur.

## Qui a réalisé ce projet ?

* **EL HAJAM Ayoub**
* **HERON Sajid**

## Technologies utilisées
* **Backend** : PHP
* **Frontend** : HTML, CSS, JavaScript
* **Hébergement** & Déploiement : Alwaysdata, requêtes Cron
* **Données** : API JSON/XML de l'État, manipulation de fichiers CSV

**Cadre du projet :** 
L2 Informatique – Semestre 4 (Année 2025-2026)
CY Cergy Paris Université