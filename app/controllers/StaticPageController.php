<?php

namespace App\Controllers;

// No models usually needed for purely static pages, but session might be for header/footer context
// use PDO; // Only if some static pages might need dynamic elements from DB later

class StaticPageController
{
    
    public function __construct()
    {
        // Example if you ever need PDO for a "static" page that has a small dynamic part
        // if (session_status() === PHP_SESSION_NONE) { session_start(); }
        // $dbConnectionPath = __DIR__ . '/../../config/db_connection.php';
        // if (file_exists($dbConnectionPath)) {
        //     $this->pdo = require $dbConnectionPath;
        //     if (!($this->pdo instanceof PDO)) { die("Error STC01"); }
        // } else { die("Error STC02"); }
    }
    

    public function legalNotice()
    {
        $pageName = 'legal_notice'; // Matches legal_notice.css
        $GLOBALS['pageName'] = $pageName;
        require_once __DIR__ . '/../views/legal_notice/legal_notice.php';
    }

    public function sitemap()
    {
        $pageName = 'sitemap'; // Matches sitemap.css
        $GLOBALS['pageName'] = $pageName;
        require_once __DIR__ . '/../views/sitemap/sitemap.php';
    }

    public function termsAndConditions()
    {
        $pageName = 'terms_conditions'; // Matches terms_conditions.css
        $GLOBALS['pageName'] = $pageName;
        require_once __DIR__ . '/../views/legal_notice/terms_conditions.php';
    }

    public function privacyPolicy() // Example for a new static page
    {
        $pageName = 'privacy_policy'; // Matches privacy_policy.css
        $GLOBALS['pageName'] = $pageName;
        require_once __DIR__ . '/../views/legal_notice/privacy_policy.php'; // Create this view
    }

    public function contact() // Example: Moving contact page to this controller
    {
        $pageName = 'contact'; // Matches contact.css
        $GLOBALS['pageName'] = $pageName;
        // For a contact page with a form, you might have POST handling here or in a separate ContactController
        require_once __DIR__ . '/../views/contact/contact.php';
    }

    // Add more methods for other static pages: about_us, faq_page_controller, etc.
    public function faq()
    {
        $pageName = 'faq';
        $GLOBALS['pageName'] = $pageName;
        // Note: Your existing FAQ seems to be a self-contained module.
        // To integrate it properly into the MVC, this controller method would
        // fetch FAQ data from an FaqModel and pass it to an MVC-style view.
        // For now, if you want to link to the existing standalone FAQ:
        // header('Location: ' . (defined('BASE_PATH') ? BASE_PATH : '') . '/views/faq/index.html'); exit;
        // Or, if faq.php is the entry point for your MVC FAQ page:
        require_once __DIR__ . '/../views/faq/faq.php';
    }

}
?>