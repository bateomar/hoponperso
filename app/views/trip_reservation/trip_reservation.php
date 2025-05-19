<?php

// Définir la locale en français pour les dates
setlocale(LC_TIME, 'fr_FR.UTF-8', 'fra');

$env = "production";

if ($env == "production") {
    $servername = "herogu.garageisep.com";
    $username = "d3UG45BFAl_hopon";
    $password = "MNYObOzVNqptcHLu";
    $dbname = "NvChx418Vk_hopon";
} else {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "NvChx418Vk_hopon";
}

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Erreur de connexion: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// Sécurisation de l'ID trajet
$trip_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Vérifie si l'utilisateur est connecté
$utilisateur_connecte = isset($_SESSION['user_id']);
$utilisateur_id = $utilisateur_connecte ? $_SESSION['user_id'] : 0;

$message = "";

if (isset($_POST['reserver']) && $utilisateur_connecte) {
    $sql_verif_type = "SELECT user_type FROM users WHERE id = ?";
    $stmt_verif = $conn->prepare($sql_verif_type);
    $stmt_verif->bind_param("i", $utilisateur_id);
    $stmt_verif->execute();
    $result_verif = $stmt_verif->get_result();
    $user_data = $result_verif->fetch_assoc();

    if ($user_data && $user_data['user_type'] == 'member') {
        $sql_seats = "SELECT seats_offered, seats_booked FROM trips WHERE id = ?";
        $stmt_seats = $conn->prepare($sql_seats);
        $stmt_seats->bind_param("i", $trip_id);
        $stmt_seats->execute();
        $result_seats = $stmt_seats->get_result();
        $trip_seats = $result_seats->fetch_assoc();

        if ($trip_seats && $trip_seats['seats_offered'] > $trip_seats['seats_booked']) {
            // Créer une nouvelle réservation
            $sql_book = "INSERT INTO bookings (trip_id, passenger_id, booking_date, status) VALUES (?, ?, NOW(), 'pending_confirmation')";
            $stmt_book = $conn->prepare($sql_book);
            $stmt_book->bind_param("ii", $trip_id, $utilisateur_id);

            if ($stmt_book->execute()) {
                // Mettre à jour le nombre de places réservées dans la table trips
                $sql_update_seats = "UPDATE trips SET seats_booked = seats_booked + 1 WHERE id = ?";
                $stmt_update_seats = $conn->prepare($sql_update_seats);
                $stmt_update_seats->bind_param("i", $trip_id);
                $stmt_update_seats->execute();
                $message = "<div class='alert alert-success'>Votre demande de réservation a été envoyée.</div>";
            } else {
                $message = "<div class='alert alert-danger'>Erreur lors de la réservation. Veuillez réessayer.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Plus de places disponibles pour ce trajet.</div>";
        }
    } else {
        $message = "<div class='alert alert-danger'>Seuls les membres peuvent réserver un trajet.</div>";
    }
}

$trip = null;
$driver = null;
$vehicle = null;
$nb_ratings = 0;
$average_rating = 0;

if ($trip_id > 0) {
    $sql = "SELECT t.*,
                   u.first_name,
                   u.last_name,
                   u.id AS driver_id,
                   v.make,
                   v.model,
                   v.color,
                   u.pref_smokes,
                   u.pref_pets,
                   u.pref_music,
                   u.pref_talk
            FROM trips t
            JOIN users u ON t.driver_id = u.id
            LEFT JOIN vehicles v ON u.id = v.driver_id
            WHERE t.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $trip_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $trip = $result->fetch_assoc();
        $driver_id = $trip['driver_id'];

        // Définir les variables $jour_semaine et $jour_mois
        $departure_time = new DateTime($trip['departure_time']);
        $jour_semaine = strftime('%A', $departure_time->getTimestamp());
        $jour_mois = strftime('%d %B %Y', $departure_time->getTimestamp());

        $vehicle = [
            'make' => $trip['make'],
            'model' => $trip['model'],
            'color' => $trip['color']
        ];

        $sql_ratings = "SELECT COUNT(*) as nb_ratings, AVG(score) as average
                        FROM ratings
                        WHERE target_id = ?";
        $stmt_ratings = $conn->prepare($sql_ratings);
        $stmt_ratings->bind_param("i", $driver_id);
        $stmt_ratings->execute();
        $result_ratings = $stmt_ratings->get_result();

        if ($result_ratings->num_rows > 0) {
            $ratings_data = $result_ratings->fetch_assoc();
            $nb_ratings = $ratings_data['nb_ratings'];
            $average_rating = number_format($ratings_data['average'], 1);
        }
    } else {
        $message = "<div class='alert alert-danger'>Trajet non trouvé.</div>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<base href="/HopOn/">    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HopOn - Réservation de trajet</title>
    <?php
    // Chemin fichiers CSS
    $mainstyle = "public/css/trip_reservation.css";

    // Paramètre de version pour éviter la mise en cache
    $version = "1.0.3";

    // Inclusion fichier CSS dans l'en-tête
    echo '<link rel="stylesheet" href="' . $mainstyle . '?v=' . $version . '">';
    echo '<link rel="stylesheet" href="public/css/footer.css?v=' . $version . '">';
    ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <a href="index.php" class="logo text-decoration-none">HopOn</a>
                <div>
                    <?php if ($utilisateur_connecte): ?>
                        <a href="profil.php" class="btn btn-outline-primary me-2">Mon profil</a>
                        <a href="deconnexion.php" class="btn btn-outline-secondary">Déconnexion</a>
                    <?php else: ?>
                        <a href="connexion.php" class="btn btn-outline-primary me-2">Connexion</a>
                        <a href="inscription.php" class="btn btn-outline-secondary">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <div class="container my-4">
        <?php echo $message; ?>

        <?php if ($trip): ?>
            <h1 class="mb-4">Trajet du <?php echo $jour_semaine . " " . $jour_mois; ?></h1>

            <div class="row">
                <div class="col-lg-8">
                    <div class="trajet-card">
                        <div class="trajet-info">
                            <div class="trajet-horaire">
                                <div class="trajet-heure"><?php echo substr($trip['departure_time'], 11, 5); ?></div>
                                <div class="trajet-separation"></div>
                                <div class="trajet-heure">
                                    <?php
                                    // Calcul d'une heure d'arrivée approximative (5h de trajet par exemple)
                                    $arrival_time = date('H:i', strtotime($trip['arrival_time_estimated']));
                                    echo $arrival_time;
                                    ?>
                                </div>
                            </div>

                            <div class="trajet-lieu">
                                <div>
                                    <div class="trajet-ville"><?php echo $trip['departure_location']; ?></div>
                                    <div class="trajet-detail">Point de rendez-vous à définir</div>
                                </div>
                                <div class="mt-3">
                                    <div class="trajet-ville"><?php echo $trip['arrival_location']; ?></div>
                                    <div class="trajet-detail">Point d'arrivée à définir</div>
                                </div>
                            </div>
                        </div>

                        <div class="conducteur-info">
                            <div class="conducteur-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <h5><?php echo $trip['first_name']; ?></h5>
                                <div>
                                    <span class="conducteur-note">
                                        <i class="fas fa-star"></i> <?php echo $average_rating; ?>
                                    </span>
                                    <span class="text-muted"><?php echo $nb_ratings; ?> avis</span>
                                </div>
                            </div>
                        </div>

                        <?php if ($vehicle): ?>
                        <div class="vehicule-info">
                            <p><i class="fas fa-car preference-icon"></i> <?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?> - <?php echo $vehicle['color']; ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="preferences mt-3">
                            <?php if (isset($trip)): ?>
                                <?php
                                $preferences_affichage = [
                                    'pref_smokes' => ['icon' => 'fa-smoking-ban', 'label' => ' Cigarette', 'values' => ['No preference' => 'Non spécifié', 'No smoking' => 'Non']],
                                    'pref_pets' => ['icon' => 'fa-paw', 'label' => ' Animaux', 'values' => ['Not specified' => 'Non spécifié', 'No pets' => 'Non', 'No preference' => 'Non spécifié', 'No preference ' => 'Non spécifié']],
                                    'pref_music' => ['icon' => 'fa-music', 'label' => ' Musique', 'values' => ['Not specified' => 'Non spécifié', 'Pop' => 'Pop', 'Rock' => 'Rock', 'None' => 'Pas de musique']],
                                    'pref_talk' => ['icon' => 'fa-comments', 'label' => ' Conversation', 'values' => ['Not specified' => 'Non spécifié', 'Talkative' => 'J\'aime discuter', 'Silent' => 'Je préfère le silence', 'Flexible' => 'Flexible', 'Not specified ' => 'Non spécifié']],
                                ];

                                foreach ($preferences_affichage as $key => $pref_data):
                                    if (isset($trip[$key])):
                                        echo '<p><i class="fas ' . $pref_data['icon'] . ' preference-icon"></i> ';
                                        echo htmlspecialchars($pref_data['label']) . ' : ';
                                        if (isset($pref_data['values'][$trip[$key]])) {
                                            echo htmlspecialchars($pref_data['values'][$trip[$key]]);
                                        } else {
                                            echo 'Non spécifié';
                                        }
                                        echo '</p>';
                                    endif;
                                endforeach;
                                ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="reservation-card">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="trajet-prix-label">Prix par passager</span>
                                <div class="trajet-prix"><?php echo number_format($trip['price'], 2, ',', ' '); ?> €</div>
                            </div>
                        </div>

                        <div class="trajet-places">
                            <i class="fas fa-user-friends places-icon"></i>
                            <span class="places-text"><?php echo $trip['seats_offered'] - $trip['seats_booked']; ?> place(s) disponible(s)</span>
                        </div>

                        <?php if ($utilisateur_connecte): ?>
                            <?php if ($trip['status'] == 'scheduled' && ($trip['seats_offered'] > $trip['seats_booked'])): ?>
                                <form method="post">
                                    <button type="submit" name="reserver" class="btn-reserver">
                                        Demande de réservation
                                    </button>
                                </form>
                            <?php else: ?>
                                <button disabled class="btn-reserver bg-secondary">
                                    Trajet complet
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="connexion.php?redirect=reservation_trajet.php?id=<?php echo $trip_id; ?>" class="btn-reserver d-block text-center text-decoration-none">
                                Connexion pour réserver
                            </a>
                        <?php endif; ?>

                        <?php if ($utilisateur_connecte && $trip['driver_id'] != $utilisateur_id): ?>
                            <div class="mt-3 text-center">
                                <a href="messages.php?destinataire=<?php echo $trip['driver_id']; ?>" class="text-primary">
                                    <i class="fas fa-comment-dots"></i> Contacter <?php echo $trip['first_name']; ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                Trajet non trouvé ou invalide. <a href="index.php">Retour à l'accueil</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>