<?php
// FILE: app/views/trip/index.php (Texte en Français)
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
$trips = $GLOBALS['trips'] ?? [];
$filters = $GLOBALS['filters'] ?? [];
$currentSortBy = $GLOBALS['orderBy'] ?? 't.departure_time ASC';

// Traductions pour les options de tri - Ceci devrait idéalement venir du contrôleur aussi
$validSortsFrench = [
    't.departure_time ASC' => 'Heure de départ (la plus proche)',
    't.departure_time DESC' => 'Heure de départ (la plus éloignée)',
    't.price ASC' => 'Prix (le moins cher)',
    't.price DESC' => 'Prix (le plus cher)',
    'driver_avg_rating DESC' => 'Note du conducteur (la meilleure)',
];
// Utiliser les traductions françaises si disponibles, sinon celles passées par le contrôleur
$sortOptionsToDisplay = $GLOBALS['validSortsFrench'] ?? ($GLOBALS['validSorts'] ?? $validSortsFrench);


// Helper pour formater la date en français
if (!function_exists('formatDateFrenchList')) {
    function formatDateFrenchList($dateTimeString) {
        try {
            $dt = new \DateTime($dateTimeString);
            if (class_exists('IntlDateFormatter')) {
                // Format: "sam. 25 Déc."
                $fmt = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'eee d MMM');
                $formattedDate = $fmt->format($dt);
                if ($formattedDate === false) throw new \Exception(intl_get_error_message());
                return ucfirst($formattedDate); // Ensure first letter is capital
            } else {
                // Fallback (might show English day/month if locale not set globally)
                // You could implement a manual translation map for day/month abbreviations here
                return $dt->format('D, M j');
            }
        } catch (\Exception $e) {
            error_log("Error formatting date in trip list: " . $e->getMessage());
            return date('d/m/Y', strtotime($dateTimeString)); // Basic fallback
        }
    }
}


require_once __DIR__ . '/../partials/header.php';
?>

<main class="container browse-trips-container">
    <div class="browse-header">
        <h1>Trouver un Trajet</h1>
        <a href="<?= $base_url ?>/trip/create" class="btn btn-primary">Proposer un Trajet</a>
    </div>

    <aside class="filters-sidebar card">
        <h2>Filtrer les Trajets</h2>
        <form action="<?= $base_url ?>/trips" method="GET" id="filter-form">
            <div class="form-group">
                <label for="departure">Lieu de départ</label>
                <input type="text" id="departure" name="departure" value="<?= htmlspecialchars($filters['departure_location'] ?? '') ?>" placeholder="Ex : Paris">
            </div>
            <div class="form-group">
                <label for="arrival">Lieu d'arrivée</label>
                <input type="text" id="arrival" name="arrival" value="<?= htmlspecialchars($filters['arrival_location'] ?? '') ?>" placeholder="Ex : Lyon">
            </div>
            <div class="form-group">
                <label for="date">Date de départ</label>
                <input type="date" id="date" name="date" value="<?= htmlspecialchars($filters['departure_date'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="max_price">Prix maximum (€)</label>
                <input type="number" id="max_price" name="max_price" min="0" step="1" value="<?= htmlspecialchars($filters['max_price'] ?? '') ?>" placeholder="Ex : 50">
            </div>
            <div class="form-group">
                <label for="min_rating">Note min. du conducteur (1-5)</label>
                <input type="number" id="min_rating" name="min_rating" min="1" max="5" step="0.1" value="<?= htmlspecialchars($filters['min_driver_rating'] ?? '') ?>" placeholder="Ex : 4.0">
            </div>
            <div class="form-group">
                <label for="min_seats">Places disponibles min.</label>
                <input type="number" id="min_seats" name="min_seats" min="1" value="<?= htmlspecialchars($filters['min_seats'] ?? '1') ?>">
            </div>
            <div class="form-group">
                <label for="sort_by">Trier par</label>
                <select name="sort_by" id="sort_by">
                    <?php foreach ($sortOptionsToDisplay as $value => $label): ?>
                        <option value="<?= $value ?>" <?= $currentSortBy === $value ? 'selected' : '' ?>><?= htmlspecialchars($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary full-width">Appliquer les Filtres</button>
            <a href="<?= $base_url ?>/trips" class="btn btn-secondary full-width clear-filters-btn">Effacer les Filtres</a>
        </form>
    </aside>

    <section class="trip-list-section">
        <?php if (empty($trips)): ?>
            <p class="no-trips-message card">Aucun trajet trouvé correspondant à vos critères. Essayez d'élargir votre recherche !</p>
        <?php else: ?>
            <div class="trip-list">
                <?php foreach ($trips as $trip): ?>
                    <div class="trip-item card">
                        <div class="trip-item-main">
                            <div class="trip-route">
                                <span class="location"><?= htmlspecialchars($trip['departure_location']) ?></span>
                                <i class="fas fa-long-arrow-alt-right route-arrow"></i>
                                <span class="location"><?= htmlspecialchars($trip['arrival_location']) ?></span>
                            </div>
                            <div class="trip-time">
                                <i class="fas fa-calendar-alt"></i> <?= formatDateFrenchList($trip['departure_time']) ?>
                                à <?= date('H:i', strtotime($trip['departure_time'])) ?>
                            </div>
                        </div>
                        <div class="trip-item-details">
                            <div class="driver-info-summary">
                                <img src="<?= $base_url ?><?= htmlspecialchars($trip['driver_avatar'] ?? '/images/default_avatar.png') ?>" alt="Conducteur" class="driver-avatar-small">
                                <span><?= htmlspecialchars($trip['driver_first_name']) ?></span>
                                <?php if (isset($trip['driver_avg_rating']) && $trip['driver_avg_rating'] > 0): ?>
                                    <span class="driver-rating-small"><i class="fas fa-star"></i> <?= number_format($trip['driver_avg_rating'], 1) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="trip-price">
                                <?= number_format($trip['price'], 2, ',', ' ') ?> € 
                                <small>par place</small>
                            </div>
                             <div class="trip-seats">
                                <i class="fas fa-chair"></i> <?= htmlspecialchars($trip['seats_truly_available']) ?> place<?= $trip['seats_truly_available'] > 1 ? 's' : '' ?> restante<?= $trip['seats_truly_available'] > 1 ? 's' : '' ?>
                            </div>
                            <a href="<?= $base_url ?>/trip/<?= $trip['id'] ?>" class="btn btn-secondary btn-sm">Voir les détails</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php // TODO: Ajouter la pagination si beaucoup de résultats ?>
        <?php endif; ?>
    </section>

</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>