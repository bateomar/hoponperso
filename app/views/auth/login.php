<?php

$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

require_once __DIR__ . '/../partials/header.php';
?>

<main class="login-container"> 
    <div class="login-form-wrapper"> 

        <a href="<?= $base_url === '' ? '/' : $base_url ?>" class="back-home">← Retour à l'accueil</a>

        <h1>Connexion</h1>

        <?php if (!empty($GLOBALS['login_error_message'])) : ?>
            <p class="error-message"><?= htmlspecialchars($GLOBALS['login_error_message']) ?></p>
        <?php endif; ?>

        <?php
            // Get email from GLOBALS for repopulation, default to empty
            $formEmail = $GLOBALS['login_form_email'] ?? '';
        ?>

        <form action="<?= $base_url ?>/login" method="POST"> 
            <div class="form-group">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" class="input" placeholder="Entrez votre adresse e-mail" value="<?= htmlspecialchars($formEmail) ?>" required>
            </div>
            <div class="form-group">
                 <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" class="input" placeholder="Entrez votre mot de passe" required>
            </div>
            <button type="submit" class="btn btn-primary">Connexion</button> 
        </form>
        <div class="form-section">
            <p>Vous n'avez pas de compte? <a href="<?= $base_url ?>/signup">Créer votre Compte</a></p>
        </div>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>