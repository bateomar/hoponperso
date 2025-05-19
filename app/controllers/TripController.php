<?php

namespace App\Controllers;

use App\Models\TripModel;
use App\Models\BookingModel;
use App\Models\User; // Needed to check user type
use App\Models\VehicleModel; // For fetching user's vehicles for create form

use PDO;
use DateTime; // Added for date manipulation

// Include flash message helper
require_once __DIR__ . '/../helpers/flash_messages.php';

class TripController
{
    private $pdo;
    private $tripModel;
    private $bookingModel;
    private $userModel; // Added

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        $dbConnectionPath = __DIR__ . '/../../config/db_connection.php';
        if (file_exists($dbConnectionPath)) {
            $this->pdo = require $dbConnectionPath;
            if (!($this->pdo instanceof PDO)) { die("Error TRC01"); }

            $this->tripModel = new TripModel($this->pdo);
            $this->bookingModel = new BookingModel($this->pdo);
            $this->userModel = new User($this->pdo); // Instantiate UserModel

        } else { die("Error TRC02"); }
    }

    /**
     * Display the details page for a specific trip.
     * @param int $tripId
     */
    public function show(int $tripId)
    {
        if ($tripId <= 0) {
            http_response_code(404);
            set_flash_message('error', 'Invalid trip ID specified.');
             header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '/')); exit; // Redirect home
        }

        $trip = $this->tripModel->findByIdWithDetails($tripId);

        if (!$trip) {
             http_response_code(404);
             set_flash_message('error', 'Trip not found.');
             header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '/')); exit; // Redirect home
        }

        // Prepare data for the view
        $pageName = 'trip_reservation'; // Use appropriate CSS file name
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['trip'] = $trip;
        $GLOBALS['is_user_logged_in'] = isset($_SESSION['user_id']);
        $GLOBALS['current_user_id'] = $_SESSION['user_id'] ?? 0;

        // Check if current user has already booked this trip
        $GLOBALS['has_already_booked'] = false;
        if ($GLOBALS['is_user_logged_in']) {
            $GLOBALS['has_already_booked'] = $this->bookingModel->hasBooking($GLOBALS['current_user_id'], $tripId);
        }

        // Calculate remaining seats
        $GLOBALS['seats_remaining'] = $trip['seats_offered'] - $trip['seats_booked'];

        // Format dates/times
        try {
            $departure_time = new DateTime($trip['departure_time']);
            // Requires locale 'fr_FR.UTF-8' to be available on server OR intl extension
            // setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');
            $GLOBALS['jour_semaine'] = class_exists('IntlDateFormatter') ? (new \IntlDateFormatter('fr_FR', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE))->format($departure_time) : $departure_time->format('l'); // Fallback to English day name
            $GLOBALS['jour_mois_annee'] = class_exists('IntlDateFormatter') ? (new \IntlDateFormatter('fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE))->format($departure_time) : $departure_time->format('F j, Y'); // Fallback
            $GLOBALS['heure_depart'] = $departure_time->format('H:i');
            // Estimate arrival if not set (adjust duration as needed)
            $GLOBALS['heure_arrivee'] = !empty($trip['arrival_time_estimated'])
                                        ? (new DateTime($trip['arrival_time_estimated']))->format('H:i')
                                        : $departure_time->modify('+5 hours')->format('H:i'); // Example 5hr duration
        } catch (\Exception $e) {
            error_log("Error formatting trip date/time: " . $e->getMessage());
            $GLOBALS['jour_semaine'] = "Date";
            $GLOBALS['jour_mois_annee'] = "Inconnue";
            $GLOBALS['heure_depart'] = "--:--";
            $GLOBALS['heure_arrivee'] = "--:--";
        }


        // Load the view (use the new location)
        require_once __DIR__ . '/../views/trip/trip_show_details.php'; // << USE NEW VIEW FILE
    }

    /**
     * Handle the POST request to book a trip.
     */
    public function handleBooking()
    {
        // Basic checks (already done in Router, but good practice here too)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            http_response_code(403); die("Forbidden");
        }

        $userId = $_SESSION['user_id'];
        $tripId = isset($_POST['trip_id']) ? intval($_POST['trip_id']) : 0; // Get trip_id from form

        // Redirect URL in case of error or success
        $redirectUrl = (defined('BASE_PATH') ? BASE_PATH : '') . '/trip/' . $tripId;

        if ($tripId <= 0) {
            set_flash_message('error', 'Invalid trip specified for booking.');
            header('Location: ' . $redirectUrl); exit;
        }

        // --- Check User Type (ensure only passengers can book) ---
        $user = $this->userModel->findById($userId); // Assuming findById gets user type
         if (!$user) { // Should not happen if session is valid
              set_flash_message('error', 'User not found.');
              header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;
         }
        // Assuming 'is_driver' flag determines role, adjust if using 'user_type' string
         if ($user['is_driver'] == 1) {
             set_flash_message('error', 'Drivers cannot book trips.');
             header('Location: ' . $redirectUrl); exit;
         }


        // --- Check if already booked ---
        if ($this->bookingModel->hasBooking($userId, $tripId)) {
            set_flash_message('warning', 'You have already requested or booked this trip.');
            header('Location: ' . $redirectUrl); exit;
        }

        // --- Attempt to book (Handles seat availability check via incrementBookedSeats logic) ---
        // Start Transaction
        $this->pdo->beginTransaction();

        // Increment booked seats (this implicitly checks availability)
        if ($this->tripModel->incrementBookedSeats($tripId, 1)) {
             // Seats available & count incremented, now create booking record
            $bookingStatus = 'confirmed'; // Or 'pending_confirmation' if driver approval needed
            $bookingId = $this->bookingModel->createBooking($userId, $tripId, 1, $bookingStatus);

            if ($bookingId) {
                // Check if trip is now full
                $trip = $this->tripModel->findByIdWithDetails($tripId); // Re-fetch to get updated counts
                if ($trip && $trip['seats_booked'] >= $trip['seats_offered']) {
                    $this->tripModel->updateStatus($tripId, 'full');
                }
                // Commit Transaction
                $this->pdo->commit();
                set_flash_message('success', 'Booking successful!');
                 header('Location: ' . $redirectUrl); exit;

            } else {
                // Failed to create booking record, rollback seat increment
                $this->pdo->rollBack();
                set_flash_message('error', 'Failed to record booking after checking seats.');
                header('Location: ' . $redirectUrl); exit;
            }
        } else {
            // Failed to increment seats (likely full or DB error), rollback (though nothing happened yet)
            $this->pdo->rollBack();
            set_flash_message('error', 'No more seats available or unable to reserve seat.');
            header('Location: ' . $redirectUrl); exit;
        }
    }

    public function browse()
    {
        // Get filter values from GET request
        $filters = [
            'departure_location' => trim($_GET['departure'] ?? ''),
            'arrival_location' => trim($_GET['arrival'] ?? ''),
            'departure_date' => trim($_GET['date'] ?? ''),
            'max_price' => !empty($_GET['max_price']) ? (float)$_GET['max_price'] : null,
            'min_driver_rating' => !empty($_GET['min_rating']) ? (float)$_GET['min_rating'] : null,
            'min_seats' => !empty($_GET['min_seats']) ? (int)$_GET['min_seats'] : null,
        ];

        // Get sorting preference (sanitize this if it's user-driven)
        $orderBy = $_GET['sort_by'] ?? 't.departure_time ASC';
        // Whitelist valid sort options
        $validSorts = [
            't.departure_time ASC' => 'Departure Time (Soonest)',
            't.departure_time DESC' => 'Departure Time (Latest)',
            't.price ASC' => 'Price (Lowest)',
            't.price DESC' => 'Price (Highest)',
            'driver_avg_rating DESC' => 'Driver Rating (Highest)',
        ];
        if (!array_key_exists($orderBy, $validSorts)) {
            $orderBy = 't.departure_time ASC'; // Default sort
        }

        $trips = $this->tripModel->getAllAvailableTrips(array_filter($filters), $orderBy); // array_filter removes empty filter values

        $pageName = 'trip'; // For trip.css
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['trips'] = $trips;
        $GLOBALS['filters'] = $filters; // Pass filters back to view for pre-filling
        $GLOBALS['orderBy'] = $orderBy;   // Pass current sort order
        $GLOBALS['validSorts'] = $validSorts; // Pass sort options

        require_once __DIR__ . '/../views/trip/trips.php'; // View to list trips
    }

    /**
     * Show the form to create a new trip.
     */
    public function showCreateTripForm()
    {
        if (!isset($_SESSION['user_id'])) {
             set_flash_message('error', 'You must be logged in to offer a trip.');
             header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;
        }
        // Optionally, check if user is_driver
        // $user = $this->userModel->findById($_SESSION['user_id']);
        // if (!$user || !$user['is_driver']) {
        //     set_flash_message('error', 'Only registered drivers can offer trips.');
        //     header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/profile'); exit;
        // }

        // Fetch user's vehicles to populate a select dropdown
        $vehicleModel = new VehicleModel($this->pdo); // Instantiate here if not global in constructor
        $userVehicles = $vehicleModel->findByDriverId($_SESSION['user_id']);


        $pageName = 'create_trip'; // For create_trip.css or reuse add_edit_vehicle.css
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['userVehicles'] = $userVehicles; // Pass vehicles for selection
        $GLOBALS['form_data'] = $_SESSION['create_trip_form_data'] ?? []; // For repopulating form on error
        unset($_SESSION['create_trip_form_data']);

        require_once __DIR__ . '/../views/trip/trip_create.php';
    }

    /**
     * Handle POST request to create a new trip.
     */
    public function handleCreateTrip()
    {
        if (!isset($_SESSION['user_id'])) { /* ... redirect to login ... */ exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/trip/create'); exit;
        }

        $userId = $_SESSION['user_id'];
        $base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

        // --- Data Collection and Basic Sanitization ---
        $data = [
            'driver_id' => $userId,
            'vehicle_id' => !empty($_POST['vehicle_id']) ? (int)$_POST['vehicle_id'] : null,
            'departure_location' => trim($_POST['departure_location'] ?? ''),
            'arrival_location' => trim($_POST['arrival_location'] ?? ''),
            'departure_time_date' => trim($_POST['departure_date'] ?? ''),
            'departure_time_time' => trim($_POST['departure_time'] ?? ''),
            'arrival_time_estimated_date' => trim($_POST['arrival_date'] ?? ''), // Optional
            'arrival_time_estimated_time' => trim($_POST['arrival_time'] ?? ''), // Optional
            'price' => !empty($_POST['price']) ? (float)$_POST['price'] : 0.0,
            'seats_offered' => !empty($_POST['seats_offered']) ? (int)$_POST['seats_offered'] : 0,
            'trip_details' => trim($_POST['trip_details'] ?? ''),
            'allow_instant_booking' => isset($_POST['allow_instant_booking']) ? 1 : 0,
            'status' => 'scheduled', // Default status for new trips
        ];

        // Combine date and time for departure
        if (!empty($data['departure_time_date']) && !empty($data['departure_time_time'])) {
            $data['departure_time'] = $data['departure_time_date'] . ' ' . $data['departure_time_time'] . ':00';
        } else {
            $data['departure_time'] = null;
        }
        // Combine date and time for optional arrival
        if (!empty($data['arrival_time_estimated_date']) && !empty($data['arrival_time_estimated_time'])) {
            $data['arrival_time_estimated'] = $data['arrival_time_estimated_date'] . ' ' . $data['arrival_time_estimated_time'] . ':00';
        } else {
            $data['arrival_time_estimated'] = null;
        }


        // --- Validation ---
        $errors = [];
        if (empty($data['departure_location'])) $errors[] = "Departure location is required.";
        if (empty($data['arrival_location'])) $errors[] = "Arrival location is required.";
        if (empty($data['departure_time'])) $errors[] = "Departure date and time are required.";
        // Validate departure_time is in the future
        if ($data['departure_time'] && strtotime($data['departure_time']) < time()) {
             $errors[] = "Departure time must be in the future.";
        }
        if ($data['price'] < 0) $errors[] = "Price cannot be negative."; // Allow 0 for free trips?
        if ($data['seats_offered'] <= 0) $errors[] = "Please offer at least 1 seat.";
        // Add more validation (vehicle exists and belongs to user, etc.)

        if (!empty($errors)) {
            $_SESSION['create_trip_form_data'] = $_POST; // Store submitted data for repopulation
            set_flash_message('error', implode('<br>', $errors));
            header('Location: ' . $base_url . '/trip/create');
            exit;
        }

        // --- Call Model to Create Trip ---
        $tripId = $this->tripModel->createTrip($data);

        if ($tripId) {
            set_flash_message('success', 'Trip created successfully!');
            header('Location: ' . $base_url . '/trip/' . $tripId); // Redirect to the new trip's detail page
            exit;
        } else {
            $_SESSION['create_trip_form_data'] = $_POST;
            set_flash_message('error', 'Failed to create trip. Please try again.');
            header('Location: ' . $base_url . '/trip/trip_create');
            exit;
        }
    }
}