<?php
// FILE: app/views/booking/index.php
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
$bookings = $GLOBALS['bookings'] ?? [];
$current_filter = $GLOBALS['current_filter'] ?? 'upcoming';

// $GLOBALS['pageName'] should be 'my_bookings' set by BookingController::index()
require_once __DIR__ . '/../partials/header.php'; // This will display flash messages

// Helper function to translate booking status (can be moved to a global helper file)
function translateBookingStatusFrench($status) {
    $translations = [
        'pending_confirmation' => 'En attente de confirmation',
        'confirmed' => 'Confirmée (Paiement en attente)',
        'paid' => 'Payée et Confirmée',
        'rejected' => 'Refusée par le conducteur',
        'cancelled_passenger' => 'Annulée par vous',
        'cancelled_driver' => 'Annulée par le conducteur',
        'completed' => 'Terminée',
        'no_show' => 'Non-présentation',
        // Add more as needed
    ];
    return $translations[$status] ?? ucfirst(str_replace('_', ' ', $status)); // Fallback
}
?>
<main class="container my-bookings-container">
    <div class="my-bookings-header">
        <h1>Mes Réservations</h1>
        <div class="filter-buttons">
            <a href="<?= $base_url ?>/bookings?filter=upcoming" class="btn <?= $current_filter === 'upcoming' ? 'btn-primary' : 'btn-outline' ?>">À venir</a>
            <a href="<?= $base_url ?>/bookings?filter=past" class="btn <?= $current_filter === 'past' ? 'btn-primary' : 'btn-outline' ?>">Passées</a>
            <a href="<?= $base_url ?>/bookings?filter=all" class="btn <?= $current_filter === 'all' ? 'btn-primary' : 'btn-outline' ?>">Toutes</a>
        </div>
    </div>

    <?php if (empty($bookings)): ?>
        <div class="no-bookings-message card"> 
            <p>
                <?php
                switch ($current_filter) {
                    case 'upcoming': echo "Vous n'avez aucune réservation à venir."; break;
                    case 'past': echo "Vous n'avez aucune réservation passée."; break;
                    default: echo "Vous n'avez effectué aucune réservation."; break;
                }
                ?>
            </p>
            <a href="<?= $base_url ?>/trips" class="btn btn-primary" style="margin-top: 15px;">Rechercher un trajet</a>
        </div>
    <?php else: ?>
        <div class="booking-list">
            <?php foreach ($bookings as $booking):
                // Determine if the trip is in the future
                $isUpcoming = false;
                try {
                    $departureDateTime = new \DateTime($booking['departure_time']);
                    $now = new \DateTime();
                    $isUpcoming = $departureDateTime > $now;
                } catch (\Exception $e) {
                    error_log("Error parsing departure_time for booking ID {$booking['booking_id']}: " . $e->getMessage());
                }

                // Define cancellable statuses
                $cancellableStatuses = ['pending_confirmation', 'confirmed', 'paid'];
                $isCancellable = $isUpcoming && in_array($booking['booking_status'], $cancellableStatuses);

                // Determine if payment is needed (e.g., status is 'confirmed' or 'pending_confirmation' but not 'paid')
                $needsPayment = $isUpcoming &&
                                ($booking['booking_status'] === 'confirmed' || $booking['booking_status'] === 'pending_confirmation') &&
                                $booking['booking_status'] !== 'paid';
            ?>
                <div class="booking-item card">
                    <div class="booking-item-header">
                        <h3>
                            <?= htmlspecialchars($booking['departure_location']) ?>
                            <i class="fas fa-long-arrow-alt-right"></i>
                            <?= htmlspecialchars($booking['arrival_location']) ?>
                        </h3>
                        <span class="booking-status status-<?= str_replace('_', '-', htmlspecialchars($booking['booking_status'])) ?>">
                            <?= translateBookingStatusFrench($booking['booking_status']) ?>
                        </span>
                    </div>
                    <div class="booking-item-body">
                        <p><strong>Date & Heure :</strong> <?= isset($booking['departure_time']) ? date('d/m/Y à H:i', strtotime($booking['departure_time'])) : 'N/A' ?></p>
                        <p><strong>Conducteur :</strong>
                            <a href="<?= $base_url ?>/user/<?= htmlspecialchars($booking['driver_id']) ?>">
                                <?= htmlspecialchars($booking['driver_first_name'] . ' ' . substr($booking['driver_last_name'], 0, 1) . '.') ?>
                            </a>
                        </p>
                        <p><strong>Prix (par siège) :</strong> <?= number_format($booking['trip_price'], 2, ',', ' ') ?> €</p>
                        <p><strong>Sièges réservés :</strong> <?= htmlspecialchars($booking['booking_seats_count']) ?></p>
                        <p><strong>Réservé le :</strong> <?= isset($booking['booking_date']) ? date('d/m/Y', strtotime($booking['booking_date'])) : 'N/A' ?></p>
                    </div>
                    <div class="booking-item-actions">
                        <a href="<?= $base_url ?>/trip/<?= $booking['trip_id'] ?>" class="btn btn-secondary btn-sm">Voir les détails du trajet</a>
                        <?php if ($isCancellable): ?>
                            <form action="<?= $base_url ?>/booking/cancel/<?= $booking['booking_id'] ?>" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ? Cette action est irréversible.');">
                                <button type="submit" class="btn btn-danger btn-sm">Annuler la réservation</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($needsPayment): ?>
                            <a href="<?= $base_url ?>/booking/pay/<?= $booking['booking_id'] ?>" class="btn btn-success btn-sm">Payer la réservation</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>