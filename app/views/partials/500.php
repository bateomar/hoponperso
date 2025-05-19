<?php
$title = 'Erreur serveur - HopOn';
$description = "Une erreur interne du serveur s'est produite. Nous travaillons à la résoudre.";
include 'app/views/partials/header.php';
?>

<div class="container error-page">
    <div class="error-content">
        <h1>500</h1>
        <h2>Erreur interne du serveur</h2>
        <p>Oups ! Une erreur s'est produite de notre côté. Nos équipes techniques ont été notifiées et travaillent à la résoudre.</p>
        <a href="/" class="btn btn-primary">Retour à l'accueil</a>
    </div>
    
    <div class="error-image">
        <img src="/assets/images/500.svg" alt="Erreur serveur">
    </div>
</div>

<?php include 'app/views/partials/footer.php'; ?>