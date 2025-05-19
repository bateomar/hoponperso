<?php
// FILE: app/views/legal_notice/privacy_policy.php
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
// $GLOBALS['pageName'] is set by StaticPageController
require_once __DIR__ . '/../partials/header.php';
?>

<main class="container static-page-container">
    <h1>Politique de Confidentialité</h1>
    <p class="last-updated">Dernière mise à jour : <?= date('d F Y') ?></p>

    <p>La présente Politique de Confidentialité décrit la manière dont HopOn (projet étudiant) collecte, utilise et protège les informations personnelles que vous nous fournissez lors de l'utilisation de notre site web et de nos services (simulés).</p>

    <section class="content-section">
        <h2>1. Collecte des Informations Personnelles</h2>
        <p>Nous collectons les types d'informations personnelles suivants lorsque vous utilisez notre Service :</p>
        <ul>
            <li><strong>Informations d'inscription :</strong> Lorsque vous créez un compte, nous collectons votre nom, prénom, adresse e-mail et mot de passe (crypté).</li>
            <li><strong>Informations de profil (optionnelles) :</strong> Vous pouvez choisir de fournir des informations supplémentaires telles que votre numéro de téléphone, date de naissance, biographie, photo de profil, et préférences de voyage (fumeur, animaux, musique, conversation).</li>
            <li><strong>Informations sur les trajets :</strong> Si vous proposez un trajet, nous collectons les informations relatives à ce trajet (lieux de départ et d'arrivée, dates, heures, prix, nombre de places, détails du véhicule).</li>
            <li><strong>Informations sur les réservations :</strong> Si vous réservez un trajet, nous enregistrons les détails de cette réservation.</li>
            <li><strong>Informations sur les avis :</strong> Si vous laissez un avis sur un autre utilisateur, nous collectons le contenu de cet avis.</li>
            <li><strong>Données de communication :</strong> Si vous utilisez notre messagerie interne (fonctionnalité future), nous pouvons stocker ces messages.</li>
            <li><strong>Données de navigation (simulées) :</strong> De manière générale, les sites collectent des informations via des cookies (adresses IP, type de navigateur, pages visitées). Pour ce projet, cette collecte est minimale ou simulée.</li>
        </ul>
    </section>

    <section class="content-section">
        <h2>2. Utilisation des Informations Personnelles</h2>
        <p>Les informations que nous collectons sont utilisées pour les finalités suivantes (dans le cadre de la simulation du service) :</p>
        <ul>
            <li>Fournir et gérer le Service de mise en relation pour le covoiturage.</li>
            <li>Créer et gérer votre compte utilisateur.</li>
            <li>Faciliter la publication de trajets par les Conducteurs et la réservation par les Passagers.</li>
            <li>Permettre la communication entre Utilisateurs (fonctionnalité future).</li>
            <li>Afficher les profils utilisateurs et les avis pour instaurer la confiance.</li>
            <li>Améliorer le Service et développer de nouvelles fonctionnalités (à des fins d'analyse pour le projet).</li>
            <li>Répondre à vos demandes et vous fournir un support.</li>
        </ul>
    </section>

    <section class="content-section">
        <h2>3. Partage des Informations Personnelles</h2>
        <p>Dans le cadre de ce projet étudiant, vos informations personnelles ne sont pas partagées avec des tiers à des fins commerciales.</p>
        <p>Certaines de vos informations sont visibles par d'autres Utilisateurs pour permettre le fonctionnement du service de covoiturage :</p>
        <ul>
            <li>Votre prénom et l'initiale de votre nom, votre photo de profil (si fournie), votre ancienneté, vos avis reçus, et vos préférences sont visibles sur votre profil public.</li>
            <li>Lorsqu'un trajet est publié, le prénom du Conducteur et les informations de base du trajet sont visibles.</li>
            <li>Lorsque vous réservez ou qu'un passager réserve votre trajet, certaines informations de contact (comme le nom complet ou le numéro de téléphone si la vérification est implémentée et que l'utilisateur y consent) peuvent être partagées entre le Conducteur et le Passager pour faciliter l'organisation du trajet. Ceci est une fonctionnalité simulée pour le projet.</li>
        </ul>
    </section>

    <section class="content-section">
        <h2>4. Sécurité des Données</h2>
        <p>Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles simulées pour protéger vos informations personnelles contre l'accès non autorisé, la modification, la divulgation ou la destruction.</p>
        <p>Les mots de passe sont stockés sous forme hachée (cryptés). Cependant, aucune méthode de transmission sur Internet ou de stockage électronique n'est sûre à 100 %. Bien que ce soit un projet étudiant, nous simulons les bonnes pratiques en matière de sécurité.</p>
    </section>

    <section class="content-section">
        <h2>5. Conservation des Données</h2>
        <p>Vos informations personnelles sont conservées aussi longtemps que votre compte est actif ou pour la durée nécessaire à la démonstration et à l'évaluation de ce projet étudiant. À la fin du projet, ou sur demande, les données pourront être anonymisées ou supprimées.</p>
    </section>

    <section class="content-section">
        <h2>6. Vos Droits</h2>
        <p>Conformément au RGPD, vous disposez des droits suivants concernant vos données personnelles :</p>
        <ul>
            <li>Droit d'accès : Vous pouvez demander une copie des informations que nous détenons sur vous.</li>
            <li>Droit de rectification : Vous pouvez demander la correction des informations inexactes.</li>
            <li>Droit à l'effacement ("droit à l'oubli") : Vous pouvez demander la suppression de vos données sous certaines conditions.</li>
            <li>Droit à la limitation du traitement.</li>
            <li>Droit à la portabilité des données.</li>
            <li>Droit d'opposition.</li>
        </ul>
        <p>Pour exercer ces droits, veuillez nous contacter via l'adresse email fournie dans les Mentions Légales.</p>
    </section>

    <section class="content-section">
        <h2>7. Cookies (Simulé)</h2>
        <p>Ce site simule l'utilisation de cookies essentiels pour assurer son bon fonctionnement (par exemple, pour la gestion des sessions utilisateur). Aucun cookie de suivi tiers ou publicitaire n'est utilisé dans le cadre de ce projet étudiant.</p>
    </section>

    <section class="content-section">
        <h2>8. Modifications de cette Politique</h2>
        <p>Nous pouvons mettre à jour cette Politique de Confidentialité périodiquement. Nous vous informerons de tout changement significatif. Nous vous encourageons à consulter régulièrement cette page.</p>
    </section>

    <section class="content-section">
        <h2>9. Contact</h2>
        <p>Pour toute question relative à cette Politique de Confidentialité, veuillez nous contacter à l'adresse indiquée dans nos <a href="<?= $base_url ?>/legal">Mentions Légales</a>.</p>
    </section>

</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>