<?php
require_once 'db_connect.php';

// Récupérer le terme de recherche
$search = isset($_GET['term']) ? $_GET['term'] : '';

// Vérifier que le terme de recherche a au moins 2 caractères
if (strlen($search) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // Connexion à la base de données
    $pdo = connectDB();
    
    // Debug: Enregistrer la recherche
    error_log("Recherche de villes avec le terme: " . $search);
    
    // Préparer la requête pour chercher les villes qui commencent par le terme de recherche
    $stmt = $pdo->prepare("
        SELECT DISTINCT departure_location as city FROM trips WHERE departure_location LIKE :search
        UNION
        SELECT DISTINCT arrival_location as city FROM trips WHERE arrival_location LIKE :search
        ORDER BY city
        LIMIT 10
    ");
    
    // Exécuter la requête
    $stmt->bindValue(':search', $search . '%', PDO::PARAM_STR);
    $stmt->execute();
    
    // Récupérer les résultats
    $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Debug: Enregistrer les résultats
    error_log("Résultats trouvés: " . count($results) . " - " . json_encode($results));
    
    // Définir les en-têtes pour éviter les problèmes CORS
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // Renvoyer les résultats au format JSON
    echo json_encode($results);
} catch (PDOException $e) {
    // Enregistrer l'erreur
    error_log("Erreur dans cities.php: " . $e->getMessage());
    
    // Définir les en-têtes pour éviter les problèmes CORS
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    // En cas d'erreur, renvoyer un tableau vide
    echo json_encode([]);
}
?>