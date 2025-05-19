<?php

namespace App\Controllers;

use App\Models\User; // Use the User model
use PDO;

require_once __DIR__ . '/../helpers/flash_messages.php';


class SignupController
{
    private $pdo;

    public function __construct()
    {
        $dbConnectionPath = __DIR__ . '/../../config/db_connection.php';
        if (file_exists($dbConnectionPath)) {
            $this->pdo = require $dbConnectionPath;
            if (!($this->pdo instanceof PDO)) {
                error_log("FATAL: SignupController::__construct() - Failed to load PDO object from db_connection.php.");
                die("A critical application error occurred. Please contact support. (Error Code: SCC01)");
            }
        } else {
            error_log("FATAL: SignupController::__construct() - db_connection.php not found at {$dbConnectionPath}");
            die("A critical application error occurred. Please contact support. (Error Code: SCC02)");
        }
    }

    public function showSignupForm($error = null, $success = null, $formData = [])
    {
        $pageName = 'signup';
        $GLOBALS['pageName'] = $pageName;

        $GLOBALS['signup_error_message'] = $error;
        $GLOBALS['signup_success_message'] = $success;
        $GLOBALS['signup_form_data'] = $formData;

        require_once __DIR__ . '/../views/signup/signup.php';
    }

    public function handleSignup()
    {
        $error = null;
        // $success = null; // Success will be handled by flash message upon redirect
        $formData = $_POST;
        $base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';


        // 1. Retrieve and sanitize/trim input data
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // 2. Validate Input Data
        if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
            $error = 'Veuillez remplir tous les champs.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "L'adresse e-mail n'est pas valide.";
        } elseif (strlen($password) < 6) {
            $error = 'Le mot de passe doit contenir au moins 6 caractères.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Les mots de passe ne correspondent pas.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

            if (!$this->pdo) {
                $error = "Erreur interne: La connexion à la base de données n'est pas disponible.";
                error_log("SignupController::handleSignup() - \$this->pdo is not set or invalid before User model instantiation.");
                $this->showSignupForm($error, null, $formData);
                return;
            }

            $userModel = new User($this->pdo);
            $creationResult = $userModel->create($firstName, $lastName, $email, $hashedPassword);

            if ($creationResult === true) {
                // --- SUCCESS: LOG THE USER IN AND REDIRECT ---
                // Fetch the newly created user to get their ID for the session
                $newUser = $userModel->findByEmail($email); // findByEmail should return the user array

                if ($newUser) {
                    // Start session if not already started (Router usually does this, but good check)
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    session_regenerate_id(true); // Prevent session fixation

                    $_SESSION['user_id'] = $newUser['id'];
                    $_SESSION['user_first_name'] = $newUser['first_name'];
                    $_SESSION['user_email'] = $newUser['email'];
                    $_SESSION['is_admin'] = (bool)($newUser['is_admin'] ?? 0);

                    set_flash_message('success', 'Compte créé avec succès ! Bienvenue, ' . htmlspecialchars($newUser['first_name']) . '.');
                    header('Location: ' . $base_url . '/profile'); // Redirect to profile page
                    exit; // Important: Stop script execution after redirect
                } else {
                    // This case is unlikely if create() succeeded but findByEmail() failed immediately after
                    error_log("Signup successful for {$email} but failed to retrieve new user details for auto-login.");
                    set_flash_message('warning', 'Compte créé, mais une erreur est survenue lors de la connexion automatique. Veuillez vous connecter manuellement.');
                    header('Location: ' . $base_url . '/login'); // Redirect to login
                    exit;
                }
                // --------------------------------------------
            } elseif ($creationResult === 'email_exists') {
                $error = "Cette adresse e-mail est déjà utilisée. Veuillez en choisir une autre.";
            } else {
                $error = "Une erreur interne s'est produite lors de la création de votre compte. Veuillez réessayer plus tard.";
                error_log("Signup failed for email: {$email}. User::create returned: " . var_export($creationResult, true));
            }
        }

        // If there was an error, re-display the signup form with the error message
        $this->showSignupForm($error, null, $formData);
    }
}
?>