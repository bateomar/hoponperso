<?php
// FILE: app/views/payment_page/payment.php

$viewData = $GLOBALS['payment_page_data'] ?? []; // Data passed from PaymentController

// Extract variables for easier use in the template, with defaults
$villeDepart = htmlspecialchars($viewData['departure_location'] ?? "N/A");
$villeArrivee = htmlspecialchars($viewData['arrival_location'] ?? "N/A");

// Format dates correctly if departure_time is provided
$dateDepart = "N/A";
$heureDepart = "N/A";
if (isset($viewData['departure_time'])) {
    try {
        $dt = new \DateTime($viewData['departure_time']);
        // Set French locale for month names if intl is not used for formatting
        // setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');
        if (class_exists('IntlDateFormatter')) {
            $fmtDate = new \IntlDateFormatter('fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
            $dateDepart = $fmtDate->format($dt);
        } else {
            $dateDepart = $dt->format('d F Y'); // Fallback, might be English month
        }
        $heureDepart = $dt->format('H:i');
    } catch (\Exception $e) {
        error_log("Payment page: Error formatting date - " . $e->getMessage());
    }
}

$nomConducteur = htmlspecialchars(($viewData['driver_first_name'] ?? "Conducteur") . ' ' . substr($viewData['driver_last_name'] ?? "", 0, 1) . '.');
$montantAPayer = $viewData['amount_to_pay'] ?? 0.00;
$booking_id_to_pay = $viewData['booking_id'] ?? null;
$payment_error_message_from_controller = $viewData['payment_error_message'] ?? null; // Error from controller

// For repopulating form (if controller sets this after validation error)
$form_data = $_SESSION['payment_form_data'] ?? []; // Check session for repopulation data
if (!empty($form_data)) {
    unset($_SESSION['payment_form_data']); // Clear after use
}

// $GLOBALS['pageName'] should be 'payment' set by controller
require_once __DIR__ . '/../partials/header.php'; // Header displays flash messages
?>

<main class="container payment-container">
    <h2>Confirmation et Paiement</h2>

    <?php if ($payment_error_message_from_controller): ?>
        <div class="payment-confirmation-message error">
            <?= htmlspecialchars($payment_error_message_from_controller) ?>
        </div>
    <?php endif; ?>

    <div class="recap-trip-details card">
        <h3>Récapitulatif du trajet</h3>
        <?php if ($booking_id_to_pay): ?>
            <p><strong>Trajet :</strong> <?= $villeDepart ?> <i class="fas fa-arrow-right"></i> <?= $villeArrivee ?></p>
            <p><strong>Date :</strong> <?= $dateDepart ?></p>
            <p><strong>Heure de départ :</strong> <?= $heureDepart ?></p>
            <p><strong>Conducteur :</strong> <?= $nomConducteur ?></p>
            <p><strong>Montant total :</strong> <span class="total-amount"><?= number_format($montantAPayer, 2, ',', ' ') ?> €</span></p>
        <?php else: ?>
            <p>Aucune information de réservation à afficher pour le paiement. Veuillez sélectionner un trajet à payer depuis vos réservations.</p>
        <?php endif; ?>
    </div>

    <?php if ($booking_id_to_pay): ?>
    <form action="<?= $base_url ?>/payment" method="POST" class="payment-form card">
        <h3>Informations de Paiement</h3>
        <input type="hidden" name="process_payment" value="1">
        <input type="hidden" name="booking_id" value="<?= htmlspecialchars($booking_id_to_pay) ?>">

        <div class="form-group">
            <label for="card_name">Nom sur la carte</label>
            <input type="text" id="card_name" name="card_name" value="<?= htmlspecialchars($form_data['card_name'] ?? '') ?>" placeholder="Jean Dupont" required />
        </div>

        <div class="form-group">
            <label for="card_number">Numéro de carte</label>
            <input type="text" id="card_number" name="card_number" value="<?= htmlspecialchars($form_data['card_number'] ?? '') ?>" inputmode="numeric" pattern="[0-9\s]{13,19}" autocomplete="cc-number" maxlength="19" placeholder="xxxx xxxx xxxx xxxx" required />
        </div>

        <div class="payment-card-details">
            <div class="form-group expiry-group">
                <label for="card_expiry">Date d'expiration (MM/AA)</label>
                <input type="text" id="card_expiry" name="card_expiry" value="<?= htmlspecialchars($form_data['card_expiry'] ?? '') ?>" placeholder="MM/AA" maxlength="5" required />
            </div>
            <div class="form-group cvv-group">
                <label for="card_cvv">CVV/CVC</label>
                <input type="text" id="card_cvv" name="card_cvv" value="<?= htmlspecialchars($form_data['card_cvv'] ?? '') ?>" inputmode="numeric" autocomplete="cc-csc" maxlength="4" placeholder="123" required />
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-submit-payment">
            Payer <?= number_format($montantAPayer, 2, ',', ' ') ?> €
        </button>
    </form>
    <div class="payment-security-info">
        <p><i class="fas fa-lock"></i> Paiement sécurisé. Vos informations sont protégées.</p>
    </div>
    <?php else: ?>
        <div class="card" style="text-align:center; padding: 20px;">
            <p>Veuillez retourner à <a href="<?= $base_url ?>/bookings">vos réservations</a> pour sélectionner un trajet à payer.</p>
        </div>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script>
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            let formattedValue = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) { formattedValue += ' '; }
                formattedValue += value[i];
            }
            e.target.value = formattedValue.substring(0, 19);
        });
    }
    const cardExpiryInput = document.getElementById('card_expiry');
    if (cardExpiryInput) {
        cardExpiryInput.addEventListener('input', function (e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 2) {
                if (e.target.value.length === 2 && e.inputType !== 'deleteContentBackward') {
                     value = value.substring(0, 2) + '/' + value.substring(2);
                } else {
                     value = value.substring(0, 2) + (value.length > 2 ? '/' : '') + value.substring(2, 4);
                }
            }
            e.target.value = value.substring(0,5);
        });
    }
</script>
</body>
</html>
