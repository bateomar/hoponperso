<?php
// FILE: app/views/legal_notice/terms_conditions.php
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
// $GLOBALS['pageName'] is set by StaticPageController
require_once __DIR__ . '/../partials/header.php';
?>

<main class="container static-page-container">
    <h1>Conditions Générales de Vente et d'Utilisation (CGV/CGU)</h1>
    <p class="last-updated">Dernière mise à jour : <?= date('d F Y') ?></p>

    <p><strong>PRÉAMBULE</strong></p>
    <p>Les présentes Conditions Générales de Vente et d'Utilisation (ci-après "CGV/CGU") régissent l'utilisation du service de covoiturage HopOn (ci-après "le Service"), un projet étudiant. L'accès et l'utilisation du Service impliquent l'acceptation pleine et entière des présentes CGV/CGU par l'utilisateur (ci-après "l'Utilisateur").</p>

    <section class="content-section">
        <h2>Article 1 : Objet du Service</h2>
        <p>HopOn est une plateforme en ligne qui a pour objectif de mettre en relation des conducteurs proposant des places disponibles dans leur véhicule pour un trajet donné (ci-après "Conducteurs") et des passagers recherchant une place pour effectuer ce même trajet (ci-après "Passagers"). HopOn agit uniquement comme un intermédiaire et n'est en aucun cas une société de transport.</p>
        <p><em>Ce service est développé dans un cadre purement éducatif et de démonstration. Aucune transaction financière réelle n'est effectuée et aucun service de transport réel n'est garanti.</em></p>
    </section>

    <section class="content-section">
        <h2>Article 2 : Inscription et Compte Utilisateur</h2>
        <p>2.1. L'utilisation du Service pour proposer ou réserver des trajets nécessite la création d'un compte Utilisateur. L'Utilisateur s'engage à fournir des informations exactes, complètes et à jour lors de son inscription et à les maintenir ainsi.</p>
        <p>2.2. L'Utilisateur est responsable de la confidentialité de ses identifiants de connexion (email et mot de passe) et de toutes les activités effectuées depuis son compte.</p>
        <p>2.3. L'inscription est réservée aux personnes majeures et capables juridiquement.</p>
    </section>

    <section class="content-section">
        <h2>Article 3 : Utilisation du Service</h2>
        <p><strong>3.1. Obligations des Conducteurs :</strong></p>
        <ul>
            <li>Proposer des trajets réels et conformes à la description (horaires, lieux, prix).</li>
            <li>Être titulaire d'un permis de conduire valide et d'une assurance automobile couvrant le transport de passagers à titre non onéreux.</li>
            <li>Utiliser un véhicule en bon état de fonctionnement et conforme aux réglementations en vigueur.</li>
            <li>Ne pas réaliser de bénéfices sur les trajets proposés; le prix demandé aux Passagers doit correspondre à un partage des frais (carburant, péages, usure du véhicule).</li>
            <li>Respecter le Code de la Route et faire preuve de prudence.</li>
        </ul>
        <p><strong>3.2. Obligations des Passagers :</strong></p>
        <ul>
            <li>Se présenter à l'heure et au lieu de rendez-vous convenus.</li>
            <li>Respecter le Conducteur, les autres passagers et le véhicule.</li>
            <li>Payer la contribution aux frais convenue avec le Conducteur (simulée dans ce projet).</li>
        </ul>
        <p><strong>3.3. Interactions et Annulations :</strong></p>
        <ul>
            <li>Les Utilisateurs communiquent et s'organisent entre eux pour les détails du trajet.</li>
            <li>En cas d'annulation, l'Utilisateur concerné s'engage à prévenir l'autre partie dans les plus brefs délais. (Des politiques d'annulation plus détaillées pourraient être ajoutées).</li>
        </ul>
    </section>

    <section class="content-section">
        <h2>Article 4 : Prix et Paiement (Simulé)</h2>
        <p>4.1. Les Conducteurs fixent le prix de la contribution aux frais pour chaque place. Ce prix doit rester dans l'esprit du covoiturage (partage de frais) et ne pas constituer une source de profit.</p>
        <p>4.2. Le paiement de la contribution par les Passagers est simulé sur la plateforme. Aucune transaction financière réelle n'est effectuée.</p>
    </section>

    <section class="content-section">
        <h2>Article 5 : Responsabilité</h2>
        <p>5.1. HopOn, en tant que simple intermédiaire et projet étudiant, ne saurait être tenu responsable des incidents, accidents, retards, annulations ou tout autre désagrément survenant avant, pendant ou après un trajet de covoiturage.</p>
        <p>5.2. Les Utilisateurs sont seuls responsables de leurs interactions, des informations qu'ils publient et du respect des présentes CGV/CGU.</p>
        <p>5.3. HopOn ne garantit pas la disponibilité ou la qualité des trajets proposés, ni le comportement des Utilisateurs.</p>
    </section>

    <section class="content-section">
        <h2>Article 6 : Données Personnelles</h2>
        <p>La collecte et le traitement des données personnelles sont régis par notre <a href="<?= $base_url ?>/privacy-policy">Politique de Confidentialité</a>, qui fait partie intégrante des présentes CGV/CGU.</p>
    </section>

    <section class="content-section">
        <h2>Article 7 : Modification des CGV/CGU</h2>
        <p>L'équipe du projet HopOn se réserve le droit de modifier les présentes CGV/CGU à tout moment. Les Utilisateurs seront informés de ces modifications (par exemple, par une notification sur le site). L'utilisation continue du Service après modification vaut acceptation des nouvelles CGV/CGU.</p>
    </section>

    <section class="content-section">
        <h2>Article 8 : Droit Applicable et Litiges</h2>
        <p>Les présentes CGV/CGU sont soumises au droit français. En cas de litige découlant de l'utilisation de ce service (simulé), une solution amiable sera recherchée en priorité. À défaut, les tribunaux français seront compétents (bien que non applicable concrètement pour un projet étudiant).</p>
    </section>

</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>