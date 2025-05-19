<?php

namespace App\Controllers;

use App\Models\User;
use PDO;

require_once __DIR__ . '/../helpers/flash_messages.php';


class AuthController
{
    private $pdo;

    public function __construct()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Database connection
        $dbConnectionPath = __DIR__ . '/../../config/db_connection.php';
        if (file_exists($dbConnectionPath)) {
            $this->pdo = require $dbConnectionPath;
            if (!($this->pdo instanceof PDO)) {
                error_log("FATAL: AuthController::__construct() - Failed to load PDO object from db_connection.php.");
                die("A critical application error occurred. Please contact support. (Error Code: AUC01)");
            }
        } else {
            error_log("FATAL: AuthController::__construct() - db_connection.php not found at {$dbConnectionPath}");
            die("A critical application error occurred. Please contact support. (Error Code: AUC02)");
        }
    }

    /**
     * Displays the login form.
     * @param string|null $error Error message to display.
     * @param string|null $email Email to pre-fill in the form.
     */
    public function showLoginForm($error = null, $email = null)
    {
        $pageName = 'login';
        $GLOBALS['pageName'] = $pageName;
        $GLOBALS['login_error_message'] = $error;
        $GLOBALS['login_form_email'] = $email;
        require_once __DIR__ . '/../views/auth/login.php';
    }

    /**
     * Handles the POST request from the login form.
     */
    public function handleLogin()
    {
        $error = null;
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Basic Validation
        if (empty($email) || empty($password)) {
            $error = 'Please enter your email and password.';
            $this->showLoginForm($error, $email);
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
            $this->showLoginForm($error, $email);
            return;
        }

        // Find user by email
        $userModel = new User($this->pdo);
        $user = $userModel->findByEmail($email);

        // Verify user, password, and account status
        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, check account status
            if ($user['account_status'] === 'active') {
                // Start session
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_first_name'] = $user['first_name']; // Use the correct key
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = (bool)$user['is_admin']; // Store admin status if needed

                // Redirect to profile or dashboard
                $redirectUrl = (defined('BASE_PATH') ? BASE_PATH : '') . '/profile';
                header('Location: ' . $redirectUrl);
                exit;

            } else {
                 // Account is not active (suspended, deleted)
                 $error = 'Your account is currently inactive. Please contact support.';
                 // Optionally log this attempt: error_log("Login attempt for inactive account: {$email}");
                 $this->showLoginForm($error, $email);
                 return;
            }
        } else {
            // Invalid email or password
            $error = 'Incorrect email or password.';
            $this->showLoginForm($error, $email);
            return;
        }
    }

    public function handleLogout()
    {
        // Ensure session is active before trying to manipulate it
        if (session_status() === PHP_SESSION_NONE) {
            session_start(); // Start session if not already started
        }

        // 1. Unset all of the session variables.
        $_SESSION = array();

        // 2. If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, // Set cookie to expire in the past
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        // 3. Finally, destroy the session.
        session_destroy();

        // Optional: Set a flash message to inform the user (if using flash messages)
        // The set_flash_message function needs to be available here
        set_flash_message('success', 'Vous avez été déconnecté avec succès.'); // "You have been successfully logged out."

        // 4. Redirect to the homepage or login page.
        $base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
        $redirectUrl = $base_url === '' ? '/' : $base_url; // Redirect to homepage
        // Or redirect to login: $redirectUrl = $base_url . '/login';

        header('Location: ' . $redirectUrl);
        exit; // Important: Stop script execution immediately after sending the redirect header
    }
}
?>