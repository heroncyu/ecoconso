<?php
declare(strict_types=1);
require_once "includes/functions.inc.php";

$page_title = "EcoConso — Page technique";
$page_active = "tech";

$film = get_film_ghibli_aleatoire();
$ip = get_ip_visiteur();
$geoloc = get_geoloc_ip($ip);

$wmi_key = "2a0ad35c38a0e545ca9eace4f9a73998";
$wmi_url = "https://api.whatismyip.com/ip-address-lookup.php?key=" . $wmi_key . "&input=" . urlencode($ip) . "&output=xml";
$wmi_xml = appel_api_xml($wmi_url);

require_once "includes/header.inc.php";
?>

<section class="tech-section">
    <h2>Film Ghibli du moment</h2>
    <p>Chaque visite vous réserve une surprise...</p>

    <?php if ($film === false): ?>
        <p>Impossible de contacter l'API Ghibli pour l'instant, réessayez en rechargeant la page.</p>
    <?php else: ?>
    <article class="ghibli-article">
        <h2 class="sr-only">Film ghibli</h2>
        <p class="ghibli-titre">
            <?php echo echapper($film["title"]); ?> —
            <span lang="ja"><?php echo echapper($film["original_title"]); ?></span>
        </p>
        <p class="ghibli-sous-titre">
            <?php echo echapper($film["original_title_romanised"]); ?> — sorti en <?php echo echapper((string)$film["release_date"]); ?>
        </p>
        <div class="ghibli-images">
            <figure>
                <img src="<?= echapper($film["image"]) ?>" alt="Affiche <?= echapper($film["title"]) ?>" />
                <figcaption>Affiche — <?= echapper($film["title"]) ?></figcaption>
            </figure>
            <figure>
                <img src="<?= echapper($film["movie_banner"]) ?>" alt="Bannière <?= echapper($film["title"]) ?>" />
                <figcaption>Bannière — <?= echapper($film["title"]) ?></figcaption>
            </figure>
        </div>
        <p class="ghibli-description"><?= echapper($film["description"]) ?></p>
    </article>
    <?php endif; ?>
</section>

<section class="tech-section">
    <h2>Votre position approximative</h2>
    <p>Adresse IP détectée : <span class="ip-badge"><?= echapper($ip) ?></span></p>

    <div class="geoloc-grille">
        <div class="geoloc-bloc">
            <h3>Source 1 — ipinfo.io</h3>
            <?php if ($geoloc === false): ?>
                <p>Impossible de récupérer votre position.</p>
            <?php else: ?>
                <dl>
                    <dt>Ville</dt>
                    <dd><?= echapper($geoloc["city"] ?? "—") ?></dd>
                    <dt>Région</dt>
                    <dd><?= echapper($geoloc["region"] ?? "—") ?></dd>
                    <dt>Pays</dt>
                    <dd><?= echapper($geoloc["country"] ?? "—") ?></dd>
                    <dt>Coordonnées GPS</dt>
                    <dd><?= echapper($geoloc["loc"] ?? "—") ?></dd>
                    <dt>Code postal</dt>
                    <dd><?= echapper($geoloc["postal"] ?? "—") ?></dd>
                </dl>
            <?php endif; ?>
        </div>

        <div class="geoloc-bloc">
            <h3>Source 2 — whatismyip (XML)</h3>
            <?php if ($wmi_xml === false): ?>
                <p>Service indisponible pour l'instant (limite journalière de l'API probablement atteinte).</p>
            <?php else: ?>
                <table>
                    <thead><tr><th>Champ</th><th>Valeur</th></tr></thead>
                    <tbody>
                        <tr><td>Pays</td><td><?= echapper((string)($wmi_xml->server_data->country ?? "—")) ?></td></tr>
                        <tr><td>Région</td><td><?= echapper((string)($wmi_xml->server_data->region ?? "—")) ?></td></tr>
                        <tr><td>Ville</td><td><?= echapper((string)($wmi_xml->server_data->city ?? "—")) ?></td></tr>
                        <tr><td>Code postal</td><td><?= echapper((string)($wmi_xml->server_data->postalcode ?? "—")) ?></td></tr>
                        <tr><td>Latitude</td><td><?= echapper((string)($wmi_xml->server_data->latitude ?? "—")) ?></td></tr>
                        <tr><td>Longitude</td><td><?= echapper((string)($wmi_xml->server_data->longitude ?? "—")) ?></td></tr>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once "includes/footer.inc.php"; ?>