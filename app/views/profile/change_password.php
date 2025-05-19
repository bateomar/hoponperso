<?php
// FILE: app/views/profile/change_password.php
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
$error = $GLOBALS['password_error'] ?? null;

require_once __DIR__ . '/../partials/header.php';
?>
<main class="container change-password-container">
    <h1>Change Password</h1>

    <form action="<?= $base_url ?>/profile/password" method="POST" class="change-password-form card">
         <?php if ($error): ?>
             <p class="error-message"><?= htmlspecialchars($error) ?></p>
         <?php endif; ?>

        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" required>
        </div>
         <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" required minlength="6">
            <small>Minimum 6 characters.</small>
        </div>
         <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
        </div>
        <div class="form-actions">
             <button type="submit" class="btn btn-primary">Update Password</button>
             <a href="<?= $base_url ?>/profile" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>