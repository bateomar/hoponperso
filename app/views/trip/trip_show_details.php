<?php
// FILE: app/views/trip/show.php
// Expects $GLOBALS['pageName'], $GLOBALS['trip'], $GLOBALS['is_user_logged_in'],
// $GLOBALS['current_user_id'], $GLOBALS['has_already_booked'], $GLOBALS['seats_remaining']
// $GLOBALS['jour_semaine'], $GLOBALS['jour_mois_annee'], $GLOBALS['heure_depart'], $GLOBALS['heure_arrivee']

$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
$trip = $GLOBALS['trip'] ?? null;
$is_user_logged_in = $GLOBALS['is_user_logged_in'] ?? false;
$current_user_id = $GLOBALS['current_user_id'] ?? 0;
$has_already_booked = $GLOBALS['has_already_booked'] ?? false;
$seats_remaining = $GLOBALS['seats_remaining'] ?? 0;

// Get formatted date/time from GLOBALS
$jour_semaine = $GLOBALS['jour_semaine'] ?? 'Date';
$jour_mois_annee = $GLOBALS['jour_mois_annee'] ?? 'Inconnue';
$heure_depart = $GLOBALS['heure_depart'] ?? '--:--';
$heure_arrivee = $GLOBALS['heure_arrivee'] ?? '--:--';


if (!$trip) {
    // Header should have been included before controller redirects on error
    // But as a fallback:
    echo "<main class='container'><p class='error-message'>Détails du trajet non disponibles.</p></main>";
    // Include footer?
    exit;
}

// Helper to translate preference values stored in English in DB to French display text
// Move this to a helper file eventually
function translatePreferenceValueToFrench($value) {
    $map = [ /* ... map as defined in profile.php ... */ ];
    return $map[$value] ?? htmlspecialchars($value);
}


require_once __DIR__ . '/../partials/header.php';
?>
<main class="container my-4">
    <?php // Flash messages displayed by header.php ?>

    <?php if ($trip): ?>
        <h1 class="mb-4">Trajet du <?= htmlspecialchars($jour_semaine) ?> <?= htmlspecialchars($jour_mois_annee) ?></h1>

        <div class="row">
            <div class="col-lg-8">
                <div class="trajet-card">
                    <div class="trajet-info">
                        <div class="trajet-horaire">
                            <div class="trajet-heure"><?= htmlspecialchars($heure_depart) ?></div>
                            <div class="trajet-separation"></div>
                            <div class="trajet-heure"><?= htmlspecialchars($heure_arrivee) ?></div>
                        </div>
                        <div class="trajet-lieu">
                            <div>
                                <div class="trajet-ville"><?= htmlspecialchars($trip['departure_location']) ?></div>
                                <div class="trajet-detail">Point de RDV précis à confirmer via messagerie.</div>
                            </div>
                            <div class="mt-3">
                                <div class="trajet-ville"><?= htmlspecialchars($trip['arrival_location']) ?></div>
                                <div class="trajet-detail">Point de dépose précis à confirmer via messagerie.</div>
                            </div>
                        </div>
                    </div>

                    <div class="conducteur-info">
                            <img src="<?= htmlspecialchars($trip['driver_avatar'] ?? '/images/default_avatar.png') ?>" alt="Avatar de <?= htmlspecialchars($trip['driver_first_name']) ?>" class='avatar-md'>
                      
                        <div>
                            <h5><a href="<?= $base_url ?>/user/<?= $trip['driver_id'] ?>"><?= htmlspecialchars($trip['driver_first_name']) ?></a></h5>
                            <div>
                                <span class="conducteur-note">
                                    <i class="fas fa-star"></i> <?= number_format($trip['driver_average_rating'] ?? 0, 1) ?>
                                </span>
                                <span class="text-muted">(<?= $trip['driver_ratings_count'] ?? 0 ?> avis)</span>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($trip['vehicle_make']) && !empty($trip['vehicle_model'])): ?>
                    <div class="vehicule-info">
                        <p><i class="fas fa-car preference-icon"></i> <?= htmlspecialchars($trip['vehicle_make']) ?> <?= htmlspecialchars($trip['vehicle_model']) ?> - <?= htmlspecialchars($trip['vehicle_color'] ?? 'Couleur non spécifiée') ?></p>
                    </div>
                    <?php endif; ?>

                    <div class="preferences mt-3">
                         <h3>Préférences du conducteur</h3>
                         <ul>
                             <li><i class="fas <?= ($trip['pref_smokes'] ?? 'No') === 'No' ? 'fa-smoking-ban' : 'fa-smoking' ?>"></i> Cigarette: <?= translatePreferenceValueToFrench($trip['pref_smokes'] ?? 'Not specified') ?></li>
                             <li><i class="fas fa-paw"></i> Animaux: <?= translatePreferenceValueToFrench($trip['pref_pets'] ?? 'Not specified') ?></li>
                             <li><i class="fas fa-music"></i> Musique: <?= translatePreferenceValueToFrench($trip['pref_music'] ?? 'Not specified') ?></li>
                             <li><i class="fas fa-comments"></i> Conversation: <?= translatePreferenceValueToFrench($trip['pref_talk'] ?? 'Not specified') ?></li>
                         </ul>
                    </div>

                    <?php if (!empty($trip['trip_details'])): ?>
                    <div class="trip-details-text mt-3">
                         <h3>Détails du trajet</h3>
                         <p><?= nl2br(htmlspecialchars($trip['trip_details'])) ?></p>
                    </div>
                    <?php endif; ?>

                </div>
            </div>

            <div class="col-lg-4">
                <div class="reservation-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="trajet-prix-label">Prix par passager</span>
                            <div class="trajet-prix"><?= number_format($trip['price'], 2, ',', ' ') ?> €</div>
                        </div>
                    </div>

                    <div class="trajet-places">
                        <i class="fas fa-user-friends places-icon"></i>
                        <span class="places-text">
                            <?php if ($seats_remaining > 0): ?>
                                <?= $seats_remaining ?> place<?= ($seats_remaining > 1) ? 's' : '' ?> disponible<?= ($seats_remaining > 1) ? 's' : '' ?>
                            <?php else: ?>
                                Complet
                            <?php endif; ?>
                        </span>
                    </div>

                    <?php if ($is_user_logged_in): ?>
                        <?php if ($current_user_id == $trip['driver_id']): ?>
                             <button disabled class="btn-reserver bg-secondary">Vous êtes le conducteur</button>
                        <?php elseif ($has_already_booked): ?>
                            <button disabled class="btn-reserver bg-info">Déjà réservé / Demandé</button>
                        <?php elseif ($trip['status'] == 'scheduled' && $seats_remaining > 0): ?>
                             
                            <form action="<?= $base_url ?>/trip/book" method="POST">
                                <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
                                
                                <button type="submit" name="reserver" class="btn-reserver">
                                    Demander à réserver (<?= number_format($trip['price'], 2, ',', ' ') ?> €)
                                </button>
                            </form>
                        <?php else: ?>
                            <button disabled class="btn-reserver bg-secondary">
                                Trajet non disponible
                            </button>
                        <?php endif; ?>
                    <?php else: // User not logged in ?>
                        <a href="<?= $base_url ?>/login?redirect=<?= urlencode('/trip/'.$trip['id']) ?>" class="btn-reserver d-block text-center text-decoration-none">
                            Connectez-vous pour réserver
                        </a>
                    <?php endif; ?>

                     <?php if ($is_user_logged_in && $current_user_id != $trip['driver_id']): ?>
                        <div class="mt-3 text-center">
                            <a href="<?= $base_url ?>/messages/new/<?= $trip['driver_id'] ?>?trip=<?= $trip['id'] ?>" class="text-primary contact-link">
                                <i class="fas fa-comment-dots"></i> Contacter <?= htmlspecialchars($trip['driver_first_name']) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
         <?php // Message handled by flash message ?>
         <p><a href="<?= $base_url ?>">Retour à l'accueil</a></p>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<?php // Bootstrap JS is likely not needed unless you add Bootstrap components ?>
<?php // <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script> ?>
</body>
</html>