<?php
/**
 * Fonctions pour le calcul de l'empreinte carbone
 */

/**
 * Calcule l'empreinte carbone d'un trajet en fonction de la distance et du type de véhicule
 * 
 * @param float $distance Distance en kilomètres
 * @param string $vehicleType Type de véhicule (small, medium, large, suv, electric)
 * @param int $passengers Nombre de passagers (pour le calcul par personne)
 * @return array Empreinte carbone [total, per_person, saved]
 */
function calculateCarbonFootprint($distance, $vehicleType = 'medium', $passengers = 1) {
    // Facteurs d'émission de CO2 en kg par km selon le type de véhicule
    $emissionFactors = [
        'small' => 0.12,     // Petite voiture (ex: citadine)
        'medium' => 0.18,    // Voiture moyenne
        'large' => 0.25,     // Grande voiture
        'suv' => 0.30,       // SUV
        'electric' => 0.05,  // Voiture électrique
        'hybrid' => 0.10,    // Voiture hybride
        'bus' => 0.08,       // Bus (par passager)
        'train' => 0.04,     // Train (par passager)
    ];
    
    // Vérifier si le type de véhicule est valide
    if (!isset($emissionFactors[$vehicleType])) {
        $vehicleType = 'medium'; // Type par défaut
    }
    
    // Émission totale pour le trajet
    $totalEmission = $distance * $emissionFactors[$vehicleType];
    
    // Émission par personne (partage de la voiture)
    $perPersonEmission = $totalEmission / max(1, $passengers);
    
    // Émission économisée par le covoiturage par rapport à des trajets individuels
    // Calcul : (nombre de passagers - 1) * émission d'une voiture moyenne
    $savedEmission = ($passengers > 1) ? ($passengers - 1) * $distance * $emissionFactors['medium'] : 0;
    
    return [
        'total' => round($totalEmission, 2),         // kg CO2 total
        'per_person' => round($perPersonEmission, 2), // kg CO2 par personne
        'saved' => round($savedEmission, 2),          // kg CO2 économisé
    ];
}

/**
 * Calcule la distance approximative entre deux coordonnées géographiques
 * en utilisant la formule de Haversine
 * 
 * @param float $lat1 Latitude du point de départ
 * @param float $lon1 Longitude du point de départ
 * @param float $lat2 Latitude du point d'arrivée
 * @param float $lon2 Longitude du point d'arrivée
 * @return float Distance en kilomètres
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    // Rayon de la Terre en km
    $earthRadius = 6371;
    
    // Conversion des degrés en radians
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);
    
    // Calcul de la différence
    $latDiff = $lat2 - $lat1;
    $lonDiff = $lon2 - $lon1;
    
    // Formule de Haversine
    $a = sin($latDiff / 2) * sin($latDiff / 2) + cos($lat1) * cos($lat2) * sin($lonDiff / 2) * sin($lonDiff / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;
    
    // Arrondir à 2 décimales
    return round($distance, 2);
}

/**
 * Formate une valeur de CO2 avec l'unité appropriée
 * 
 * @param float $value Valeur en kg de CO2
 * @return string Valeur formatée avec unité
 */
function formatCO2($value) {
    if ($value < 1) {
        return round($value * 1000) . ' g CO₂';
    } else {
        return round($value, 2) . ' kg CO₂';
    }
}

/**
 * Obtient une suggestion d'équivalent écologique pour la quantité de CO2
 * 
 * @param float $kgCO2 Quantité de CO2 en kg
 * @return string Message d'équivalent écologique
 */
function getCO2Equivalent($kgCO2) {
    if ($kgCO2 < 1) {
        return 'Équivalent à moins d\'1 km en voiture classique';
    } elseif ($kgCO2 < 5) {
        return 'Équivalent à la charge d\'un smartphone pendant ' . round($kgCO2 * 300) . ' jours';
    } elseif ($kgCO2 < 10) {
        return 'Équivalent à la production de ' . round($kgCO2 / 0.5) . ' repas';
    } elseif ($kgCO2 < 50) {
        return 'Équivalent à ' . round($kgCO2 / 6) . ' kg de viande de bœuf';
    } else {
        return 'Équivalent à ' . round($kgCO2 / 120) . ' arbres à planter pour compenser';
    }
}

/**
 * Obtient une suggestion d'équivalent écologique pour la quantité de CO2 économisée
 * 
 * @param float $kgCO2 Quantité de CO2 économisée en kg
 * @return string Message d'économie écologique
 */
function getCO2SavedEquivalent($kgCO2) {
    if ($kgCO2 < 1) {
        return 'Économie minime, mais chaque geste compte !';
    } elseif ($kgCO2 < 5) {
        return 'Équivalent à ' . round($kgCO2 * 10) . ' km non parcourus en voiture';
    } elseif ($kgCO2 < 20) {
        return 'Équivalent à ' . round($kgCO2 / 12) . ' jours sans utiliser de voiture';
    } elseif ($kgCO2 < 50) {
        return 'Équivalent à ' . round($kgCO2 / 40) . ' arbres absorbant du CO₂ pendant un mois';
    } else {
        return 'Équivalent à ' . round($kgCO2 / 175) . ' vols Paris-Londres évités';
    }
}
?>