<?php

namespace Core;

class Router
{
    public function handleRequest()
    {
        $requestUri = strtok($_SERVER['REQUEST_URI'], '?');
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';
        $path = $requestUri;

        // Normalize the path
        if ($basePath && strpos($requestUri, $basePath) === 0) {
            $path = substr($requestUri, strlen($basePath));
        }
        if (empty($path)) {
            $path = '/'; // Ensure root path is represented by '/'
        }
        if ($path !== '/' && substr($path, -1) === '/') {
            $path = rtrim($path, '/'); // Remove trailing slash unless it's the root
        }

        // Ensure session is started before routing potentially uses it
        if (session_status() === PHP_SESSION_NONE) {
             session_start();
        }

        // Use switch(true) to allow both exact matches and regex later
        switch (true) {

            // --- Exact Matches ---
            case ($path === '/' || $path === ''):
                $controller = new \App\Controllers\IndexController();
                $controller->index();
                break;

            case ($path === '/signup'):
                $controller = new \App\Controllers\SignupController();
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->handleSignup();
                } else {
                    $controller->showSignupForm(null, null, []);
                }
                break;

            case ($path === '/login'):
                $controller = new \App\Controllers\AuthController();
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller->handleLogin();
                } else {
                    $error = $_GET['error'] ?? null;
                    $email = $_GET['email'] ?? null;
                    $controller->showLoginForm($error, $email);
                }
                break;

            // --- Profile Routes ---
            case ($path === '/profile'):
                // Authentication check should ideally be in the controller's method itself
                // But doing a basic check here before instantiating is also common
                if (!isset($_SESSION['user_id'])) {
                    header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login?redirect=profile'); // Redirect to login
                    exit;
                }
                // Instantiate and call the ProfileController method
                $controller = new \App\Controllers\ProfileController();
                $controller->index(); // This method loads the profile view
                break;

            case ($path === '/profile/edit'):
                 if (!isset($_SESSION['user_id'])) { header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;}
                 $controller = new \App\Controllers\ProfileController();
                 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                      $controller->update();
                 } else {
                     $controller->edit();
                 }
                 break;

            case ($path === '/profile/password'):
                if (!isset($_SESSION['user_id'])) { header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;}
                $controller = new \App\Controllers\ProfileController(); // Or AuthController if preferred
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $controller->handlePasswordChange();
                } else {
                    $controller->showPasswordForm();
                }
                break;

                       // --- Profile Picture Route ---
                       case ($path === '/profile/edit-picture'): // <<< Path defined here
                        if (!isset($_SESSION['user_id'])) { header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;}
                        $controller = new \App\Controllers\ProfileController();
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $controller->handlePictureUpdate();
                        } else {
                            $controller->showPictureForm();
                        }
                        break;
            
                        // --- Vehicle Management Routes ---
            case ($path === '/profile/vehicles'): // List vehicles
                if (!isset($_SESSION['user_id'])) { header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;}
                $controller = new \App\Controllers\ProfileController();
                $controller->showVehicles();
                break;

           case ($path === '/profile/add-vehicle'): // Show add form / Handle add POST
                 if (!isset($_SESSION['user_id'])) { header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;}
                 $controller = new \App\Controllers\ProfileController();
                 if ($_SERVER['REQUEST_METHOD'] === 'POST') { $controller->handleAddVehicle(); } else { $controller->showAddVehicleForm(); }
                 break;

           case (preg_match('/^\/profile\/edit-vehicle\/(\d+)$/', $path, $matches) ? true : false): // Show edit form / Handle edit POST
                 $vehicleId = (int)$matches[1];
                 if (!isset($_SESSION['user_id'])) { header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;}
                 $controller = new \App\Controllers\ProfileController();
                 if ($_SERVER['REQUEST_METHOD'] === 'POST') { $controller->handleUpdateVehicle($vehicleId); } else { $controller->showEditVehicleForm($vehicleId); }
                 break;

            case (preg_match('/^\/profile\/delete-vehicle\/(\d+)$/', $path, $matches) ? true : false): // Handle delete (Requires POST)
                 $vehicleId = (int)$matches[1];
                 if (!isset($_SESSION['user_id'])) { header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;}
                 $controller = new \App\Controllers\ProfileController();
                 // Usually triggered by a form submission with POST method
                  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                      $controller->handleDeleteVehicle($vehicleId);
                  } else {
                      // Maybe show a confirmation page first? Or redirect back with error.
                      http_response_code(405); // Method Not Allowed
                      echo "Deletion must be done via POST.";
                  }
                 break;

           case (preg_match('/^\/profile\/set-default-vehicle\/(\d+)$/', $path, $matches) ? true : false): // Handle set default (Requires POST)
                 $vehicleId = (int)$matches[1];
                  if (!isset($_SESSION['user_id'])) { header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;}
                  $controller = new \App\Controllers\ProfileController();
                   if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                      $controller->handleSetDefaultVehicle($vehicleId);
                  } else {
                      http_response_code(405);
                      echo "Setting default vehicle must be done via POST.";
                  }
                  break;



            // --- Trip Routes ---
            case ($path === '/trips'): // Browse all available trips
                $controller = new \App\Controllers\TripController();
                $controller->browse();
                break;

            case ($path === '/trip/create'): // Show create trip form (GET) / Handle creation (POST)
                 if (!isset($_SESSION['user_id'])) { header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;}
                 $controller = new \App\Controllers\TripController();
                 if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                     $controller->handleCreateTrip();
                 } else {
                     $controller->showCreateTripForm();
                 }
                 break;

            case (preg_match('/^\/trip\/(\d+)$/', $path, $matches) ? true : false): // Show Trip Details
                $tripId = (int)$matches[1];
                $controller = new \App\Controllers\TripController();
                $controller->show($tripId);
                break;

            case ($path === '/trip/book'): // Handle Booking POST request
                 if (!isset($_SESSION['user_id'])) { header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;}
                 if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo "Booking must be via POST."; exit; }
                 $controller = new \App\Controllers\TripController();
                 $controller->handleBooking();
                 break;

           // --- Ratings Route ---
           case ($path === '/profile/ratings'): // View ratings received
                if (!isset($_SESSION['user_id'])) { header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;}
                $controller = new \App\Controllers\ProfileController();
                $controller->showRatings();
                break;
                case ($path === '/trip/book'): // Handle Booking POST request
                    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                       // Redirect or show error if accessed via GET
                       http_response_code(405);
                       echo "Booking must be done via POST.";
                       exit;
                    }
                    // Authentication required
                    if (!isset($_SESSION['user_id'])) { header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/login'); exit;}
                    // Instantiate TripController and call booking handler
                    $controller = new \App\Controllers\TripController(); // << CREATE THIS CONTROLLER
                    $controller->handleBooking();
                    break;

    
                 
            // --- Booking Routes ---
            case ($path === '/bookings'): // List user's bookings
                $controller = new \App\Controllers\BookingController(); // << CREATE BookingController
                $controller->index();
                break;

            case (preg_match('/^\/booking\/cancel\/(\d+)$/', $path, $matches) ? true : false): // Cancel a booking
                $bookingId = (int)$matches[1];
                // This should be a POST request for security
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $controller = new \App\Controllers\BookingController();
                    $controller->cancel($bookingId);
                } else {
                    http_response_code(405); // Method Not Allowed
                    echo "Cancellation must be done via POST.";
                }
                break;

            case (preg_match('/^\/booking\/pay\/(\d+)$/', $path, $matches) ? true : false): // Initiate payment for a booking
                $bookingId = (int)$matches[1];
                $controller = new \App\Controllers\BookingController();
                $controller->pay($bookingId);
                break;

                case ($path === '/payment'):
                    // Authentication is handled within PaymentController constructor
                    $controller = new \App\Controllers\PaymentController(); // USE PaymentController
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
                        $controller->handlePaymentSubmission();
                    } else {
                        $controller->showPaymentPage();
                    }
                    break;

                    // --- Public User Profile Route (New) ---
            case (preg_match('/^\/user\/(\d+)$/', $path, $matches) ? true : false):
                $userId = (int)$matches[1];
                $controller = new \App\Controllers\ProfileController(); // Or a new UserController
                $controller->showPublicProfile($userId);
                break;

                 // --- Static Pages Routes ---
                 case ($path === '/legal'):
                     $controller = new \App\Controllers\StaticPageController(); // <-- CHANGE HERE
                     $controller->legalNotice();
                     break;
     
                 case ($path === '/sitemap'):
                     $controller = new \App\Controllers\StaticPageController(); // <-- CHANGE HERE
                     $controller->sitemap();
                     break;
     
                 case ($path === '/terms'):
                     $controller = new \App\Controllers\StaticPageController(); // <-- CHANGE HERE
                     $controller->termsAndConditions();
                     break;
     
                 case ($path === '/privacy-policy'): // Example new route
                      $controller = new \App\Controllers\StaticPageController(); // <-- CHANGE HERE
                      $controller->privacyPolicy();
                      break;
     
                 case ($path === '/contact'): // Example for contact page
                      $controller = new \App\Controllers\StaticPageController(); // <-- CHANGE HERE
                      // If contact form has POST logic, handle it:
                      // if ($_SERVER['REQUEST_METHOD'] === 'POST') { $controller->handleContactForm(); } else { $controller->contact(); }
                      $controller->contact();
                      break;
     
                 case ($path === '/faq'): // Route for FAQ
                      $controller = new \App\Controllers\StaticPageController(); // <-- CHANGE HERE
                      $controller->faq();
                      break;

                      case ($path === '/logout'):
                        $controller = new \App\Controllers\AuthController();
                        $controller->handleLogout();
                        break;
            // --- Default Route (404 Not Found) ---
            default:
                http_response_code(404);
                $GLOBALS['pageName'] = '404'; // Assuming you have a 404.css
                // Check if 404 view exists, otherwise echo simple message
                $view404 = __DIR__ . '/../app/views/errors/404.php';
                if (file_exists($view404)) {
                    require_once $view404; // Create this view file
                } else {
                     echo "404 - Page not found: " . htmlspecialchars($path);
                }
                break;
        } // End switch(true)
    } // End handleRequest()
} // End Router class