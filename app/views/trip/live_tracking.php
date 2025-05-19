<?php
require_once 'includes/db_connect.php';

// Vérifier si l'ID du trajet est fourni
if (!isset($_GET['ride_id'])) {
    header('Location: ride_sharing.php');
    exit;
}

$rideId = $_GET['ride_id'];

// Dans une application réelle, récupérer les détails du trajet depuis la base de données
// Pour la démo, utilisons des données statiques
$ride = [
    'id' => $rideId,
    'depart' => 'Paris',
    'destination' => 'Lyon',
    'date_heure_depart' => date('Y-m-d H:i:s', strtotime('+2 days')),
    'prix' => '25.00',
    'driver_name' => 'Thomas Dubois',
    'status' => 'active', // active, completed, cancelled
    'tracking_id' => 'TRK' . strtoupper(substr(md5($rideId), 0, 8))
];

// Vérifier si le trajet est en cours de suivi
$isTracking = isset($_GET['tracking']) && $_GET['tracking'] === 'start';

// Si la requête concerne l'arrêt du suivi
if (isset($_GET['tracking']) && $_GET['tracking'] === 'stop') {
    // Dans une application réelle, mettez à jour la base de données
    header('Location: ride_sharing.php');
    exit;
}

// Page title
$pageTitle = "Suivi en temps réel - {$ride['depart']} à {$ride['destination']}";

// Extra CSS
$extraCss = '
<link rel="stylesheet" href="assets/css/ride-sharing.css">
<link rel="stylesheet" href="assets/css/ride-details.css">
<link rel="stylesheet" href="assets/css/google-maps.css">
';

include 'includes/header.php';
?>

<div class="content-container share-container">
    <div class="share-content">
        <div class="share-header">
            <h1><i class="fas fa-broadcast-tower"></i> Suivi en temps réel</h1>
            <p>Trajet de <?php echo $ride['depart']; ?> à <?php echo $ride['destination']; ?></p>
        </div>
        
        <div class="share-main">
            <?php if ($isTracking): ?>
            <!-- Suivi en cours -->
            <div class="live-tracking-container">
                <div class="live-tracking-status">
                    <i class="fas fa-broadcast-tower"></i> Suivi en temps réel actif
                </div>
                
                <p>Votre position est partagée avec vos contacts de confiance.</p>
                <p>ID de suivi: <span class="tracking-id"><?php echo $ride['tracking_id']; ?></span></p>
                
                <div class="map-container" style="margin: 2rem 0;">
                    <div id="tracking-map" style="height: 400px;"></div>
                </div>
                
                <p>Le suivi sera automatiquement désactivé à votre arrivée à destination.</p>
                
                <a href="live_tracking.php?ride_id=<?php echo $rideId; ?>&tracking=stop" class="stop-tracking-btn">
                    <i class="fas fa-stop-circle"></i> Arrêter le suivi
                </a>
            </div>
            <?php else: ?>
            <!-- Démarrage du suivi -->
            <div class="share-section">
                <h2><i class="fas fa-info-circle"></i> Informations sur le suivi</h2>
                <p class="share-description">
                    Le suivi en temps réel permet à vos contacts de confiance de suivre votre progression pendant votre trajet.
                    Votre position sera mise à jour toutes les 30 secondes et partagée uniquement avec les personnes que vous avez sélectionnées.
                </p>
                
                <div style="margin: 2rem 0;">
                    <div class="ride-card" style="margin-bottom: 1.5rem;">
                        <div class="ride-header">
                            <div class="ride-title">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?php echo $ride['depart']; ?> → <?php echo $ride['destination']; ?>
                            </div>
                            <div class="ride-date">
                                <?php echo (new DateTime($ride['date_heure_depart']))->format('d/m/Y à H:i'); ?>
                            </div>
                        </div>
                        
                        <div class="ride-details">
                            <div class="ride-detail-item">
                                <div class="ride-detail-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>Conducteur: <?php echo $ride['driver_name']; ?></div>
                            </div>
                            
                            <div class="ride-detail-item">
                                <div class="ride-detail-icon">
                                    <i class="fas fa-broadcast-tower"></i>
                                </div>
                                <div>ID de suivi: <span class="tracking-id"><?php echo $ride['tracking_id']; ?></span></div>
                            </div>
                        </div>
                        
                        <div id="preview-map" style="height: 200px; margin: 1rem 0; border-radius: var(--radius); overflow: hidden;"></div>
                    </div>
                    
                    <div class="share-description" style="background-color: rgba(255, 193, 7, 0.1); padding: 1rem; border-radius: var(--radius); border-left: 4px solid #FFC107;">
                        <div style="font-weight: 600; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fas fa-exclamation-triangle" style="color: #FFC107;"></i> Important
                        </div>
                        <p>En activant le suivi en temps réel :</p>
                        <ul style="margin-top: 0.5rem; padding-left: 1.5rem;">
                            <li>Votre position GPS sera partagée avec vos contacts sélectionnés</li>
                            <li>La consommation de batterie peut augmenter pendant le suivi</li>
                            <li>Le suivi sera automatiquement désactivé à votre arrivée à destination</li>
                        </ul>
                    </div>
                    
                    <div style="margin-top: 2rem; display: flex; justify-content: center;">
                        <a href="live_tracking.php?ride_id=<?php echo $rideId; ?>&tracking=start" class="submit-button" style="padding: 1rem 2rem;">
                            <i class="fas fa-play-circle"></i> Démarrer le suivi en temps réel
                        </a>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Google Maps JavaScript -->
<script>
let map;
let directionsService;
let directionsRenderer;
let userMarker;
let departureMarker;
let destinationMarker;
let routeLine;
let watchId;

// Initialiser la carte
function initMap() {
    <?php if ($isTracking): ?>
    // Carte pour le suivi actif
    map = new google.maps.Map(document.getElementById('tracking-map'), {
        center: { lat: 46.603354, lng: 1.888334 }, // Centre de la France
        zoom: 6,
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
        
        // Commencer à suivre la position de l'utilisateur
        startTracking();
    }).catch(error => {
        console.error('Erreur lors de l\'initialisation de la carte:', error);
    });
    <?php else: ?>
    // Carte de prévisualisation
    map = new google.maps.Map(document.getElementById('preview-map'), {
        center: { lat: 46.603354, lng: 1.888334 }, // Centre de la France
        zoom: 5,
        mapTypeControl: false,
        streetViewControl: false,
        zoomControl: false,
        fullscreenControl: false
    });
    
    // Obtenir les coordonnées des villes
    Promise.all([
        geocodeCity('<?php echo addslashes($ride['depart']); ?>'),
        geocodeCity('<?php echo addslashes($ride['destination']); ?>')
    ]).then(([departureCoords, destinationCoords]) => {
        // Marqueur de départ
        new google.maps.Marker({
            position: departureCoords,
            map: map,
            icon: {
                url: "https://maps.google.com/mapfiles/ms/icons/green-dot.png",
                scaledSize: new google.maps.Size(30, 30),
            },
            title: '<?php echo addslashes($ride['depart']); ?>'
        });
        
        // Marqueur de destination
        new google.maps.Marker({
            position: destinationCoords,
            map: map,
            icon: {
                url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
                scaledSize: new google.maps.Size(30, 30),
            },
            title: '<?php echo addslashes($ride['destination']); ?>'
        });
        
        // Tracer une ligne directe entre départ et destination
        const routeLine = new google.maps.Polyline({
            path: [departureCoords, destinationCoords],
            geodesic: true,
            strokeColor: '#3b5998',
            strokeOpacity: 0.7,
            strokeWeight: 3
        });
        
        routeLine.setMap(map);
        
        // Ajuster la vue pour montrer la ligne
        const bounds = new google.maps.LatLngBounds();
        bounds.extend(departureCoords);
        bounds.extend(destinationCoords);
        map.fitBounds(bounds);
    }).catch(error => {
        console.error('Erreur lors de l\'initialisation de la carte de prévisualisation:', error);
    });
    <?php endif; ?>
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
                routeLine = new google.maps.Polyline({
                    path: [origin, destination],
                    geodesic: true,
                    strokeColor: '#3b5998',
                    strokeOpacity: 0.7,
                    strokeWeight: 3
                });
                
                routeLine.setMap(map);
                
                // Ajuster la vue pour montrer la ligne
                const bounds = new google.maps.LatLngBounds();
                bounds.extend(origin);
                bounds.extend(destination);
                map.fitBounds(bounds);
            }
        }
    );
}

// Commencer à suivre la position de l'utilisateur
function startTracking() {
    // Créer un marqueur pour la position de l'utilisateur
    userMarker = new google.maps.Marker({
        map: map,
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            scale: 8,
            fillColor: "#4285F4",
            fillOpacity: 1,
            strokeColor: "#FFFFFF",
            strokeWeight: 2
        },
        title: 'Votre position'
    });
    
    // Ajouter un cercle autour du marqueur pour indiquer la précision
    const accuracyCircle = new google.maps.Circle({
        map: map,
        fillColor: '#4285F4',
        fillOpacity: 0.2,
        strokeColor: '#4285F4',
        strokeOpacity: 0.5,
        strokeWeight: 1
    });
    
    // Dans un environnement réel, utiliser la géolocalisation du navigateur
    if (navigator.geolocation) {
        watchId = navigator.geolocation.watchPosition(
            (position) => {
                const pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                
                // Mettre à jour la position du marqueur
                userMarker.setPosition(pos);
                
                // Mettre à jour le cercle de précision
                accuracyCircle.setCenter(pos);
                accuracyCircle.setRadius(position.coords.accuracy);
                
                // Pour la démo, nous simulons l'envoi de la position au serveur
                // Dans une application réelle, il faudrait envoyer les coordonnées au serveur
                console.log('Position mise à jour:', pos);
                
                // Centrer la carte sur la position actuelle
                map.setCenter(pos);
            },
            (error) => {
                console.error('Erreur de géolocalisation:', error);
                
                // Pour la démo, en cas d'erreur, nous simulons une position le long de l'itinéraire
                simulatePosition();
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        console.error('La géolocalisation n\'est pas prise en charge par votre navigateur');
        
        // Pour la démo, nous simulons une position le long de l'itinéraire
        simulatePosition();
    }
}

// Simuler une position le long de l'itinéraire (pour la démo)
function simulatePosition() {
    // Obtenir les coordonnées des villes
    Promise.all([
        geocodeCity('<?php echo addslashes($ride['depart']); ?>'),
        geocodeCity('<?php echo addslashes($ride['destination']); ?>')
    ]).then(([departureCoords, destinationCoords]) => {
        // Pour la démo, nous positionnons l'utilisateur au 1/3 de l'itinéraire
        const pos = {
            lat: departureCoords.lat() + (destinationCoords.lat() - departureCoords.lat()) / 3,
            lng: departureCoords.lng() + (destinationCoords.lng() - departureCoords.lng()) / 3
        };
        
        // Mettre à jour la position du marqueur
        userMarker.setPosition(pos);
        
        // Centrer la carte sur la position simulée
        map.setCenter(pos);
        map.setZoom(8);
    }).catch(error => {
        console.error('Erreur lors de la simulation de position:', error);
    });
}

// Arrêter le suivi
function stopTracking() {
    if (watchId) {
        navigator.geolocation.clearWatch(watchId);
    }
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
    
    // Arrêter le suivi si l'utilisateur quitte la page
    window.addEventListener('beforeunload', stopTracking);
});
</script>

<?php include 'includes/footer.php'; ?>