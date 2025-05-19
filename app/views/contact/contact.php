<?php
// FILE: app/views/contact/contact.php
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
// $GLOBALS['pageName'] is set by StaticPageController
require_once __DIR__ . '/../partials/header.php';
?>

<main class="container static-page-container">
    <h1>Contactez-Nous</h1>

    <section class="content-section contact-intro">
        <p>Vous avez une question, une suggestion, ou besoin d'assistance ? N'hésitez pas à nous contacter. Étant un projet étudiant, nous ferons de notre mieux pour vous répondre dans les meilleurs délais (simulés).</p>
    </section>

    <div class="contact-methods">
        <div class="contact-card card">
            <i class="fas fa-envelope fa-3x"></i>
            <h3>Par Email</h3>
            <p>Pour toute demande générale ou support technique (simulé) :</p>
            <a href="mailto:[Votre Email de Contact Projet]" class="contact-link">[Votre Email de Contact Projet]</a>
        </div>

        <div class="contact-card card">
             <i class="fas fa-comments fa-3x"></i>
            <h3>Forum Communautaire</h3>
            <p>Échangez avec d'autres utilisateurs et l'équipe du projet :</p>
            <a href="<?= $base_url ?>/forum" class="contact-link">Visiter le Forum</a>
            <p><small>(Fonctionnalité future)</small></p>
        </div>

        <div class="contact-card card">
            <i class="fas fa-question-circle fa-3x"></i>
            <h3>FAQ</h3>
            <p>Consultez notre Foire Aux Questions pour trouver rapidement des réponses :</p>
            <a href="<?= $base_url ?>/faq" class="contact-link">Consulter la FAQ</a>
        </div>
    </div>

   
    <section class="content-section contact-form-section">
        <h2>Ou envoyez-nous un message directement :</h2>
        <form action="<?= $base_url ?>/contact/submit" method="POST" class="card">
            <div class="form-group">
                <label for="contact_name">Votre Nom :</label>
                <input type="text" id="contact_name" name="name" required>
            </div>
            <div class="form-group">
                <label for="contact_email">Votre Email :</label>
                <input type="email" id="contact_email" name="email" required>
            </div>
            <div class="form-group">
                <label for="contact_subject">Sujet :</label>
                <input type="text" id="contact_subject" name="subject" required>
            </div>
            <div class="form-group">
                <label for="contact_message">Votre Message :</label>
                <textarea id="contact_message" name="message" rows="6" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Envoyer le Message</button>
        </form>
    </section>
    

</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>