<?php
// FILE: app/views/sitemap/sitemap.php
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
// $GLOBALS['pageName'] is set by StaticPageController
require_once __DIR__ . '/../partials/header.php';
?>

<main class="container static-page-container">
    <h1>Plan du Site</h1>

    <div class="sitemap-columns">
        <section class="sitemap-section">
            <h2>Navigation Principale</h2>
            <ul>
                <li><a href="<?= $base_url === '' ? '/' : $base_url ?>">Accueil</a></li>
                <li><a href="<?= $base_url ?>/trips">Rechercher un Trajet</a></li>
                <li><a href="<?= $base_url ?>/trip/create">Proposer un Trajet</a></li>
            </ul>
        </section>

        <section class="sitemap-section">
            <h2>Mon Compte</h2>
            <ul>
                <li><a href="<?= $base_url ?>/login">Connexion</a></li>
                <li><a href="<?= $base_url ?>/signup">Créer un Compte</a></li>
                <li><a href="<?= $base_url ?>/profile">Mon Profil</a> (si connecté)</li>
                <li><a href="<?= $base_url ?>/bookings">Mes Réservations</a> (si connecté)</li>
                <li><a href="<?= $base_url ?>/profile/vehicles">Mes Véhicules</a> (si connecté)</li>
            </ul>
        </section>

        <section class="sitemap-section">
            <h2>Assistance & Informations</h2>
            <ul>
                <li><a href="<?= $base_url ?>/faq">FAQ (Foire Aux Questions)</a></li>
                <li><a href="<?= $base_url ?>/contact">Contactez-Nous</a></li>
                <li><a href="<?= $base_url ?>/support">Support</a></li>
                <li><a href="<?= $base_url ?>/forum">Forum</a></li>
            </ul>
        </section>

        <section class="sitemap-section">
            <h2>Informations Légales</h2>
            <ul>
                <li><a href="<?= $base_url ?>/legal">Mentions Légales</a></li>
                <li><a href="<?= $base_url ?>/terms">Conditions Générales de Vente et d'Utilisation</a></li>
                <li><a href="<?= $base_url ?>/privacy-policy">Politique de Confidentialité</a></li>
            </ul>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>