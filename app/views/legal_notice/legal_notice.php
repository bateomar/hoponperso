<?php
// FILE: app/views/legal_notice/legal_notice.php
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
// $GLOBALS['pageName'] is set by StaticPageController
require_once __DIR__ . '/../partials/header.php';
?>

<main class="container static-page-container">
    <h1>Mentions Légales</h1>

    <section class="content-section">
        <h2>1. Informations sur l'Éditeur du Site</h2>
        <p>
            <strong>Nom du Service :</strong> HopOn (Projet étudiant)<br>
            <strong>Propriétaire / Responsable de la publication :</strong> [Vos Noms / Nom de l'Équipe de Projet]<br>
            <strong>Statut :</strong> Projet dans le cadre de la formation à [Nom de votre école/université, ex: ISEP]<br>
            <strong>Adresse :</strong> [Adresse de votre école/université ou une adresse fictive pour le projet]<br>
            <strong>Contact Email :</strong> <a href="mailto:[Votre Email de Contact Projet]">[Votre Email de Contact Projet]</a><br>
            <em>Note : S'agissant d'un projet étudiant, les informations de société (SIREN, RCS, etc.) ne sont pas applicables.</em>
        </p>
    </section>

    <section class="content-section">
        <h2>2. Hébergement du Site</h2>
        <p>
            <strong>Hébergeur :</strong> Heroku (via GarageISEP) / [Ou votre hébergeur actuel, ex: MAMP localement]<br>
            <strong>Adresse de l'hébergeur :</strong> [Si Heroku/GarageISEP, se référer à leurs informations. Si local, indiquer "Développement local via MAMP"]<br>
            <em>Pour le projet GarageISEP, les détails spécifiques de l'hébergement sont gérés par la plateforme.</em>
        </p>
    </section>

    <section class="content-section">
        <h2>3. Propriété Intellectuelle</h2>
        <p>
            Le contenu de ce site (textes, images, graphismes, logo, icônes, sons, logiciels...), à l'exception des éléments explicitement crédités à des tiers, est la propriété exclusive de l'équipe du projet HopOn en ce qui concerne les droits de propriété intellectuelle ou les droits d'usage.
        </p>
        <p>
            Toute reproduction, représentation, modification, publication, adaptation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite, sauf autorisation écrite préalable de l'équipe du projet HopOn.
        </p>
        <p>
            Le logo HopOn et le nom "HopOn" sont des créations originales pour ce projet et ne doivent pas être utilisés sans permission.
        </p>
    </section>

    <section class="content-section">
        <h2>4. Données Personnelles</h2>
        <p>
            Dans le cadre de ce projet étudiant, la collecte et le traitement des données personnelles (nom, email, etc., lors de l'inscription) sont effectués dans le but de simuler le fonctionnement d'un service de covoiturage.
        </p>
        <p>
            Conformément au Règlement Général sur la Protection des Données (RGPD) et à la loi "Informatique et Libertés", vous disposez d'un droit d'accès, de rectification, de suppression et d'opposition aux données vous concernant. Vous pouvez exercer ces droits en contactant l'équipe du projet à l'adresse email mentionnée ci-dessus.
        </p>
        <p>
            Les données collectées ne sont pas utilisées à des fins commerciales et sont conservées uniquement pour la durée du projet et à des fins de démonstration. Pour plus de détails, veuillez consulter notre <a href="<?= $base_url ?>/privacy-policy">Politique de Confidentialité</a>.
        </p>
    </section>

    <section class="content-section">
        <h2>5. Limitation de Responsabilité</h2>
        <p>
            HopOn est un projet étudiant développé à des fins éducatives. Il simule une plateforme de mise en relation pour le covoiturage. L'équipe du projet s'efforce de fournir des informations aussi précises que possible. Toutefois, elle ne pourra être tenue responsable des omissions, des inexactitudes et des carences dans la mise à jour, qu’elles soient de son fait ou du fait des tiers partenaires qui lui fournissent ces informations.
        </p>
        <p>
            Les trajets proposés sur la plateforme sont fictifs ou simulés dans le cadre du projet. L'équipe du projet HopOn ne pourra être tenue responsable des incidents ou dommages de toute nature qui pourraient survenir lors de l'utilisation (simulée) du service.
        </p>
        <p>
            Ce site peut contenir des liens hypertextes vers d’autres sites. L'équipe HopOn n'a pas la possibilité de vérifier le contenu des sites ainsi visités, et n’assumera en conséquence aucune responsabilité de ce fait.
        </p>
    </section>

    <p class="last-updated">Dernière mise à jour : <?= date('d F Y') ?></p>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>