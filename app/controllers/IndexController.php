<?php

namespace App\Controllers;

use App\Models\TripModel;
use PDO;

class IndexController
{
    private $pdo;
    private $tripModel;

    public function __construct()
    {
        // Get PDO connection (similar to SignupController)
        $dbConnectionPath = __DIR__ . '/../../config/db_connection.php';
        if (file_exists($dbConnectionPath)) {
            $this->pdo = require $dbConnectionPath;
            if (!($this->pdo instanceof PDO)) {
                error_log("FATAL: IndexController::__construct() - Failed to load PDO object from db_connection.php.");
                die("A critical application error occurred. Please contact support. (Error Code: IDC01)");
            }
            // Instantiate the model
            $this->tripModel = new TripModel($this->pdo);

        } else {
            error_log("FATAL: IndexController::__construct() - db_connection.php not found at {$dbConnectionPath}");
            die("A critical application error occurred. Please contact support. (Error Code: IDC02)");
        }
    }

    public function index()
    {
        // Set page name for CSS loading in header.php
        // Needs to match your CSS filename (accueil.css or index.css)
        $pageName = 'accueil'; // <--- SET TO 'accueil' TO MATCH accueil.css
        $GLOBALS['pageName'] = $pageName;

        // Fetch only 2 popular routes for the homepage overview
        $popularRoutes = $this->tripModel->getPopularRoutesDetails(2);

        // Pass data to the view using GLOBALS (as per current project pattern)
        $GLOBALS['popular_routes'] = $popularRoutes;

        // Load the view
        require_once __DIR__ . '/../views/home/index.php';
    }
}
?>