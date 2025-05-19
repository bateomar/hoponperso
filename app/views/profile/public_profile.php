<?php
// FILE: app/views/profile/public_profile.php
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
$data = $GLOBALS['publicProfileData'] ?? null;

if (!$data || !isset($data['user'])) {
    // This case should ideally be handled by the controller with a 404 redirect
    // If header is already sent, this is a fallback.
    if (!headers_sent()) {
         // Attempt to include header if possible, but might fail if controller errored out before $pageName
         // $GLOBALS['pageName'] = 'error'; require_once __DIR__ . '/../partials/header.php';
    }
    echo "<main class='container'><p class='error-message'>Profil utilisateur non trouvé.</p></main>";
    if (!headers_sent()) {
        // require_once __DIR__ . '/../partials/footer.php';
    }
    exit;
}

$user = $data['user'];
$vehicles = $data['vehicles'];
$ratings = $data['ratings'];
$avgRating = $data['averageRating'];
$ratingsCount = $data['ratingsCount'];
$upcomingTrips = $data['upcomingTrips'];
$isVerified = $data['isVerified'];
$preferences = $data['preferences']; // From user table

// Helper for date formatting (already defined in your profile.php, ensure it's accessible or redefine)
if (!function_exists('formatDateMemberSinceFrench')) {
    function formatDateMemberSinceFrench($dateString) { /* ... same as in profile.php ... */
        try { $date = new \DateTime($dateString);
            if (class_exists('IntlDateFormatter')) {
                $fmt = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE, null, null, 'MMMM yyyy');
                return "Membre depuis " . ucfirst($fmt->format($date->getTimestamp()));
            } return "Membre depuis " . $date->format('F Y');
        } catch (\Exception $e) { return "Membre depuis date inconnue"; }
    }
}
if (!function_exists('translatePreferenceValueToFrench')) {
    function translatePreferenceValueToFrench($value) { /* ... same as in profile.php ... */
         $map = [ 'Not specified' => 'Non spécifié', 'No' => 'Non', 'Yes' => 'Oui', 'Window open' => 'Fenêtre ouverte', 'On request' => 'Sur demande', 'Sometimes' => 'Parfois', 'Chatty' => 'Bavard(e)', 'Quiet' => 'Plutôt calme', 'Varies' => 'Variée'];
        return $map[$value] ?? htmlspecialchars($value);
    }
}


require_once __DIR__ . '/../partials/header.php';
?>

<main class="public-profile-container container">
    <div class="profile-grid">

        <aside class="profile-sidebar">
            <div class="profile-picture-section">
                <img src="<?= htmlspecialchars($user['profile_picture_url'] ?? '/images/default_avatar.png') ?>" alt="Photo de profil de <?= htmlspecialchars($user['first_name']) ?>" class="avatar-xl">
            </div>

            <div class="profile-name-section">
                <h1><?= htmlspecialchars($user['first_name']) ?> <?= htmlspecialchars(substr($user['last_name'], 0, 1)) ?>.</h1>
                <p class="member-since"><?= formatDateMemberSinceFrench($user['registration_date']) ?></p>
                <?php if (isset($user['age'])): ?>
                    <p class="user-age"><?= htmlspecialchars($user['age']) ?> ans</p>
                <?php endif; ?>
            </div>

            <?php if ($user['is_driver']): // Only show verifications relevant to a driver or public profile ?>
            <div class="profile-verification card">
                <h3>Vérifications</h3>
                <ul>
                    <li class="<?= $isVerified['email'] ? 'verified' : 'not-verified' ?>">
                        <i class="fas <?= $isVerified['email'] ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> Adresse e-mail vérifiée
                    </li>
                    
                </ul>
            </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $user['id']): // Show contact button if viewing someone else's profile and logged in ?>
                <div class="profile-actions card">
                     <a href="<?= $base_url ?>/messages/new/<?= $user['id'] ?>" class="btn btn-primary full-width">Contacter <?= htmlspecialchars($user['first_name']) ?></a>
                </div>
            <?php endif; ?>

        </aside>

        <section class="profile-main-content">
            <?php if ($user['is_driver']): ?>
                <div class="profile-rating-summary card">
                    <div class="rating-stars">
                        <i class="fas fa-star"></i> <?= number_format($avgRating, 1) ?>/5
                    </div>
                    <div class="rating-count">
                        Basé sur <?= $ratingsCount ?> avis
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($user['bio'])): ?>
            <div class="profile-bio card">
                <h2>À propos de <?= htmlspecialchars($user['first_name']) ?></h2>
                <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
            </div>
            <?php endif; ?>

            <?php if ($user['is_driver']): ?>
            <div class="profile-preferences card">
                <h2>Préférences de voyage</h2>
                 <ul>
                    <li><i class="fas <?= ($preferences['smokes'] ?? 'No') === 'No' ? 'fa-smoking-ban' : 'fa-smoking' ?>"></i> Cigarette: <?= translatePreferenceValueToFrench($preferences['smokes'] ?? 'Not specified') ?></li>
                    <li><i class="fas fa-paw"></i> Animaux: <?= translatePreferenceValueToFrench($preferences['pets'] ?? 'Not specified') ?></li>
                    <li><i class="fas fa-music"></i> Musique: <?= translatePreferenceValueToFrench($preferences['music'] ?? 'Not specified') ?></li>
                    <li><i class="fas fa-comments"></i> Conversation: <?= translatePreferenceValueToFrench($preferences['talk'] ?? 'Not specified') ?></li>
                 </ul>
            </div>

            <?php if (!empty($vehicles)): ?>
            <div class="profile-vehicles card">
                 <h2>Véhicule(s) Principal(aux)</h2>
                 <?php foreach($vehicles as $vehicle): ?>
                    <?php if ($vehicle['is_default']): // Optionally show only default vehicle on public profile ?>
                         <div class="vehicle-item">
                              <i class="fas fa-car vehicle-icon"></i>
                              <span><?= htmlspecialchars($vehicle['make']) ?> <?= htmlspecialchars($vehicle['model']) ?> (<?= htmlspecialchars($vehicle['color']) ?>)</span>
                         </div>
                    <?php endif; ?>
                 <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($upcomingTrips)): ?>
            <div class="profile-upcoming-trips card">
                <h2>Trajets Proposés Prochainement</h2>
                <ul class="trip-list-condensed">
                    <?php foreach($upcomingTrips as $trip):
                         $seatsTrulyAvailablePublic = ($trip['seats_offered'] ?? 0) - ($trip['seats_booked'] ?? 0);
                    ?>
                        <li>
                            <a href="<?= $base_url ?>/trip/<?= $trip['id'] ?>" class="trip-link">
                                <strong><?= htmlspecialchars($trip['departure_location']) ?> <i class="fas fa-arrow-right"></i> <?= htmlspecialchars($trip['arrival_location']) ?></strong>
                                <span class="trip-date"><?= date('d/m/Y H:i', strtotime($trip['departure_time'])) ?></span>
                                <span class="trip-price-public"><?= number_format($trip['price'], 2, ',', ' ') ?> €</span>
                                <span class="trip-seats-public"><?= $seatsTrulyAvailablePublic ?> place<?= $seatsTrulyAvailablePublic > 1 ? 's' : '' ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>


            <div class="profile-ratings-received card">
                 <h2>Avis Récents</h2>
                 <?php if (!empty($ratings)): ?>
                     <ul class="ratings-list">
                         <?php foreach($ratings as $rating): ?>
                             <li>
                                 <div class="rating-info">
                                     <span class="rating-author"><?= htmlspecialchars($rating['rater_first_name'] ?? 'Utilisateur') ?></span>
                                     <span class="rating-score"><i class="fas fa-star"></i> <?= htmlspecialchars($rating['score']) ?></span>
                                     <span class="rating-date"><?= date('d/m/Y', strtotime($rating['created_at'])) ?></span>
                                 </div>
                                 <p class="rating-comment"><?= !empty($rating['comment']) ? nl2br(htmlspecialchars($rating['comment'])) : '<i>Pas de commentaire.</i>' ?></p>
                             </li>
                         <?php endforeach; ?>
                     </ul>
                     <?php if ($ratingsCount > count($ratings)): // If more ratings exist than shown ?>
                        <a href="<?= $base_url ?>/user/<?= $user['id'] ?>/ratings" class="view-all-link">Voir tous les avis de <?= htmlspecialchars($user['first_name']) ?></a>
                     <?php endif; ?>
                 <?php else: ?>
                     <p><?= htmlspecialchars($user['first_name']) ?> n'a pas encore reçu d'avis.</p>
                 <?php endif; ?>
             </div>
            <?php else: // User is not a driver ?>
                <div class="card">
                    <p><?= htmlspecialchars($user['first_name']) ?> n'a pas activé de profil conducteur pour le moment.</p>
                </div>
            <?php endif; // End is_driver check ?>
        </section>
    </div>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>