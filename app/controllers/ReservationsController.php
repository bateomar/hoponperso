<?php

namespace App\Controllers;

use PDO;

class ReservationsController extends \Controller
{
    private $pdo;

    public function __construct()
    {
        // Charger la connexion PDO centralisée
        $dbConnectionPath = __DIR__ . '/../../config/db_connection.php';
        if (file_exists($dbConnectionPath)) {
            $this->pdo = require $dbConnectionPath;
        } else {
            // Gestion d'erreur si le fichier de connexion est introuvable
            die("Erreur: Impossible de charger la connexion à la base de données.");
        }
    }

    public function index()
    {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['user_id'])) {
            // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
            header('Location: ' . BASE_PATH . '/login'); // Assurez-vous que BASE_PATH est défini correctement
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Récupérer les réservations à venir depuis la base de données
        $upcomingReservations = $this->getUpcomingReservationsFromDB($userId);
        // Récupérer les réservations passées depuis la base de données
        $pastReservations = $this->getPastReservationsFromDB($userId);

        // Définir le contrôleur global pour que la vue puisse accéder aux méthodes utilitaires
        $GLOBALS['controller'] = $this;

        // Rendre la vue
        $this->render('reservations', 'reservations', [
            'upcomingReservations' => $upcomingReservations,
            'pastReservations' => $pastReservations,
        ]);
    }

    // Fonction pour formater la date en français
    public function formatDateFr($date)
    {
        $timestamp = strtotime($date);
        $joursSemaine = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $mois = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

        $jourSemaine = $joursSemaine[date('w', $timestamp)];
        $jour = date('j', $timestamp);
        $moisIndex = date('n', $timestamp) - 1;
        $annee = date('Y', $timestamp);

        return "$jourSemaine $jour {$mois[$moisIndex]} $annee";
    }

    // Fonction pour générer les étoiles de notation
    public function generateStars($rating)
    {
        $rating = floatval($rating);
        $fullStars = floor($rating);
        $halfStar = ($rating - $fullStars) >= 0.5;
        $emptyStars = 5 - $fullStars - ($halfStar ? 1 : 0);

        $html = '';

        for ($i = 0; $i < $fullStars; $i++) {
            $html .= '★';
        }

        if ($halfStar) {
            $html .= '★';
        }

        for ($i = 0; $i < $emptyStars; $i++) {
            $html .= '☆';
        }

        return $html;
    }

    private function getUpcomingReservationsFromDB($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                b.id AS booking_id,
                b.status,
                t.departure_time AS date_heure_depart,
                t.departure_location AS depart,
                t.arrival_location AS destination,
                t.price,
                u.first_name AS conducteur_prenom,
                u.last_name AS conducteur_nom,
                u.id AS conducteur_id,
                AVG(r.rating) as driver_rating
            FROM hopon_bookings b
            JOIN hopon_trips t ON b.trip_id = t.id
            JOIN hopon_users u ON t.driver_id = u.id
            LEFT JOIN hopon_ratings r ON u.id = r.driver_id  -- Join pour récupérer la note du conducteur
            WHERE b.passenger_id = :user_id
            AND t.departure_time > NOW()
            GROUP BY b.id, b.status, t.departure_time, t.departure_location, t.arrival_location, t.price, u.first_name, u.last_name, u.id
            ORDER BY t.departure_time ASC
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getPastReservationsFromDB($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                b.id AS booking_id,
                b.status,
                t.departure_time AS date_heure_depart,
                t.departure_location AS depart,
                t.arrival_location AS destination,
                t.price,
                u.first_name AS conducteur_prenom,
                u.last_name AS conducteur_nom,
                u.id AS conducteur_id,
                AVG(r.rating) as driver_rating,
                (SELECT COUNT(*) FROM hopon_ratings WHERE booking_id = b.id AND user_id = :user_id) as has_rated
            FROM hopon_bookings b
            JOIN hopon_trips t ON b.trip_id = t.id
            JOIN hopon_users u ON t.driver_id = u.id
            LEFT JOIN hopon_ratings r ON u.id = r.driver_id  -- Join pour récupérer la note du conducteur
            WHERE b.passenger_id = :user_id
            AND t.departure_time <= NOW()
            GROUP BY b.id, b.status, t.departure_time, t.departure_location, t.arrival_location, t.price, u.first_name, u.last_name, u.id
            ORDER BY t.departure_time DESC
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function hasUserRated($bookingId, $userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM hopon_ratings
            WHERE booking_id = :booking_id AND user_id = :user_id
        ");
        $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }
}
