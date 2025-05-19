<?php
// FILE: app/views/profile/edit_profile.php (Texte en Français)
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
$user = $GLOBALS['user'] ?? null; // Données utilisateur passées par le contrôleur
$formData = $GLOBALS['form_data'] ?? $user; // Utiliser les données du formulaire en cas d'erreur, sinon les données utilisateur

if (!$user) {
    // Ce cas devrait être géré par le contrôleur avec une redirection
    // ou un message d'erreur via flash_message.
    // Pour l'instant, un simple die pour indiquer le problème.
    die("Erreur : Impossible de charger les données utilisateur pour l'édition.");
}

// $GLOBALS['pageName'] est défini par ProfileController::edit()
require_once __DIR__ . '/../partials/header.php';
?>

<main class="container edit-profile-container">
    <h1>Modifier mon Profil</h1>

    <form action="<?= $base_url ?>/profile/edit" method="POST" class="profile-edit-form card">

        <h2>Informations Personnelles</h2>
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">Prénom</label>
                <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($formData['first_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="last_name">Nom de famille</label>
                <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($formData['last_name'] ?? '') ?>" required>
            </div>
        </div>
         <div class="form-row">
            <div class="form-group">
                <label for="birth_date">Date de naissance</label>
                <input type="date" id="birth_date" name="birth_date" value="<?= htmlspecialchars($formData['birth_date'] ?? '') ?>">
            </div>
             <div class="form-group">
                <label for="gender">Genre</label>
                <select id="gender" name="gender">
                    <option value="" <?= empty($formData['gender']) ? 'selected' : '' ?>>Sélectionnez...</option>
                    <option value="Male" <?= ($formData['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Homme</option>
                    <option value="Female" <?= ($formData['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Femme</option>
                    <option value="Other" <?= ($formData['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Autre</option>
                    <option value="Prefer not to say" <?= ($formData['gender'] ?? '') === 'Prefer not to say' ? 'selected' : '' ?>>Préfère ne pas préciser</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="phone_number">Numéro de téléphone</label>
            <input type="tel" id="phone_number" name="phone_number" value="<?= htmlspecialchars($formData['phone_number'] ?? '') ?>" placeholder="Ex : 06 12 34 56 78">
        </div>
        <div class="form-group">
             <label for="email">Adresse e-mail</label>
             <input type="email" id="email" name="email_disabled" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
             <small>L'adresse e-mail ne peut pas être modifiée ici.</small>
        </div>


        <h2 id="bio">À propos de moi</h2>
        <div class="form-group">
            <label for="bio">Biographie</label>
            <textarea id="bio" name="bio" rows="5" placeholder="Dites-en un peu plus sur vous..."><?= htmlspecialchars($formData['bio'] ?? '') ?></textarea>
        </div>

        <h2 id="preferences">Préférences de voyage</h2>
         <div class="form-row">
             <div class="form-group">
                 <label for="pref_smokes">Cigarette ?</label>
                 <select id="pref_smokes" name="pref_smokes">
                     <option value="Not specified" <?= ($formData['pref_smokes'] ?? 'Not specified') === 'Not specified' ? 'selected' : '' ?>>Non spécifié</option>
                     <option value="No" <?= ($formData['pref_smokes'] ?? '') === 'No' ? 'selected' : '' ?>>Non</option>
                     <option value="Yes" <?= ($formData['pref_smokes'] ?? '') === 'Yes' ? 'selected' : '' ?>>Oui</option>
                     <option value="Window open" <?= ($formData['pref_smokes'] ?? '') === 'Window open' ? 'selected' : '' ?>>Fenêtre ouverte uniquement</option>
                 </select>
             </div>
              <div class="form-group">
                 <label for="pref_pets">Animaux ?</label>
                 <select id="pref_pets" name="pref_pets">
                     <option value="Not specified" <?= ($formData['pref_pets'] ?? 'Not specified') === 'Not specified' ? 'selected' : '' ?>>Non spécifié</option>
                     <option value="No" <?= ($formData['pref_pets'] ?? '') === 'No' ? 'selected' : '' ?>>Non</option>
                     <option value="Yes" <?= ($formData['pref_pets'] ?? '') === 'Yes' ? 'selected' : '' ?>>Oui</option>
                     <option value="On request" <?= ($formData['pref_pets'] ?? '') === 'On request' ? 'selected' : '' ?>>Sur demande</option>
                 </select>
             </div>
         </div>
         <div class="form-row">
             <div class="form-group">
                 <label for="pref_music">Musique ?</label>
                 <select id="pref_music" name="pref_music">
                      <option value="Not specified" <?= ($formData['pref_music'] ?? 'Not specified') === 'Not specified' ? 'selected' : '' ?>>Non spécifié</option>
                      <option value="Varies" <?= ($formData['pref_music'] ?? '') === 'Varies' ? 'selected' : '' ?>>Variée</option>
                      <option value="Quiet" <?= ($formData['pref_music'] ?? '') === 'Quiet' ? 'selected' : '' ?>>Plutôt calme</option>
                      <option value="Pop" <?= ($formData['pref_music'] ?? '') === 'Pop' ? 'selected' : '' ?>>Pop</option>
                      <option value="Rock" <?= ($formData['pref_music'] ?? '') === 'Rock' ? 'selected' : '' ?>>Rock</option>
                     
                 </select>
             </div>
              <div class="form-group">
                 <label for="pref_talk">Niveau de conversation ?</label>
                 <select id="pref_talk" name="pref_talk">
                     <option value="Not specified" <?= ($formData['pref_talk'] ?? 'Not specified') === 'Not specified' ? 'selected' : '' ?>>Non spécifié</option>
                     <option value="Sometimes" <?= ($formData['pref_talk'] ?? '') === 'Sometimes' ? 'selected' : '' ?>>Parfois</option>
                     <option value="Chatty" <?= ($formData['pref_talk'] ?? '') === 'Chatty' ? 'selected' : '' ?>>Bavard(e)</option>
                     <option value="Quiet" <?= ($formData['pref_talk'] ?? '') === 'Quiet' ? 'selected' : '' ?>>Plutôt calme</option>
                 </select>
             </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="<?= $base_url ?>/profile" class="btn btn-secondary">Annuler</a>
        </div>

    </form>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>