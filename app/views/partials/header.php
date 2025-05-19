<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="HopOn : Le covoiturage simple, rapide et économique. Réservez ou proposez vos trajets en quelques clics.">
    <meta name="keywords" content="covoiturage, HopOn, trajets, réserver, partager, voiture, écoresponsable, voyage">
    <meta name="author" content="HopOn Team">

    

    <!-- Favicon Configuration -->
    <link rel="icon" href="<?= $base_url ?>/favicon/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="manifest" href="/favicon/site.webmanifest">
    <meta name="msapplication-TileColor" content="#6FC8F0"> <!-- Primary Light Blue from charte -->
    <meta name="theme-color" content="#FFFFFF"> <!-- Secondary White from charte -->

    <title>HopOn - Covoiturage Facile et Économique</title>

    <!-- CSS Links - Order: Global -> Components -> Page-Specific -->

    <!-- 1. Global Stylesheet -->
    <link rel="stylesheet" href="/css/main.css">
    

    <!-- 2. Component-specific stylesheets (header & footer) -->
    <link rel="stylesheet" href="/css/header.css">
    <link rel="stylesheet" href="/css/footer.css">



    <!-- 3. Page-specific stylesheet (Dynamically included) -->
    <?php
    $pageName = $GLOBALS['pageName'] ?? ''; // Get pageName, default to empty
    if (!empty($pageName) && preg_match('/^[a-zA-Z0-9_-]+$/', $pageName)) {
        $pageCssFilename = htmlspecialchars($pageName) . ".css";
        // Construct the actual file system path to check if the file exists
        // Path relative to THIS file (app/views/partials/header.php)
        $fileSystemPathToCss = __DIR__ . '/../../../public/css/' . $pageCssFilename;

        if (file_exists($fileSystemPathToCss)) {
            // Construct the web accessible URL path
            $webPathToCss = '/css/' . $pageCssFilename;
            echo '<link rel="stylesheet" href="' . $webPathToCss . '">' . "\n"; // Added newline for readability in source
        }
    }
    ?>
</head>
<body>
    <?php
    // Display flash messages (ensure helper is included)
    require_once __DIR__ . '/../../helpers/flash_messages.php';
    display_flash_messages();
    ?>
    <header class="site-header" role="banner">
        <div class="container header-container">
            <div class="header-left">
                <a href="<?= $base_url === '' ? '/' : $base_url ?>" class="logo-link" aria-label="Page d'accueil HopOn">
                    <img src="/images/HopOn_logo.jpg" alt="Logo HopOn" class="site-logo">
                </a>
                <nav class="main-navigation" role="navigation" aria-label="Navigation principale">
                    <ul class="nav-links">
                        <li><a href="<?= $base_url === '' ? '/' : $base_url ?>" class="<?= ($pageName === 'index' || $pageName === 'accueil' || $pageName === '') ? 'active' : '' ?>">Accueil</a></li>
                        <li><a href="<?= $base_url ?>/trips" class="<?= $pageName === 'trips' ? 'active' : '' ?>">Trajets</a></li>
                        <li><a href="<?= $base_url ?>/bookings" class="<?= ($pageName === 'my_bookings' || $pageName === 'reservation') ? 'active' : '' ?>">Réservations</a></li>
                        <li><a href="<?= $base_url ?>/comment" class="<?= $pageName === 'comment' ? 'active' : '' ?>">Commentaires</a></li>
                        <li><a href="<?= $base_url ?>/contact" class="<?= $pageName === 'contact' ? 'active' : '' ?>">Contact</a></li>
                    </ul>
                </nav>
            </div>
            <div class="header-right">
                <?php if (isset($_SESSION['user_id'])): // Check if user is logged in ?>
                    <span class="welcome-message">
                        Bonjour, <?= htmlspecialchars($_SESSION['user_first_name'] ?? 'Utilisateur') ?> !
                    </span>
                    <a href="<?= $base_url ?>/profile" class="btn btn-secondary" aria-label="Mon Profil">
                        Mon Profil
                    </a>
                <a href="<?= $base_url ?>/logout" class="btn btn-outline" aria-label="Se déconnecter">
                    Se déconnecter
                </a>
                <?php else: // User is not logged in ?>
                    <a href="<?= $base_url ?>/login" class="btn btn-outline" aria-label="Connexion">
                        Connexion
                    </a>
                    <a href="<?= $base_url ?>/signup" class="btn btn-primary" aria-label="Créer un compte">
                        Créer un compte
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>