<?php
// FILE: app/views/profile/change_picture.php
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
$currentPictureUrl = $GLOBALS['current_picture_url'] ?? '/images/default_avatar.png'; // Use default if null
$error = $GLOBALS['upload_error'] ?? null;

require_once __DIR__ . '/../partials/header.php';
?>

<main class="container change-picture-container">
    <div class="change-picture-content card">
        <h1>Changer sa photo de profil </h1>

        <?php if ($error): ?>
            <p class="error-message"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <div class="current-picture-display">
            <p>Photo actuelle :</p>
            <img src="<?= htmlspecialchars($currentPictureUrl) ?>" alt="Current profile picture">
        </div>

        <form action="<?= $base_url ?>/profile/edit-picture" method="POST" enctype="multipart/form-data" class="upload-form">
            <div class="form-group">
                <label for="profile_picture_input" class="file-upload-label">
                    <i class="fas fa-upload"></i> Choix d'image...
                </label>
                <input type="file" id="profile_picture_input" name="profile_picture" accept="image/jpeg, image/png, image/gif" required>
                <span class="file-name-display" id="file-name-display">Aucun fichier choisi</span>
                <small>Poids maximal : 2MB. Type supportés : JPG, PNG, GIF.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Sauvegarder</button>
                <a href="<?= $base_url ?>/profile" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</main>

<script>
    // Simple script to display the selected filename
    const fileInput = document.getElementById('profile_picture_input');
    const fileNameDisplay = document.getElementById('file-name-display');
    if (fileInput && fileNameDisplay) {
        fileInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
            } else {
                fileNameDisplay.textContent = 'No file chosen';
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>