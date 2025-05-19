<?php
header('Content-Type: application/json');
require_once 'ride_functions.php';

// Get parameters from request
$departure_city = isset($_GET['departure_city']) ? $_GET['departure_city'] : '';
$arrival_city = isset($_GET['arrival_city']) ? $_GET['arrival_city'] : '';
$departure_date = isset($_GET['departure_date']) ? $_GET['departure_date'] : '';
$departure_time = isset($_GET['departure_time']) ? $_GET['departure_time'] : '';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : null;
$min_rating = isset($_GET['rating']) ? (float)$_GET['rating'] : null;
$passengers = isset($_GET['passengers']) ? (int)$_GET['passengers'] : null;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Build filters array
$filters = [];

if ($min_price !== null) {
    $filters['min_price'] = $min_price;
}

if ($max_price !== null) {
    $filters['max_price'] = $max_price;
}

if ($min_rating !== null) {
    $filters['min_rating'] = $min_rating;
}

if ($passengers !== null) {
    $filters['passengers'] = $passengers;
}

// Get rides with applied filters
$rides = getRides($filters, $sort, $departure_city, $arrival_city, $departure_date, $departure_time);

if ($rides === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur s\'est produite lors de la récupération des trajets.'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'rides' => $rides
]);
?>
