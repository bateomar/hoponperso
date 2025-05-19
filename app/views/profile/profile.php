<?php
// FILE: app/views/profile/profile.php (Translated to French)

$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
$profileData = $GLOBALS['profileData'] ?? null;

// --- French Translation Array (Example - expand as needed) ---
// You could move this to a separate language file later
$lang = [
    'profile_error_load' => "Erreur: Impossible de charger les données du profil.",
    'change_photo' => "Changer la photo",
    'profile_picture_alt' => "Photo de profil de",
    'member_since' => "Membre depuis",
    'years_old' => "ans",
    'unknown_date' => "date inconnue",
    'verifications' => "Vérifications",
    'email_address' => "Adresse e-mail",
    'phone_number' => "Numéro de téléphone",
    'verify' => "Vérifier",
    'actions' => "Actions",
    'edit_profile' => "Modifier le profil",
    'manage_vehicles' => "Gérer mes véhicules",
    'view_my_ratings' => "Voir mes avis",
    'change_password' => "Changer de mot de passe",
    'based_on' => "Basé sur",
    'ratings' => "avis", // singular/plural handling might be needed
    'rating' => "avis",
    'about' => "À propos de",
    'no_bio' => "Aucune biographie fournie.",
    'edit' => "Modifier",
    'preferences' => "Préférences",
    'smoking' => "Cigarette",
    'pets' => "Animaux",
    'music' => "Musique",
    'chat_level' => "Conversation",
    'not_specified' => "Non spécifié",
    'vehicles' => "Véhicule(s)",
    'no_vehicles' => "Aucun véhicule enregistré.",
    'add_vehicle' => "Ajouter un véhicule",
    'recent_ratings_received' => "Avis récents reçus",
    'view_all_ratings' => "Voir tous les avis",
    'no_ratings_yet' => "Vous n'avez pas encore reçu d'avis.",
    'status_updated' => "Profil mis à jour avec succès !",
    'status_error' => "Une erreur s'est produite.",
    // Add preference values if they differ from English db values
    'pref_smokes_yes' => 'Oui',
    'pref_smokes_no' => 'Non',
    'pref_smokes_window' => 'Fenêtre ouverte',
    'pref_pets_yes' => 'Oui',
    'pref_pets_no' => 'Non',
    'pref_pets_request' => 'Sur demande',
    'pref_talk_sometimes' => 'Parfois',
    'pref_talk_chatty' => 'Bavard(e)',
    'pref_talk_quiet' => 'Plutôt calme',
    // Add more...
];

// Function to safely get translated string
function t($key, $default = '') {
    global $lang;
    return $lang[$key] ?? $default;
}

// Function to translate preference values stored in English in DB
function translatePreference($key, $value) {
     if ($value === 'Not specified') return t('not_specified', 'Not specified');

     switch ($key) {
        case 'smokes':
             if ($value === 'No') return t('pref_smokes_no', 'No');
             if ($value === 'Yes') return t('pref_smokes_yes', 'Yes');
             if ($value === 'Window open') return t('pref_smokes_window', 'Window open');
             break; // Fallthrough if no specific translation
        case 'pets':
             if ($value === 'No') return t('pref_pets_no', 'No');
             if ($value === 'Yes') return t('pref_pets_yes', 'Yes');
             if ($value === 'On request') return t('pref_pets_request', 'On request');
             break;
         case 'talk':
             if ($value === 'Sometimes') return t('pref_talk_sometimes', 'Sometimes');
             if ($value === 'Chatty') return t('pref_talk_chatty', 'Chatty');
             if ($value === 'Quiet') return t('pref_talk_quiet', 'Quiet');
             break;
         // Add case for 'music' if you store specific values like 'Pop', 'Rock'
     }
     return htmlspecialchars($value); // Return original value if no translation found
}


if (!$profileData || !isset($profileData['user'])) {
    echo t('profile_error_load', "Error: Could not load profile data.");
    exit;
}

$user = $profileData['user'];
$vehicles = $profileData['vehicles'];
$ratings = $profileData['ratings'];
$avgRating = $profileData['averageRating'];
$ratingsCount = $profileData['ratingsCount'];
$isVerified = $profileData['isVerified'];
$preferences = $profileData['preferences'];

// Helper function for date formatting (French) - requires locale set
function formatDateMemberSinceFr($dateString) {
    // Ensure French locale is set (best done globally earlier)
    // setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');
    try {
        $timestamp = strtotime($dateString);
        if ($timestamp === false) throw new \Exception("Invalid date format");
        // Use IntlDateFormatter for better localization if available and PHP intl extension enabled
        if (class_exists('IntlDateFormatter')) {
            $fmt = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE, null, null, 'MMMM yyyy');
            $formattedDate = $fmt->format($timestamp);
        } else {
            // Fallback using strftime (might depend on server locale config)
            $formattedDate = (new DateTime())->setTimestamp($timestamp)->format('F Y');
        }
        return t('member_since', "Membre depuis") . " " . ucfirst($formattedDate); // Capitalize month
    } catch (\Exception $e) {
        return t('member_since', "Membre depuis") . " " . t('unknown_date', "date inconnue");
    }
}

require_once __DIR__ . '/../partials/header.php';
?>

<main class="profile-container container">

    <?php // Status messages
        if (isset($_GET['status'])):
            $message = ($_GET['status'] === 'updated') ? t('status_updated', 'Profil mis à jour avec succès !') : t('status_error', 'Une erreur s\'est produite.');
            $messageClass = ($_GET['status'] === 'updated') ? 'success' : 'error';
        ?>
        <div class="status-message <?= $messageClass ?>">
             <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="profile-grid">

        <aside class="profile-sidebar">
            <div class="profile-picture-section">
                <img src="<?= htmlspecialchars($user['profile_picture_url'] ?? '/images/default_avatar.png') ?>" alt="<?= t('profile_picture_alt', 'Photo de profil de') ?> <?= htmlspecialchars($user['first_name']) ?>" class="profile-picture">
                <a href="<?= $base_url ?>/profile/edit-picture" class="change-picture-link"><?= t('change_photo', 'Changer la photo') ?></a>
            </div>

            <div class="profile-name-section">
                <h1><?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars(substr($user['last_name'], 0, 1)) ?>.</h1>
                 <p class="member-since"><?= formatDateMemberSinceFr($user['registration_date']) ?></p>
                 <?php if (isset($user['age'])): ?>
                     <p class="user-age"><?= htmlspecialchars($user['age']) ?> <?= t('years_old', 'ans') ?></p>
                 <?php endif; ?>
            </div>

            <div class="profile-verification card">
                <h3><?= t('verifications', 'Vérifications') ?></h3>
                <ul>
                    <li class="<?= $isVerified['email'] ? 'verified' : 'not-verified' ?>">
                        <i class="fas <?= $isVerified['email'] ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> <?= t('email_address', 'Adresse e-mail') ?>
                        <?php if (!$isVerified['email']): ?> <a href="#verify-email">(<?= t('verify', 'Vérifier') ?>)</a><?php endif; ?>
                    </li>
                    <li class="<?= $isVerified['phone'] ? 'verified' : 'not-verified' ?>">
                        <i class="fas <?= $isVerified['phone'] ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> <?= t('phone_number', 'Numéro de téléphone') ?>
                         <?php if (!$isVerified['phone']): ?> <a href="#verify-phone">(<?= t('verify', 'Vérifier') ?>)</a><?php endif; ?>
                    </li>
                </ul>
            </div>

            <div class="profile-actions card">
                 <h3><?= t('actions', 'Actions') ?></h3>
                 <a href="<?= $base_url ?>/profile/edit" class="btn btn-secondary full-width"><?= t('edit_profile', 'Modifier le profil') ?></a>
                 <a href="<?= $base_url ?>/profile/vehicles" class="btn btn-secondary full-width"><?= t('manage_vehicles', 'Gérer mes véhicules') ?></a>
                 <a href="<?= $base_url ?>/profile/ratings" class="btn btn-secondary full-width"><?= t('view_my_ratings', 'Voir mes avis') ?></a>
                 <a href="<?= $base_url ?>/profile/password" class="btn btn-secondary full-width"><?= t('change_password', 'Changer de mot de passe') ?></a>
            </div>
        </aside>

        <section class="profile-main-content">

             <div class="profile-rating-summary card">
                 <div class="rating-stars">
                     <i class="fas fa-star"></i> <?= number_format($avgRating, 1) ?>/5
                 </div>
                 <div class="rating-count">
                     <?= t('based_on', 'Basé sur') ?> <?= $ratingsCount ?> <?= ($ratingsCount > 1) ? t('ratings', 'avis') : t('rating', 'avis') ?>
                 </div>
             </div>

            <div class="profile-bio card">
                <h2><?= t('about', 'À propos de') ?> <?= htmlspecialchars($user['first_name']) ?></h2>
                <p>
                    <?= !empty($user['bio']) ? nl2br(htmlspecialchars($user['bio'])) : t('no_bio', 'Aucune biographie fournie.') ?>
                </p>
                <a href="<?= $base_url ?>/profile/edit#bio" class="edit-link"><?= t('edit', 'Modifier') ?></a>
            </div>

            <div class="profile-preferences card">
                <h2><?= t('preferences', 'Préférences') ?></h2>
                 <ul>
                    <li><i class="fas <?= ($preferences['smokes'] ?? 'No') === 'No' ? 'fa-smoking-ban' : 'fa-smoking' ?>"></i> <?= t('smoking', 'Cigarette') ?>: <?= translatePreference('smokes', $preferences['smokes']) ?></li>
                    <li><i class="fas fa-paw"></i> <?= t('pets', 'Animaux') ?>: <?= translatePreference('pets', $preferences['pets']) ?></li>
                    <li><i class="fas fa-music"></i> <?= t('music', 'Musique') ?>: <?= translatePreference('music', $preferences['music']) ?></li>
                    <li><i class="fas fa-comments"></i> <?= t('chat_level', 'Conversation') ?>: <?= translatePreference('talk', $preferences['talk']) ?></li>
                 </ul>
                 <a href="<?= $base_url ?>/profile/edit#preferences" class="edit-link"><?= t('edit', 'Modifier') ?></a>
            </div>

            <!-- ========== START: VEHICLES SECTION ON PROFILE (Updated) ========== -->
        <div class="profile-vehicles-summary card"> 
                <h2>Mes Véhicules</h2>
                <?php
                $defaultVehicle = null;
                $otherVehiclesCount = 0;
                if (!empty($vehicles)) { // $vehicles comes from $profileData['vehicles']
                    foreach ($vehicles as $v) {
                        if (!empty($v['is_default'])) { // Check if is_default is set and true
                            $defaultVehicle = $v;
                            // Don't break, count others if default is found first
                        }
                    }
                    // If no explicit default, and vehicles exist, consider the first one.
                    // Or, you might prefer to show nothing specific if no default is set.
                    if (!$defaultVehicle && count($vehicles) > 0) {
                        // To simply show the first vehicle if no default is set:
                        // $defaultVehicle = $vehicles[0];
                        // For now, let's stick to only showing an explicit default here for clarity
                    }

                    // Count non-default vehicles (or all if no default shown)
                    if ($defaultVehicle) {
                        foreach ($vehicles as $v) {
                            if ($v['id'] != $defaultVehicle['id']) {
                                $otherVehiclesCount++;
                            }
                        }
                    } else { // No default vehicle found or displayed, count all as "other" for the message
                        $otherVehiclesCount = count($vehicles);
                    }
                }
                ?>

                <?php if ($defaultVehicle): ?>
                    <div class="default-vehicle-display">
                        <h3>Véhicule principal :</h3>
                        <div class="vehicle-item-summary">
                            <i class="fas fa-car vehicle-icon"></i>
                            <div class="vehicle-details-summary">
                                <strong><?= htmlspecialchars($defaultVehicle['make']) ?> <?= htmlspecialchars($defaultVehicle['model']) ?></strong>
                                <span class="vehicle-color-year">(<?= htmlspecialchars($defaultVehicle['color'] ?? 'Couleur N/P') ?>, <?= htmlspecialchars($defaultVehicle['year'] ?? 'Année N/P') ?>)</span>
                            </div>
                        </div>
                        <?php if (!empty($defaultVehicle['license_plate'])): ?>
                             <p class="vehicle-plate-summary">Immatriculation : <?= htmlspecialchars($defaultVehicle['license_plate']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endif; // End if $defaultVehicle ?>

                <?php if ($otherVehiclesCount > 0): ?>
                    <p class="other-vehicles-info">
                        <?php if ($defaultVehicle): ?>
                            Vous avez également <?= $otherVehiclesCount ?> autre<?= $otherVehiclesCount > 1 ? 's' : '' ?> véhicule<?= $otherVehiclesCount > 1 ? 's' : '' ?> enregistré<?= $otherVehiclesCount > 1 ? 's' : '' ?>.
                        <?php else: // No default shown, so all are "other" ?>
                            Vous avez <?= $otherVehiclesCount ?> véhicule<?= $otherVehiclesCount > 1 ? 's' : '' ?> enregistré<?= $otherVehiclesCount > 1 ? 's' : '' ?>.
                        <?php endif; ?>
                    </p>
                <?php elseif (empty($vehicles)): ?>
                    <p>Vous n'avez pas encore enregistré de véhicule.</p>
                <?php elseif ($defaultVehicle && $otherVehiclesCount === 0) : ?>
            
                    <p style="font-size:0.9em; color: #666; margin-top:5px;">Ceci est votre unique véhicule enregistré.</p>
                <?php endif; // End if $otherVehiclesCount or empty($vehicles) ?>


                <div class="profile-vehicles-actions">
                    <a href="<?= $base_url ?>/profile/vehicles" class="btn btn-secondary btn-sm">Gérer mes véhicules</a>
                    <?php
                        // Ensure $user is defined from $profileData['user'] at the top of the view
                        // And $user['is_driver'] flag exists in your users table
                        if (isset($user['is_driver']) && $user['is_driver']):
                    ?>
                        <a href="<?= $base_url ?>/profile/add-vehicle" class="btn btn-primary btn-sm">Ajouter un véhicule</a>
                    <?php endif; ?>
                </div>
            </div>
            <!-- ========== END: VEHICLES SECTION ON PROFILE (Updated) ========== -->

             <div class="profile-ratings-received card">
                 <h2><?= t('recent_ratings_received', 'Avis récents reçus') ?></h2>
                 <?php if (!empty($ratings)): ?>
                     <ul class="ratings-list">
                         <?php foreach($ratings as $rating): ?>
                             <li>
                                 <div class="rating-info">
                                     <span class="rating-author"><?= htmlspecialchars($rating['rater_first_name'] ?? 'Utilisateur') ?></span>
                                     <span class="rating-score"><i class="fas fa-star"></i> <?= htmlspecialchars($rating['score']) ?></span>
                                     <span class="rating-date"><?= date('d/m/Y', strtotime($rating['created_at'])) ?></span>
                                 </div>
                                 <p class="rating-comment"><?= !empty($rating['comment']) ? nl2br(htmlspecialchars($rating['comment'])) : '<i>Pas de commentaire</i>' ?></p>
                             </li>
                         <?php endforeach; ?>
                     </ul>
                     <a href="<?= $base_url ?>/profile/ratings" class="view-all-link"><?= t('view_all_ratings', 'Voir tous les avis') ?></a>
                 <?php else: ?>
                     <p><?= t('no_ratings_yet', "Vous n'avez pas encore reçu d'avis.") ?></p>
                 <?php endif; ?>
             </div>

        </section>

    </div>
</main>

<?php
require_once __DIR__ . '/../partials/footer.php';
?>