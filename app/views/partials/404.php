<?php
$title = 'Page non trouvée - HopOn';
$description = 'La page que vous recherchez n\'a pas été trouvée.';
include 'app/views/partials/header.php';
?>

<div class="container error-page">
    <div class="error-content">
        <h1>404</h1>
        <h2>Page non trouvée</h2>
        <p>La page que vous recherchez n'existe pas ou a été déplacée.</p>
        <a href="/" class="btn btn-primary">Retour à l'accueil</a>
    </div>
    
    <div class="error-image">
        <img src="/assets/images/404.svg" alt="Page non trouvée">
    </div>
</div>

<?php include 'app/views/partials/footer.php'; ?>