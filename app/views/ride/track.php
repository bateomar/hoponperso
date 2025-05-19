<?php
// Page de suivi public accessible par les contacts de confiance

// Vérifier si un code de suivi est fourni
if (!isset($_GET['code']) || empty($_GET['code'])) {
    // Rediriger vers la page d'accueil si aucun code n'est fourni
    header('Location: index.php');
    exit;
}

$trackingCode = $_GET['code'];

// Dans une application réelle, vérifier si le code de suivi est valide et récupérer les informations du trajet
// Pour la démo, utilisons des données statiques
$isValidCode = true; // Simuler une validation réussie
$ride = [
    'id' => 123,
    'tracking_id' => $trackingCode,
    'depart' => 'Paris',
    'destination' => 'Lyon',
    'date_heure_depart' => date('Y-m-d H:i:s', strtotime('-30 minutes')), // Simuler un trajet en cours
    'prix' => '25.00',
    'driver_name' => 'Thomas Dubois',
    'passenger_name' => 'Jean Martin',
    'status' => 'active', // active, completed, cancelled
    'progress' => 35, // Pourcentage d'avancement (0-100)
    'estimated_arrival' => date('Y-m-d H:i:s', strtotime('+1 hour 30 minutes')),
    'current_location' => [
        'lat' => 46.3,
        'lng' => 4.2
    ]
];

// Page title
$pageTitle = "Suivi de trajet";

// Extra CSS
$extraCss = '
<link rel="stylesheet" href="assets/css/ride-sharing.css">
<link rel="stylesheet" href="assets/css/ride-details.css">
<link rel="stylesheet" href="assets/css/google-maps.css">
';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - HopOn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <?php echo $extraCss; ?>
    
    <!-- Meta refresh pour mettre à jour la page toutes les 60 secondes -->
    <meta http-equiv="refresh" content="60">
</head>
<body>
    <header style="background-color: var(--secondary-color); padding: 1rem 0;">
        <div class="header-container" style="max-width: 1200px; margin: 0 auto; padding: 0 1rem; display: flex; justify-content: space-between; align-items: center;">
            <div class="logo">
                <a href="index.php">
                    <img src="assets/images/hopon_logo.jpg" alt="HopOn Logo" style="height: 40px; width: auto;">
                </a>
            </div>
            <h1 style="color: white; font-size: 1.2rem; margin: 0;">Suivi de trajet en temps réel</h1>
        </div>
    </header>
    
    <main>
        <div class="content-container share-container">
            <div class="share-content">
                <?php if (!$isValidCode): ?>
                <!-- Code invalide -->
                <div style="padding: 2rem; text-align: center;">
                    <div style="font-size: 4rem; color: #f44336; margin-bottom: 1rem;">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <h2>Code de suivi invalide</h2>
                    <p style="margin: 1rem 0; color: var(--light-text);">
                        Le code de suivi que vous avez fourni n'est pas valide ou a expiré.
                    </p>
                    <a href="index.php" class="submit-button" style="display: inline-block; margin-top: 1rem;">
                        <i class="fas fa-home"></i> Retour à l'accueil
                    </a>
                </div>
                <?php else: ?>
                <!-- Informations de suivi -->
                <div class="share-header">
                    <h1><i class="fas fa-map-marked-alt"></i> Suivi de trajet</h1>
                    <p>Vous suivez le trajet de <?php echo $ride['passenger_name']; ?></p>
                </div>
                
                <div class="share-main">
                    <div style="background-color: var(--light-bg); padding: 1rem; border-radius: var(--radius); margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <div><strong>Trajet:</strong> <?php echo $ride['depart']; ?> → <?php echo $ride['destination']; ?></div>
                            <div>
                                <span class="ride-share-status active">
                                    <i class="fas fa-circle"></i> En cours
                                </span>
                            </div>
                        </div>
                        
                        <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem;">
                            <div style="flex: 1; min-width: 200px;">
                                <div style="margin-bottom: 0.5rem; color: var(--light-text);">Départ</div>
                                <div style="font-weight: 500;"><?php echo (new DateTime($ride['date_heure_depart']))->format('d/m/Y à H:i'); ?></div>
                            </div>
                            <div style="flex: 1; min-width: 200px;">
                                <div style="margin-bottom: 0.5rem; color: var(--light-text);">Arrivée estimée</div>
                                <div style="font-weight: 500;"><?php echo (new DateTime($ride['estimated_arrival']))->format('d/m/Y à H:i'); ?></div>
                            </div>
                            <div style="flex: 1; min-width: 200px;">
                                <div style="margin-bottom: 0.5rem; color: var(--light-text);">Conducteur</div>
                                <div style="font-weight: 500;"><?php echo $ride['driver_name']; ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Indicateur de progression -->
                    <div style="margin-bottom: 1.5rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <div><?php echo $ride['depart']; ?></div>
                            <div><?php echo $ride['progress']; ?>% du trajet</div>
                            <div><?php echo $ride['destination']; ?></div>
                        </div>
                        <div style="height: 8px; background-color: #e0e0e0; border-radius: 4px; overflow: hidden;">
                            <div style="height: 100%; width: <?php echo $ride['progress']; ?>%; background-color: var(--primary-color);"></div>
                        </div>
                    </div>
                    
                    <!-- Carte en temps réel -->
                    <div class="map-container" style="margin-bottom: 1.5rem;">
                        <div id="tracking-map" style="height: 400px;"></div>
                    </div>
                    
                    <!-- Détails supplémentaires -->
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1.5rem;">
                        <div style="flex: 1; min-width: 250px; background-color: var(--light-bg); padding: 1rem; border-radius: var(--radius);">
                            <h3 style="margin-top: 0; font-size: 1.1rem;">Dernière mise à jour</h3>
                            <p style="margin-bottom: 0;"><?php echo date('H:i:s'); ?></p>
                            <p style="font-size: 0.9rem; color: var(--light-text); margin-top: 0.5rem;">
                                Cette page se rafraîchit automatiquement toutes les 60 secondes.
                                <a href="track.php?code=<?php echo $trackingCode; ?>" style="color: var(--primary-color);">Rafraîchir maintenant</a>
                            </p>
                        </div>
                        
                        <div style="flex: 1; min-width: 250px; background-color: var(--light-bg); padding: 1rem; border-radius: var(--radius);">
                            <h3 style="margin-top: 0; font-size: 1.1rem;">Informations de partage</h3>
                            <p style="margin-bottom: 0.5rem;">Code de suivi: <span class="tracking-id"><?php echo $trackingCode; ?></span></p>
                            <p style="font-size: 0.9rem; color: var(--light-text); margin-top: 0.5rem; margin-bottom: 0;">
                                Ce lien restera actif jusqu'à 24 heures après l'arrivée.
                            </p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <footer style="background-color: var(--secondary-color); color: white; padding: 1rem 0; margin-top: 2rem; text-align: center;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 1rem;">
            <p>&copy; <?php echo date('Y'); ?> HopOn. Tous droits réservés.</p>
        </div>
    </footer>
    
    <!-- Google Maps JavaScript -->
    <script>
    let map;
    let directionsService;
    let directionsRenderer;
    let userMarker;
    let departureMarker;
    let destinationMarker;
    
    // Initialiser la carte
    function initMap() {
        // Carte pour le suivi en temps réel
        map = new google.maps.Map(document.getElementById('tracking-map'), {
            center: { 
                lat: <?php echo $ride['current_location']['lat']; ?>, 
                lng: <?php echo $ride['current_location']['lng']; ?> 
            },
            zoom: 8,
            mapTypeControl: false,
            streetViewControl: false,
            fullscreenControl: true
        });
        
        // Initialiser les services de direction
        directionsService = new google.maps.DirectionsService();
        directionsRenderer = new google.maps.DirectionsRenderer({
            suppressMarkers: true,
            polylineOptions: {
                strokeColor: '#3b5998',
                strokeWeight: 4,
                strokeOpacity: 0.7
            }
        });
        directionsRenderer.setMap(map);
        
        // Obtenir les coordonnées des villes
        Promise.all([
            geocodeCity('<?php echo addslashes($ride['depart']); ?>'),
            geocodeCity('<?php echo addslashes($ride['destination']); ?>')
        ]).then(([departureCoords, destinationCoords]) => {
            // Marqueur de départ
            departureMarker = new google.maps.Marker({
                position: departureCoords,
                map: map,
                icon: {
                    url: "https://maps.google.com/mapfiles/ms/icons/green-dot.png",
                    scaledSize: new google.maps.Size(40, 40),
                },
                title: '<?php echo addslashes($ride['depart']); ?>'
            });
            
            // Marqueur de destination
            destinationMarker = new google.maps.Marker({
                position: destinationCoords,
                map: map,
                icon: {
                    url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
                    scaledSize: new google.maps.Size(40, 40),
                },
                title: '<?php echo addslashes($ride['destination']); ?>'
            });
            
            // Calculer et afficher l'itinéraire
            calculateAndDisplayRoute(departureCoords, destinationCoords);
            
            // Ajouter le marqueur de position actuelle
            userMarker = new google.maps.Marker({
                position: { 
                    lat: <?php echo $ride['current_location']['lat']; ?>, 
                    lng: <?php echo $ride['current_location']['lng']; ?> 
                },
                map: map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 8,
                    fillColor: "#4285F4",
                    fillOpacity: 1,
                    strokeColor: "#FFFFFF",
                    strokeWeight: 2
                },
                title: 'Position actuelle'
            });
            
            // Ajouter une info-bulle sur le marqueur
            const infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="padding: 10px;">
                        <div style="font-weight: 600; margin-bottom: 5px;"><?php echo $ride['passenger_name']; ?></div>
                        <div>En route vers <?php echo $ride['destination']; ?></div>
                        <div style="font-size: 0.9rem; color: #666; margin-top: 5px;">
                            Arrivée estimée: <?php echo (new DateTime($ride['estimated_arrival']))->format('H:i'); ?>
                        </div>
                    </div>
                `
            });
            
            userMarker.addListener('click', () => {
                infoWindow.open(map, userMarker);
            });
            
            // Ouvrir l'info-bulle par défaut
            infoWindow.open(map, userMarker);
            
            // Ajouter un cercle autour du marqueur pour indiquer la précision
            new google.maps.Circle({
                map: map,
                center: { 
                    lat: <?php echo $ride['current_location']['lat']; ?>, 
                    lng: <?php echo $ride['current_location']['lng']; ?> 
                },
                radius: 1000, // 1km de précision simulée
                fillColor: '#4285F4',
                fillOpacity: 0.2,
                strokeColor: '#4285F4',
                strokeOpacity: 0.5,
                strokeWeight: 1
            });
        }).catch(error => {
            console.error('Erreur lors de l\'initialisation de la carte:', error);
        });
    }
    
    // Fonction pour géocoder une ville
    function geocodeCity(address) {
        return new Promise((resolve, reject) => {
            const geocoder = new google.maps.Geocoder();
            
            geocoder.geocode({ address: address }, (results, status) => {
                if (status === "OK" && results[0]) {
                    resolve(results[0].geometry.location);
                } else {
                    reject(`Geocode failed for ${address}: ${status}`);
                }
            });
        });
    }
    
    // Calculer et afficher l'itinéraire
    function calculateAndDisplayRoute(origin, destination) {
        directionsService.route(
            {
                origin: origin,
                destination: destination,
                travelMode: google.maps.TravelMode.DRIVING
            },
            (response, status) => {
                if (status === "OK") {
                    directionsRenderer.setDirections(response);
                } else {
                    console.error("Directions request failed:", status);
                    
                    // Si l'API de directions échoue, tracer une ligne directe
                    const routeLine = new google.maps.Polyline({
                        path: [origin, destination],
                        geodesic: true,
                        strokeColor: '#3b5998',
                        strokeOpacity: 0.7,
                        strokeWeight: 3
                    });
                    
                    routeLine.setMap(map);
                }
            }
        );
    }
    
    // Charger Google Maps API
    function loadGoogleMapsAPI() {
        const script = document.createElement('script');
        script.src = `https://maps.googleapis.com/maps/api/js?key=<?php echo getenv('GOOGLE_MAPS_API_KEY'); ?>&callback=initMap`;
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }
    
    // Charger Google Maps au chargement de la page
    document.addEventListener('DOMContentLoaded', function() {
        loadGoogleMapsAPI();
    });
    </script>
</body>
</html>