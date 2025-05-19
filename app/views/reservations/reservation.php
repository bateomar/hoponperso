<?php
// Inclure l'en-tête (header) si disponible
if (file_exists(__DIR__ . '/../partials/header.php')) {
    require_once __DIR__ . '/../partials/header.php';
}
?>

<main>
    <div class="container">
        <h1 class="page-title">Mes Réservations</h1>

        <div class="tabs">
            <div class="tab active" id="tab-upcoming">À venir</div>
            <div class="tab" id="tab-past">Passées</div>
        </div>

        <div id="upcoming-reservations">
            <div class="reservations-list">
                <?php if (empty($upcomingReservations)) : ?>
                    <div class="empty-state">
                        <h3>Aucune réservation à venir</h3>
                        <p>Vous n'avez pas encore réservé de trajet à venir.</p>
                        <br>
                        <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/trajets" class="btn">Rechercher des trajets</a>
                    </div>
                <?php else : ?>
                    <?php foreach ($upcomingReservations as $reservation) :
                        global $controller;
                        $dateDepart = $controller->formatDateFr($reservation['date_heure_depart']);
                        $heureDepart = date('H:i', strtotime($reservation['date_heure_depart']));
                        $heureArriveeEstimee = date('H:i', strtotime($reservation['date_heure_depart'] . ' +2 hours'));
                    ?>
                        <div class="reservation-card">
                            <div class="reservation-header">
                                <div class="reservation-date"><?= $dateDepart ?></div>
                                <div class="reservation-status <?= strtolower($reservation['status']) ?>"><?= ucfirst($reservation['status']) ?></div>
                            </div>
                            <div class="reservation-route">
                                <div class="route-time"><?= $heureDepart ?></div>
                                <div class="location">
                                    <div class="location-label">Départ</div>
                                    <div class="location-name"><?= htmlspecialchars($reservation['depart']) ?></div>
                                </div>
                                <div class="route-line"></div>
                                <div class="location">
                                    <div class="location-label">Arrivée</div>
                                    <div class="location-name"><?= htmlspecialchars($reservation['destination']) ?></div>
                                </div>
                                <div class="route-time"><?= $heureArriveeEstimee ?></div>
                            </div>
                            <div class="reservation-details">
                                <div class="driver-info">
                                    <div class="driver-avatar"><?= substr($reservation['conducteur_prenom'], 0, 1) ?></div>
                                    <div>
                                        <div class="driver-name"><?= htmlspecialchars($reservation['conducteur_prenom']) ?> <?= substr($reservation['conducteur_nom'], 0, 1) ?>.</div>
                                        <div class="driver-rating">
                                            <?= $controller->generateStars($reservation['driver_rating']) ?> <?= number_format($reservation['driver_rating'], 1) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="reservation-price"><?= number_format($reservation['price'], 2, ',', ' ') ?> €</div>
                            </div>
                            <div class="action-buttons">
                                <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/reservation/details/<?= $reservation['booking_id'] ?>" class="btn btn-outline">Voir les détails</a>
                                <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/message/send/<?= $reservation['conducteur_id'] ?>" class="btn">Contacter</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div id="past-reservations" style="display: none;">
            <div class="reservations-list">
                <?php if (empty($pastReservations)) : ?>
                    <div class="empty-state">
                        <h3>Aucune réservation passée</h3>
                        <p>Vous n'avez pas encore effectué de trajet avec HopOn.</p>
                    </div>
                <?php else : ?>
                    <?php foreach ($pastReservations as $reservation) :
                        global $controller;
                        $dateDepart = $controller->formatDateFr($reservation['date_heure_depart']);
                        $heureDepart = date('H:i', strtotime($reservation['date_heure_depart']));
                        $heureArriveeEstimee = date('H:i', strtotime($reservation['date_heure_depart'] . ' +2 hours'));
                    ?>
                        <div class="reservation-card">
                            <div class="reservation-header">
                                <div class="reservation-date"><?= $dateDepart ?></div>
                                <div class="reservation-status <?= strtolower($reservation['status']) ?>"><?= ucfirst($reservation['status']) ?></div>
                            </div>
                            <div class="reservation-route">
                                 <div class="route-time"><?= $heureDepart ?></div>
                                <div class="location">
                                    <div class="location-label">Départ</div>
                                    <div class="location-name"><?= htmlspecialchars($reservation['depart']) ?></div>
                                </div>
                                <div class="route-line"></div>
                                <div class="location">
                                    <div class="location-label">Arrivée</div>
                                    <div class="location-name"><?= htmlspecialchars($reservation['destination']) ?></div>
                                </div>
                                <div class="route-time"><?= $heureArriveeEstimee ?></div>
                            </div>
                            <div class="reservation-details">
                                <div class="driver-info">
                                    <div class="driver-avatar"><?= substr($reservation['conducteur_prenom'], 0, 1) ?></div>
                                    <div>
                                        <div class="driver-name"><?= htmlspecialchars($reservation['conducteur_prenom']) ?> <?= substr($reservation['conducteur_nom'], 0, 1) ?>.</div>
                                        <div class="driver-rating">
                                            <?= $controller->generateStars($reservation['driver_rating']) ?> <?= number_format($reservation['driver_rating'], 1) ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="reservation-price"><?= number_format($reservation['price'], 2, ',', ' ') ?> €</div>
                            </div>
                            <div class="action-buttons">
                                <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/reservation/details/<?= $reservation['booking_id'] ?>" class="btn btn-outline">Voir les détails</a>
                                <?php if ($reservation['has_rated']): ?>
                                    <span class="btn" style="opacity: 0.6; cursor: default;">Avis déjà laissé</span>
                                <?php else: ?>
                                    <a href="<?= defined('BASE_PATH') ? BASE_PATH : '' ?>/avis/laisser/<?= $reservation['booking_id'] ?>" class="btn">Laisser un avis</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php
// Inclure le pied de page (footer) si disponible
if (file_exists(__DIR__ . '/../partials/footer.php')) {
    require_once __DIR__ . '/../partials/footer.php';
} else {
    // Si le footer n'est pas disponible, ajouter le script ici
    echo '<script src="' . (defined('BASE_PATH') ? BASE_PATH : '') . '/public/js/reservation.js"></script>';
}
?>
