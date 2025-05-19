<?php

namespace App\Controllers;

use App\Models\User;
use App\Models\VehicleModel;
use App\Models\RatingModel;
use App\Models\TripModel;
use PDO;
use DateTime; // For date manipulation

// Include the flash message helper
require_once __DIR__ . '/../helpers/flash_messages.php';

class ProfileController
{
    private $pdo;
    private $userModel;
    private $vehicleModel;
    private $ratingModel;
    private $tripModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $dbConnectionPath = __DIR__ . '/../../config/db_connection.php';
        if (file_exists($dbConnectionPath)) {
            $this->pdo = require $dbConnectionPath;
            if (!($this->pdo instanceof PDO)) {
                error_log("FATAL: ProfileController::__construct() - Failed PDO load.");
                die("Critical Error (PRC01): Could not connect to database. Please contact support.");
            }

            // Instantiate all needed models
            $this->userModel = new User($this->pdo);
            $this->vehicleModel = new VehicleModel($this->pdo);
            $this->ratingModel = new RatingModel($this->pdo);
            $this->tripModel = new TripModel($this->pdo);

        } else {
            error_log("FATAL: ProfileController::__construct() - db_connection.php not found.");
            die("Critical Error (PRC02): Application configuration missing. Please contact support.");
        }
    }

    /**
     * Display the logged-in user's profile page.
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }
        $userId = $_SESSION['user_id'];

        $userProfile = $this->userModel->findById($userId);

        if (!$userProfile) {
            error_log("ProfileController::index() - Could not fetch profile data for user ID: " . $userId);
            session_destroy(); // Log out user if their data is missing
            set_flash_message('error', 'An error occurred while loading your profile. Please log in again.');
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }

        // --- Fetch Related Data ---
        $userVehicles = $this->vehicleModel->findByDriverId($userId);
        $userRatingsReceived = $this->ratingModel->findByTargetId($userId, 5, 0); // Latest 5 for summary
        $averageRating = $this->ratingModel->getAverageRating($userId);
        $ratingsCount = $this->ratingModel->getRatingsCount($userId);

        $viewData = [
            'user' => $userProfile,
            'vehicles' => $userVehicles,
            'ratings' => $userRatingsReceived,
            'averageRating' => $averageRating,
            'ratingsCount' => $ratingsCount,
            'isVerified' => [
                'email' => (bool)($userProfile['is_email_verified'] ?? false),
                'phone' => (bool)($userProfile['is_phone_verified'] ?? false)
            ],
            'preferences' => [
                'smokes' => $userProfile['pref_smokes'] ?? 'Not specified',
                'pets' => $userProfile['pref_pets'] ?? 'Not specified',
                'music' => $userProfile['pref_music'] ?? 'Not specified',
                'talk' => $userProfile['pref_talk'] ?? 'Not specified',
            ],
        ];

        $pageName = 'profile';
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['profileData'] = $viewData;

        require_once __DIR__ . '/../views/profile/profile.php';
    }

    /**
     * Show the form to edit the logged-in user's profile.
     */
    public function edit()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }
        $userId = $_SESSION['user_id'];

        $user = $this->userModel->findById($userId);
        if (!$user) {
            set_flash_message('error', 'Could not retrieve profile data for editing.');
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/profile');
            exit;
        }

        $pageName = 'profile_edit';
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['user'] = $user;
        // Pass any existing form data back in case of validation errors on previous attempt
        $GLOBALS['form_data'] = $_SESSION['edit_profile_form_data'] ?? $user; // Pre-fill with user data or old form data
        unset($_SESSION['edit_profile_form_data']);


        require_once __DIR__ . '/../views/profile/edit_profile.php';
    }

    /**
     * Handle POST request to update the logged-in user's profile.
     */
    public function update()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/profile/edit');
            exit;
        }
        $userId = $_SESSION['user_id'];
        $base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

        $dataToUpdate = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'phone_number' => trim($_POST['phone_number'] ?? ''),
            'birth_date' => empty($_POST['birth_date']) ? null : trim($_POST['birth_date']),
            'gender' => $_POST['gender'] ?? null,
            'bio' => trim($_POST['bio'] ?? ''),
            'pref_smokes' => $_POST['pref_smokes'] ?? 'Not specified',
            'pref_pets' => $_POST['pref_pets'] ?? 'Not specified',
            'pref_music' => $_POST['pref_music'] ?? 'Not specified',
            'pref_talk' => $_POST['pref_talk'] ?? 'Not specified',
        ];

        // Basic Validation
        if (empty($dataToUpdate['first_name']) || empty($dataToUpdate['last_name'])) {
            set_flash_message('error', 'First name and last name cannot be empty.');
            $_SESSION['edit_profile_form_data'] = $_POST; // Store submitted data for repopulation
            header('Location: ' . $base_url . '/profile/edit');
            exit;
        }
        // Add more specific validation (phone format, date format, enum values, etc.)

        if ($this->userModel->updateProfile($userId, $dataToUpdate)) {
            set_flash_message('success', 'Profile updated successfully!');
        } else {
            set_flash_message('error', 'Failed to update profile. Please try again.');
            $_SESSION['edit_profile_form_data'] = $_POST; // Store submitted data for repopulation
            header('Location: ' . $base_url . '/profile/edit'); // Redirect back to edit form on DB error
            exit;
        }

        header('Location: ' . $base_url . '/profile');
        exit;
    }

    /**
     * Show the form to change the user's password.
     */
    public function showPasswordForm($error = null)
    {
        if (!isset($_SESSION['user_id'])) { /* ... redirect ... */ exit; }

        $pageName = 'change_password';
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['password_error'] = $error; // Pass error message from handlePasswordChange

        require_once __DIR__ . '/../views/profile/change_password.php';
    }

    /**
     * Handle POST request to change the user's password.
     */
    public function handlePasswordChange()
    {
        if (!isset($_SESSION['user_id'])) { /* ... redirect ... */ exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/profile/password');
            exit;
        }
        $userId = $_SESSION['user_id'];

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->showPasswordForm('Please fill in all fields.'); return;
        }
        if (strlen($newPassword) < 6) {
            $this->showPasswordForm('New password must be at least 6 characters long.'); return;
        }
        if ($newPassword !== $confirmPassword) {
            $this->showPasswordForm('New passwords do not match.'); return;
        }

        $user = $this->userModel->findById($userId); // findById now fetches the password
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            $this->showPasswordForm('Incorrect current password.'); return;
        }

        $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        if ($this->userModel->updatePassword($userId, $newHashedPassword)) {
            set_flash_message('success', 'Password updated successfully!');
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/profile'); exit;
        } else {
            $this->showPasswordForm('Failed to update password. Please try again.'); return;
        }
    }

    /**
     * Displays the form to change the profile picture.
     */
    public function showPictureForm($error = null)
    {
        if (!isset($_SESSION['user_id'])) { /* ... redirect ... */ exit; }
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);

        if (!$user) {
            set_flash_message('error', 'Could not retrieve profile data.');
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/profile'); exit;
        }

        $pageName = 'change_picture';
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['current_picture_url'] = $user['profile_picture_url'];
        $GLOBALS['upload_error'] = $error; // From handlePictureUpdate

        require_once __DIR__ . '/../views/profile/change_picture.php';
    }

    /**
     * Handles the profile picture upload POST request.
     */
    public function handlePictureUpdate()
    {
        if (!isset($_SESSION['user_id'])) { /* ... redirect ... */ exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/profile/edit-picture'); exit;
        }
        $userId = $_SESSION['user_id'];
        $base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

        $uploadDirFilesystem = __DIR__ . '/../../public/uploads/avatars/';
        $uploadDirWeb = '/uploads/avatars/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB

        if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            // ... (error handling from previous response) ...
            $uploadError = match ($_FILES['profile_picture']['error'] ?? UPLOAD_ERR_NO_FILE) { /* ... */ };
            $this->showPictureForm($uploadError); return;
        }

        // ... (file validation: type, size from previous response) ...
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        // ...

        if (!in_array($_FILES['profile_picture']['type'], $allowedTypes)) { /* ... error ... */ $this->showPictureForm('Invalid file type...'); return; }
        if ($_FILES['profile_picture']['size'] > $maxFileSize) { /* ... error ... */ $this->showPictureForm('File size exceeds limit...'); return; }


        $newFileName = 'user_' . $userId . '_' . time() . '.' . $fileExtension;
        $destinationPathFilesystem = $uploadDirFilesystem . $newFileName;
        $destinationPathWeb = $uploadDirWeb . $newFileName;

        if (!is_dir($uploadDirFilesystem)) {
            if (!mkdir($uploadDirFilesystem, 0775, true)) { /* ... error ... */ $this->showPictureForm('Server error: Could not prepare upload directory.'); return; }
        }
        if (!is_writable($uploadDirFilesystem)) { /* ... error ... */ $this->showPictureForm('Server configuration error: Upload directory not writable.'); return; }


        if (move_uploaded_file($fileTmpPath, $destinationPathFilesystem)) {
            $user = $this->userModel->findById($userId);
            $oldPictureWebPath = $user['profile_picture_url'] ?? null;

            if ($this->userModel->updateProfilePicture($userId, $destinationPathWeb)) {
                set_flash_message('success', 'Profile picture updated successfully!');
                if ($oldPictureWebPath && $oldPictureWebPath !== '/images/default_avatar.png') {
                    $oldPictureFilesystemPath = __DIR__ . '/../../public' . $oldPictureWebPath;
                    if (file_exists($oldPictureFilesystemPath)) { @unlink($oldPictureFilesystemPath); }
                }
                header('Location: ' . $base_url . '/profile'); exit;
            } else {
                @unlink($destinationPathFilesystem);
                $this->showPictureForm('Database error: Could not update profile picture.'); return;
            }
        } else {
            error_log("Failed to move uploaded file '{$fileName}' to '{$destinationPathFilesystem}'");
            $this->showPictureForm('Error: Could not save the uploaded file.'); return;
        }
    }


    // ========== VEHICLE METHODS ==========
    public function showVehicles() {
        if (!isset($_SESSION['user_id'])) { /* ... redirect ... */ exit; }
        $userId = $_SESSION['user_id'];
        $vehicles = $this->vehicleModel->findByDriverId($userId);
        $pageName = 'manage_vehicles';
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['vehicles'] = $vehicles;
        require_once __DIR__ . '/../views/profile/manage_vehicles.php';
    }

    public function showAddVehicleForm() {
        if (!isset($_SESSION['user_id'])) { /* ... redirect ... */ exit; }
        $pageName = 'add_edit_vehicle';
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['form_action'] = 'add';
        $GLOBALS['vehicle'] = null;
        // Fetch user vehicles for dropdown if not already available globally
        $GLOBALS['userVehicles'] = $this->vehicleModel->findByDriverId($_SESSION['user_id']);
        $GLOBALS['form_data'] = $_SESSION['add_vehicle_form_data'] ?? [];
        unset($_SESSION['add_vehicle_form_data']);
        require_once __DIR__ . '/../views/profile/add_edit_vehicle.php';
    }

    public function handleAddVehicle() {
        if (!isset($_SESSION['user_id'])) { /* ... redirect ... */ exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { /* ... redirect ... */ exit; }
        $userId = $_SESSION['user_id'];
        $base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

        $data = [ /* ... collect from $_POST as before ... */
             'make' => trim($_POST['make'] ?? ''),
             'model' => trim($_POST['model'] ?? ''),
             'color' => trim($_POST['color'] ?? ''),
             'year' => empty($_POST['year']) ? null : (int)$_POST['year'],
             'type' => trim($_POST['type'] ?? ''),
             'license_plate' => trim($_POST['license_plate'] ?? ''),
             'seats_available' => (int)($_POST['seats_available'] ?? 0),
             'is_default' => isset($_POST['is_default']) ? 1 : 0,
        ];

        if (empty($data['make']) || empty($data['model']) || $data['seats_available'] <= 0) {
             set_flash_message('error', 'Make, Model, and Seats Available are required.');
             $_SESSION['add_vehicle_form_data'] = $_POST;
             header('Location: ' . $base_url . '/profile/add-vehicle'); exit;
        }

        $newVehicleId = $this->vehicleModel->add($userId, $data);
        if ($newVehicleId) {
             if ($data['is_default']) { $this->vehicleModel->setDefault($newVehicleId, $userId); }
            set_flash_message('success', 'Vehicle added successfully!');
            header('Location: ' . $base_url . '/profile/vehicles'); exit;
        } else {
            set_flash_message('error', 'Failed to add vehicle.');
            $_SESSION['add_vehicle_form_data'] = $_POST;
             header('Location: ' . $base_url . '/profile/add-vehicle'); exit;
        }
    }

    public function showEditVehicleForm(int $vehicleId) {
        if (!isset($_SESSION['user_id'])) { /* ... redirect ... */ exit; }
        $userId = $_SESSION['user_id'];
        $vehicle = $this->vehicleModel->findByIdAndDriver($vehicleId, $userId);
        if (!$vehicle) { /* ... error and redirect ... */ exit; }
        $pageName = 'add_edit_vehicle';
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['form_action'] = 'edit';
        $GLOBALS['vehicle'] = $vehicle; // Current vehicle data
        $GLOBALS['form_data'] = $_SESSION['edit_vehicle_form_data'] ?? $vehicle; // For repopulating
        unset($_SESSION['edit_vehicle_form_data']);
         // Fetch user vehicles for dropdown if needed (though not typically edited here)
        $GLOBALS['userVehicles'] = $this->vehicleModel->findByDriverId($userId);
        require_once __DIR__ . '/../views/profile/add_edit_vehicle.php';
    }

    public function handleUpdateVehicle(int $vehicleId) {
        if (!isset($_SESSION['user_id'])) { /* ... redirect ... */ exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { /* ... redirect ... */ exit; }
        $userId = $_SESSION['user_id'];
        $base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';

        $data = [ /* ... collect from $_POST as before ... */
            'make' => trim($_POST['make'] ?? ''),
             'model' => trim($_POST['model'] ?? ''),
             'color' => trim($_POST['color'] ?? ''),
             'year' => empty($_POST['year']) ? null : (int)$_POST['year'],
             'type' => trim($_POST['type'] ?? ''),
             'license_plate' => trim($_POST['license_plate'] ?? ''),
             'seats_available' => (int)($_POST['seats_available'] ?? 0),
             'is_default' => isset($_POST['is_default']) ? 1 : 0,
        ];

        if (empty($data['make']) || empty($data['model']) || $data['seats_available'] <= 0) {
             set_flash_message('error', 'Make, Model, and Seats Available are required.');
             $_SESSION['edit_vehicle_form_data'] = $_POST;
             header('Location: ' . $base_url . '/profile/edit-vehicle/' . $vehicleId); exit;
        }

        if ($this->vehicleModel->update($vehicleId, $userId, $data)) {
            if ($data['is_default']) { $this->vehicleModel->setDefault($vehicleId, $userId); }
            set_flash_message('success', 'Vehicle updated successfully!');
        } else {
            set_flash_message('error', 'Failed to update vehicle.');
            $_SESSION['edit_vehicle_form_data'] = $_POST;
            header('Location: ' . $base_url . '/profile/edit-vehicle/' . $vehicleId); exit;
        }
         header('Location: ' . $base_url . '/profile/vehicles'); exit;
    }

    public function handleDeleteVehicle(int $vehicleId) {
        if (!isset($_SESSION['user_id'])) { /* ... redirect ... */ exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { /* ... error ... */ exit; }
        $userId = $_SESSION['user_id'];
        if ($this->vehicleModel->delete($vehicleId, $userId)) {
            set_flash_message('success', 'Vehicle deleted successfully.');
        } else { set_flash_message('error', 'Failed to delete vehicle.'); }
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/profile/vehicles'); exit;
    }

    public function handleSetDefaultVehicle(int $vehicleId) {
        if (!isset($_SESSION['user_id'])) { /* ... redirect ... */ exit; }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { /* ... error ... */ exit; }
        $userId = $_SESSION['user_id'];
        if ($this->vehicleModel->setDefault($vehicleId, $userId)) {
            set_flash_message('success', 'Default vehicle updated.');
        } else { set_flash_message('error', 'Failed to update default vehicle.'); }
        header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/profile/vehicles'); exit;
    }

    // ========== RATINGS METHODS ==========
    public function showRatings() {
        if (!isset($_SESSION['user_id'])) { /* ... redirect ... */ exit; }
        $userId = $_SESSION['user_id'];
        $ratings = $this->ratingModel->findByTargetId($userId, 50, 0);
        $averageRating = $this->ratingModel->getAverageRating($userId);
        $ratingsCount = $this->ratingModel->getRatingsCount($userId);
        $pageName = 'view_ratings';
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['ratings'] = $ratings;
        $GLOBALS['averageRating'] = $averageRating;
        $GLOBALS['ratingsCount'] = $ratingsCount;
        require_once __DIR__ . '/../views/profile/view_ratings.php';
    }

    // ========== PUBLIC PROFILE ==========
    public function showPublicProfile(int $userId)
    {
        if ($userId <= 0) { http_response_code(404); echo "User ID invalid."; exit; }
        $userProfile = $this->userModel->findById($userId);
        if (!$userProfile) { /* ... 404 handling ... */ exit; }

        $vehicles = [];
        $upcomingTrips = [];
        if ($userProfile['is_driver']) {
            $vehicles = $this->vehicleModel->findByDriverId($userId);
            $filters = ['driver_id' => $userId, 'status' => 'scheduled'];
            $upcomingTrips = $this->tripModel->getAllAvailableTrips($filters, 't.departure_time ASC', 5);
        }
        $ratingsReceived = $this->ratingModel->findByTargetId($userId, 5, 0);
        $averageRating = $this->ratingModel->getAverageRating($userId);
        $ratingsCount = $this->ratingModel->getRatingsCount($userId);

        $viewData = [ /* ... as before ... */
            'user' => $userProfile,
            'vehicles' => $userProfile['is_driver'] ? $vehicles : [],
            'ratings' => $ratingsReceived,
            'averageRating' => $averageRating,
            'ratingsCount' => $ratingsCount,
            'upcomingTrips' => $upcomingTrips,
            'isVerified' => [
                'email' => (bool)($userProfile['is_email_verified'] ?? false),
                'phone' => (bool)($userProfile['is_phone_verified'] ?? false)
            ],
            'preferences' => [
                'smokes' => $userProfile['pref_smokes'] ?? 'Not specified',
                'pets' => $userProfile['pref_pets'] ?? 'Not specified',
                'music' => $userProfile['pref_music'] ?? 'Not specified',
                'talk' => $userProfile['pref_talk'] ?? 'Not specified',
            ],
        ];
        $pageName = 'public_profile';
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['publicProfileData'] = $viewData;
        require_once __DIR__ . '/../views/profile/public_profile.php';
    }
} // End ProfileController Class
?>