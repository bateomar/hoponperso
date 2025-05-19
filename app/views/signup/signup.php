<?php
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : ''; // <<< THIS LINE IS LIKELY MISSING OR AFTER THE HEADER INCLUDE

?>

<?php require_once __DIR__ . '/../partials/header.php'; ?>

<main class="signup-container">
    <section class="signup-form-wrapper">
        <h1>Créer un compte</h1>

        <?php if (!empty($GLOBALS['signup_error_message'])) : // Updated variable name for consistency ?>
            <p class="error-message"><?= htmlspecialchars($GLOBALS['signup_error_message']) ?></p>
        <?php endif; ?>
        <?php if (!empty($GLOBALS['signup_success_message'])) : // Updated variable name for consistency ?>
            <p class="success-message"><?= htmlspecialchars($GLOBALS['signup_success_message']) ?></p>
        <?php endif; ?>

        <?php
        // Get form data from GLOBALS, default to empty array if not set
        $formData = $GLOBALS['signup_form_data'] ?? [];
        ?>

        <form action="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/signup" method="POST">
            <div class="form-group">
                <label for="first_name">Prénom</label>
                <input type="text" id="first_name" name="first_name" placeholder="Entrez votre prénom" value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="last_name">Nom</label>
                <input type="text" id="last_name" name="last_name" placeholder="Entrez votre nom" value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Adresse e-mail</label>
                <input type="email" id="email" name="email" placeholder="Entrez votre e-mail" value="<?= htmlspecialchars($formData['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe (min. 6 caractères)" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmez le mot de passe</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmez votre mot de passe" required>
            </div>

            <button type="submit" class="btn btn-primary">S'inscrire</button>
        </form>
        <p>Déjà un compte ? <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/login">Connectez-vous</a></p>
    </section>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>