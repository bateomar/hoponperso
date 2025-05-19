<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\TripModel;
use PDO;
use DateTime; // For date formatting

// Include the helper file for flash messages
require_once __DIR__ . '/../helpers/flash_messages.php';

class PaymentController
{
    private $pdo;
    private $bookingModel;
    private $tripModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Authentication check for all methods in this controller
        if (!isset($_SESSION['user_id'])) {
            set_flash_message('error', 'Vous devez être connecté pour accéder à cette page.');
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }

        $dbConnectionPath = __DIR__ . '/../../config/db_connection.php';
        if (file_exists($dbConnectionPath)) {
            $this->pdo = require $dbConnectionPath;
            if (!($this->pdo instanceof PDO)) { die("Error PAYC01: DB Connection failed in PaymentController."); }

            $this->bookingModel = new BookingModel($this->pdo);
            $this->tripModel = new TripModel($this->pdo);
        } else { die("Error PAYC02: DB Config not found in PaymentController."); }
    }

    public function showPaymentPage()
    {
        $booking_id_to_pay = $_SESSION['payment_booking_id'] ?? null;
        $current_user_id = $_SESSION['user_id'];

        if (!$booking_id_to_pay) {
            set_flash_message('error', 'Aucune réservation sélectionnée pour le paiement.');
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings');
            exit;
        }

        // Fetch booking details (ensuring it belongs to the current user)
        $booking = $this->bookingModel->findByIdAndPassenger($booking_id_to_pay, $current_user_id);

        if (!$booking) {
            set_flash_message('error', 'Réservation non trouvée ou non autorisée.');
            unset($_SESSION['payment_booking_id']); // Clear invalid session data
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings');
            exit;
        }

        // Check if payment is still needed
        if ($booking['status'] === 'paid') {
            set_flash_message('info', 'Cette réservation a déjà été payée.');
            unset($_SESSION['payment_booking_id']);
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings');
            exit;
        }
        if ($booking['status'] !== 'confirmed' && $booking['status'] !== 'pending_confirmation') {
             set_flash_message('warning', 'Le paiement pour cette réservation n\'est pas actuellement requis (statut: ' . $booking['status'] . ').');
             unset($_SESSION['payment_booking_id']);
             header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings');
             exit;
        }


        // Fetch trip details using trip_id from the booking
        $trip = $this->tripModel->findByIdWithDetails($booking['trip_id']);

        if (!$trip) {
            set_flash_message('error', 'Les détails du trajet associé à cette réservation n\'ont pas pu être trouvés.');
            unset($_SESSION['payment_booking_id']);
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings');
            exit;
        }

        // Calculate amount to pay (example: price per seat * seats booked for this specific booking)
        $montantAPayer = $trip['price'] * $booking['seats_booked'];

        // Prepare data for the view
        $pageName = 'payment';
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['payment_page_data'] = [
            'departure_location' => $trip['departure_location'],
            'arrival_location' => $trip['arrival_location'],
            'departure_time' => $trip['departure_time'], // View will format this
            'driver_first_name' => $trip['driver_first_name'],
            'driver_last_name' => $trip['driver_last_name'],
            'amount_to_pay' => $montantAPayer,
            'booking_id' => $booking_id_to_pay, // Pass the booking_id to the form
            'payment_error_message' => get_flash_message('payment_form_error') // For re-displaying form with errors
        ];

        require_once __DIR__ . '/../views/payment_page/payment.php';
    }

    public function handlePaymentSubmission()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['process_payment'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/payment'); exit;
        }

        $current_user_id = $_SESSION['user_id'];
        $booking_id_to_pay = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;

        // Re-verify booking ownership and details before processing payment
        if (!$booking_id_to_pay) {
             set_flash_message('error', 'ID de réservation manquant pour le paiement.');
             header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings'); exit;
        }
        $booking = $this->bookingModel->findByIdAndPassenger($booking_id_to_pay, $current_user_id);
        if (!$booking) {
             set_flash_message('error', 'Réservation invalide pour le paiement.');
             header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings'); exit;
        }
        $trip = $this->tripModel->findByIdWithDetails($booking['trip_id']);
        if (!$trip) {
             set_flash_message('error', 'Détails du trajet introuvables pour le paiement.');
             header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings'); exit;
        }
        $montantAPayer = $trip['price'] * $booking['seats_booked'];


        // Card details validation (as in your payment.php view)
        $nomCarte = trim($_POST['card_name'] ?? '');
        // ... (other card details and validation) ...
        if (empty($nomCarte) /* || other checks */ ) {
            set_flash_message('payment_form_error', 'Veuillez remplir tous les champs de paiement.');
            $_SESSION['payment_form_data'] = $_POST; // For repopulating
            $_SESSION['payment_booking_id'] = $booking_id_to_pay; // Re-set booking_id for form re-display
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/payment'); exit;
        }

        // --- Simulate Payment Processing ---
        $paymentSuccessful = true; // Replace with actual payment gateway integration

        if ($paymentSuccessful) {
            if ($this->bookingModel->updateBookingStatus($booking_id_to_pay, 'paid')) {
                error_log("Booking ID {$booking_id_to_pay} status updated to 'paid'.");
                set_flash_message('success', "Paiement de " . number_format($montantAPayer, 2, ',', ' ') . " € effectué avec succès !");
            } else {
                error_log("Failed to update booking status for booking ID {$booking_id_to_pay} after simulated payment.");
                set_flash_message('error', 'Paiement traité, mais une erreur est survenue lors de la mise à jour de votre réservation. Veuillez contacter le support.');
            }
            unset($_SESSION['payment_booking_id']);
            unset($_SESSION['payment_form_data']);
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings?filter=upcoming'); exit;
        } else {
            set_flash_message('payment_form_error', "Le paiement a échoué. Veuillez vérifier vos informations.");
            $_SESSION['payment_form_data'] = $_POST;
            $_SESSION['payment_booking_id'] = $booking_id_to_pay;
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/payment'); exit;
        }
    }
}