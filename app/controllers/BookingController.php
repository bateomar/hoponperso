<?php

namespace App\Controllers;

use App\Models\BookingModel;
use App\Models\TripModel;
use PDO;
use DateTime; // For date comparisons

require_once __DIR__ . '/../helpers/flash_messages.php';

class BookingController
{
    private $pdo;
    private $bookingModel;
    private $tripModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        // Authentication check for all methods in this controller
        if (!isset($_SESSION['user_id'])) {
            set_flash_message('error', 'Vous devez être connecté pour accéder à cette page.');
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }

        $dbConnectionPath = __DIR__ . '/../../config/db_connection.php';
        if (file_exists($dbConnectionPath)) {
            $this->pdo = require $dbConnectionPath;
            if (!($this->pdo instanceof PDO)) { die("Error BKC01"); }

            $this->bookingModel = new BookingModel($this->pdo);
            $this->tripModel = new TripModel($this->pdo);
        } else { die("Error BKC02"); }
    }

    /**
     * Display the list of user's bookings.
     */
    public function index()
    {
        $passengerId = $_SESSION['user_id'];
        $filter = $_GET['filter'] ?? 'upcoming'; // Default to upcoming, allow 'past' or 'all'

        $validFilters = ['upcoming', 'past', 'all'];
        if (!in_array($filter, $validFilters)) {
            $filter = 'upcoming'; // Default to a safe value
        }

        $bookings = $this->bookingModel->findByPassengerIdWithDetails($passengerId, $filter);

        $pageName = 'my_bookings'; // For my_bookings.css
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['bookings'] = $bookings;
        $GLOBALS['current_filter'] = $filter;

        require_once __DIR__ . '/../views/booking/booking.php';
    }

    /**
     * Handle cancellation of a booking.
     * @param int $bookingId
     */
    public function cancel(int $bookingId)
    {
        // POST request for cancellation to prevent CSRF via GET
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            set_flash_message('error', 'Requête invalide pour annuler.');
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings'); exit;
        }

        $passengerId = $_SESSION['user_id'];
        $booking = $this->bookingModel->findByIdAndPassenger($bookingId, $passengerId);

        if (!$booking) {
            set_flash_message('error', 'Réservation non trouvée ou vous n\'avez pas la permission de l\'annuler.');
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings'); exit;
        }

        // Check if booking is cancellable (e.g., trip is in the future and status allows cancellation)
        $departureTime = new DateTime($booking['departure_time']);
        $now = new DateTime();
        $cancellableStatuses = ['pending_confirmation', 'confirmed']; // Add other cancellable statuses if any

        if ($departureTime <= $now || !in_array($booking['status'], $cancellableStatuses)) {
            set_flash_message('error', 'Cette réservation ne peut plus être annulée.');
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings'); exit;
        }

        // --- Perform Cancellation (Transaction Recommended) ---
        $this->pdo->beginTransaction();
        try {
            // Update booking status
            $statusUpdated = $this->bookingModel->updateBookingStatus($bookingId, 'cancelled_passenger');
            // Release the seat(s)
            $seatsReleased = $this->tripModel->decrementBookedSeats($booking['trip_id'], $booking['seats_booked']);

            if ($statusUpdated && $seatsReleased) {
                $this->pdo->commit();
                set_flash_message('success', 'Réservation annulée avec succès.');
            } else {
                $this->pdo->rollBack();
                error_log("Failed to cancel booking ID {$bookingId}: statusUpdate={$statusUpdated}, seatsReleased={$seatsReleased}");
                set_flash_message('error', 'Erreur lors de l\'annulation de la réservation.');
            }
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Exception during booking cancellation for ID {$bookingId}: " . $e->getMessage());
            set_flash_message('error', 'Une erreur serveur est survenue lors de l\'annulation.');
        }
        // --- End Transaction ---

        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings'); exit;
    }

     /**
     * Redirect to the payment page for a specific booking.
     * @param int $bookingId
     */
    public function pay(int $bookingId) {
        $passengerId = $_SESSION['user_id'];
        $booking = $this->bookingModel->findByIdAndPassenger($bookingId, $passengerId);

        if (!$booking) {
            set_flash_message('error', 'Réservation non trouvée.');
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings'); exit;
        }

        // Check if booking needs payment (e.g., status is 'confirmed' or 'pending_payment')
        if ($booking['status'] !== 'confirmed' && $booking['status'] !== 'pending_confirmation') { // Adjust as needed
             set_flash_message('warning', 'Ce paiement n\'est pas requis ou la réservation n\'est pas confirmée.');
             header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/bookings'); exit;
        }

        // Store booking ID or trip details in session to pass to payment page
        $_SESSION['payment_booking_id'] = $bookingId;
        // You might also fetch trip details here to pass to the payment page if it's not directly using the booking ID
        // $_SESSION['payment_trip_details'] = $this->tripModel->findByIdWithDetails($booking['trip_id']);

        // Redirect to your payment page (assuming it's payment_page/payment.php)
        $paymentPageUrl = (defined('BASE_PATH') ? BASE_PATH : '') . '/payment'; // Assumes /payment route exists
        header('Location: ' . $paymentPageUrl);
        exit;
    }
}