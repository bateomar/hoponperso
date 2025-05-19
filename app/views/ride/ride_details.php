<?php
require_once 'includes/db_connect.php';
require_once 'includes/carbon_functions.php';

// Get ride ID from URL
$ride_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Function to get ride details by ID
function getRideById($id) {
    try {
        $db = connectDB();
        
        if (!$db) {
            return false;
        }
        
        $query = "SELECT t.*,
                 u.first_name as driver_firstname, 
                 u.last_name as driver_lastname,
                 u.id as driver_id,
                 u.profile_picture_url as driver_profile_pic,
                 u.phone_number as driver_phone,
                 COALESCE((SELECT AVG(r.score) FROM ratings r WHERE r.target_id = t.driver_id), 0) as avg_rating,
                 v.type as vehicle_type,
                 COALESCE(v.model, 'Voiture standard') as vehicle_model,
                 COALESCE(v.color, '') as vehicle_color
                 FROM trips t
                 JOIN users u ON t.driver_id = u.id
                 LEFT JOIN vehicles v ON t.vehicle_id = v.id
                 WHERE t.id = :id";
        
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Database Query Error: ' . $e->getMessage());
        return false;
    }
}

// Function to get geocoding information for a city
function getGeocodingInfo($city) {
    try {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . urlencode($city) . "&key=" . getenv('GOOGLE_MAPS_API_KEY');
        $response = file_get_contents($url);
        
        if ($response === false) {
            return null;
        }
        
        $data = json_decode($response, true);
        
        if ($data['status'] !== 'OK' || empty($data['results'])) {
            return null;
        }
        
        return $data['results'][0]['geometry']['location'];
    } catch (Exception $e) {
        error_log('Geocoding Error: ' . $e->getMessage());
        return null;
    }
}

// Get ride details
$ride = getRideById($ride_id);

// If ride not found, redirect to home page
if (!$ride) {
    header('Location: index.php');
    exit;
}

// Get geocoding information for departure and arrival cities
$departureGeo = getGeocodingInfo($ride['departure_location']);
$arrivalGeo = getGeocodingInfo($ride['arrival_location']);

// Calculate distance
$distance = 0;
if ($departureGeo && $arrivalGeo) {
    $distance = calculateDistance(
        $departureGeo['lat'], 
        $departureGeo['lng'], 
        $arrivalGeo['lat'], 
        $arrivalGeo['lng']
    );
}

// Calculate carbon footprint
$vehicleType = isset($ride['vehicle_type']) ? strtolower($ride['vehicle_type']) : 'medium';
$seats = ($ride['seats_offered'] > 0) ? $ride['seats_offered'] : 4;
$carbonData = calculateCarbonFootprint($distance, $vehicleType, $seats);

// Page title
$pageTitle = "Trajet de {$ride['departure_location']} à {$ride['arrival_location']}";

// Add ride details and Google Maps CSS
$extraCss = '
<link rel="stylesheet" href="assets/css/ride-details.css">
<link rel="stylesheet" href="assets/css/google-maps.css">
';

include 'includes/header.php';
?>

<div class="content-container ride-details-container">
    <div class="ride-details-content">
        <div class="ride-details-header">
            <div class="back-link">
                <a href="index.php"><i class="fas fa-arrow-left"></i> Retour aux trajets</a>
            </div>
            <h1>Trajet de <?php echo htmlspecialchars($ride['departure_location']); ?> à <?php echo htmlspecialchars($ride['arrival_location']); ?></h1>
        </div>
        
        <div class="ride-details-main">
            <div class="ride-info-card">
                <div class="ride-route-header">
                    <div class="ride-cities">
                        <h2>
                            <span class="departure-city"><?php echo htmlspecialchars($ride['departure_location']); ?></span>
                            <i class="fas fa-long-arrow-alt-right"></i>
                            <span class="arrival-city"><?php echo htmlspecialchars($ride['arrival_location']); ?></span>
                        </h2>
                    </div>
                    <div class="ride-price">
                        <?php echo htmlspecialchars($ride['price']); ?>€
                    </div>
                </div>
                
                <div class="ride-timeline">
                    <div class="timeline-start">
                        <?php 
                        $departureDateTime = new DateTime($ride['departure_time']);
                        echo $departureDateTime->format('H:i'); 
                        ?>
                    </div>
                    <div class="timeline-line">
                        <div class="timeline-duration">
                            <?php 
                            // Calculating an estimated duration (just for display purposes)
                            $durationHours = mt_rand(1, 5);
                            $durationMinutes = mt_rand(0, 59);
                            echo "{$durationHours}h{$durationMinutes}min"; 
                            ?>
                        </div>
                    </div>
                    <div class="timeline-end">
                        <?php 
                        // Calculate estimated arrival time
                        $departureDateTime->add(new DateInterval("PT{$durationHours}H{$durationMinutes}M"));
                        echo $departureDateTime->format('H:i'); 
                        ?>
                    </div>
                </div>
                
                <div class="ride-map-container">
                    <h3>Itinéraire</h3>
                    <div id="ride-map"></div>
                </div>
                
                <div class="ride-driver-section">
                    <h3>Votre conducteur</h3>
                    <div class="driver-profile">
                        <div class="driver-avatar">
                            <?php if (!empty($ride['driver_profile_pic'])): ?>
                                <img src="<?php echo htmlspecialchars($ride['driver_profile_pic']); ?>" alt="Photo de profil">
                            <?php else: ?>
                                <div class="default-avatar"><i class="fas fa-user"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="driver-info">
                            <h4><?php echo htmlspecialchars($ride['driver_firstname'] . ' ' . $ride['driver_lastname']); ?></h4>
                            <div class="driver-rating">
                                <span class="rating-stars">
                                    <?php 
                                    $rating = round($ride['avg_rating']);
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($i <= $rating) {
                                            echo '<i class="fas fa-star"></i>';
                                        } else {
                                            echo '<i class="far fa-star"></i>';
                                        }
                                    }
                                    ?>
                                </span>
                                <span class="rating-value"><?php echo number_format($ride['avg_rating'], 1); ?></span>
                            </div>
                            <a href="#" class="driver-profile-link">Voir le profil</a>
                        </div>
                    </div>
                    
                    <?php if (!empty($ride['vehicle_model'])): ?>
                    <div class="driver-vehicle">
                        <div class="vehicle-icon"><i class="fas fa-car"></i></div>
                        <div class="vehicle-info">
                            <div class="vehicle-model"><?php echo htmlspecialchars($ride['vehicle_model']); ?></div>
                            <?php if (!empty($ride['vehicle_color'])): ?>
                            <div class="vehicle-color"><?php echo htmlspecialchars($ride['vehicle_color']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="ride-details-section">
                    <h3>Détails du trajet</h3>
                    <div class="ride-detail-item">
                        <div class="detail-label"><i class="fas fa-calendar"></i> Date</div>
                        <div class="detail-value"><?php echo (new DateTime($ride['departure_time']))->format('d/m/Y'); ?></div>
                    </div>
                    <div class="ride-detail-item">
                        <div class="detail-label"><i class="fas fa-clock"></i> Heure de départ</div>
                        <div class="detail-value"><?php echo (new DateTime($ride['departure_time']))->format('H:i'); ?></div>
                    </div>
                    <div class="ride-detail-item">
                        <div class="detail-label"><i class="fas fa-users"></i> Places disponibles</div>
                        <div class="detail-value"><?php echo htmlspecialchars($ride['seats_offered'] - $ride['seats_booked']); ?></div>
                    </div>
                    <div class="ride-detail-item">
                        <div class="detail-label"><i class="fas fa-route"></i> Distance</div>
                        <div class="detail-value distance-value">
                            <?php if ($distance > 0): ?>
                                <?php echo $distance; ?> km
                            <?php else: ?>
                                Distance non disponible
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="carbon-footprint-section">
                    <h3>Empreinte carbone</h3>
                    <div class="carbon-overview">
                        <div class="carbon-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="carbon-details">
                            <div class="carbon-data">
                                <div class="carbon-item">
                                    <div class="carbon-label">Par personne</div>
                                    <div class="carbon-value"><?php echo formatCO2($carbonData['per_person']); ?></div>
                                </div>
                                <div class="carbon-item">
                                    <div class="carbon-label">Total trajet</div>
                                    <div class="carbon-value"><?php echo formatCO2($carbonData['total']); ?></div>
                                </div>
                                <div class="carbon-item">
                                    <div class="carbon-label">CO₂ économisé</div>
                                    <div class="carbon-value eco-positive"><?php echo formatCO2($carbonData['saved']); ?></div>
                                </div>
                            </div>
                            <div class="carbon-info-box">
                                <div class="info-title">Impact environnemental</div>
                                <div class="info-content">
                                    <p><?php echo getCO2Equivalent($carbonData['per_person']); ?></p>
                                    <?php if ($carbonData['saved'] > 0): ?>
                                    <p class="eco-impact"><?php echo getCO2SavedEquivalent($carbonData['saved']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="ride-booking-section">
                    <a href="http://localhost/HopOn/app/views/trip_reservation/trip_reservation.php?id=<?php echo $ride['id']; ?>" class="booking-btn">Réserver ce trajet</a>
                    <a href="meetup_points.php?ride_id=<?php echo $ride['id']; ?>" class="meetup-btn">Points de rendez-vous</a>
                    <a href="#" class="contact-driver-btn">Contacter le conducteur</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Styles spécifiques pour l'empreinte carbone et les améliorations -->
<style>
/* Driver Profile Styles */
.ride-driver-section {
    background-color: var(--bg-color);
    border-radius: var(--radius);
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: var(--shadow);
}

.driver-profile {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.driver-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 20px;
    background-color: var(--light-bg);
    display: flex;
    align-items: center;
    justify-content: center;
}

.driver-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.default-avatar {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: var(--lighter-text);
}

.driver-info h4 {
    margin: 0 0 5px;
    font-size: 18px;
    color: var(--text-color);
}

.driver-rating {
    display: flex;
    align-items: center;
    margin-bottom: 8px;
}

.rating-stars {
    color: #FFD700;
    margin-right: 5px;
}

.rating-value {
    font-weight: 500;
}

.driver-profile-link {
    color: var(--primary-color);
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.driver-profile-link:hover {
    text-decoration: underline;
}

.driver-vehicle {
    display: flex;
    align-items: center;
    padding: 10px 15px;
    background-color: var(--light-bg);
    border-radius: var(--radius);
}

.vehicle-icon {
    margin-right: 15px;
    font-size: 20px;
    color: var(--primary-color);
}

.vehicle-model {
    font-weight: 500;
    color: var(--text-color);
}

.vehicle-color {
    font-size: 14px;
    color: var(--light-text);
}

/* Carbon Footprint Styles */
.carbon-footprint-section {
    background-color: var(--bg-color);
    border-radius: var(--radius);
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: var(--shadow);
}

.carbon-overview {
    display: flex;
    align-items: flex-start;
}

.carbon-icon {
    font-size: 36px;
    color: #4CAF50;
    margin-right: 20px;
    padding-top: 10px;
}

.carbon-details {
    flex: 1;
}

.carbon-data {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 20px;
}

.carbon-item {
    flex: 1;
    min-width: 120px;
    background-color: var(--light-bg);
    border-radius: var(--radius);
    padding: 15px;
    text-align: center;
}

.carbon-label {
    font-size: 14px;
    margin-bottom: 5px;
    color: var(--light-text);
}

.carbon-value {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-color);
}

.eco-positive {
    color: #4CAF50;
}

.carbon-info-box {
    background-color: rgba(76, 175, 80, 0.1);
    border-left: 4px solid #4CAF50;
    padding: 15px;
    border-radius: 0 var(--radius) var(--radius) 0;
}

.info-title {
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 10px;
}

.info-content p {
    margin: 0 0 10px;
    color: var(--light-text);
    font-size: 14px;
    line-height: 1.5;
}

.eco-impact {
    color: #4CAF50;
    font-weight: 500;
}

/* Map Styles */
.ride-map-container {
    margin-bottom: 25px;
}

#ride-map {
    height: 300px;
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
}

/* Responsive Styles */
@media (max-width: 768px) {
    .carbon-overview {
        flex-direction: column;
    }
    
    .carbon-icon {
        margin-right: 0;
        margin-bottom: 15px;
        text-align: center;
    }
    
    .carbon-data {
        flex-direction: column;
        gap: 10px;
    }
    
    .driver-profile {
        flex-direction: column;
        text-align: center;
    }
    
    .driver-avatar {
        margin-right: 0;
        margin-bottom: 15px;
    }
}
</style>

<!-- Google Maps JavaScript -->
<script>
let map;
let directionsService;
let directionsRenderer;
let departureMarker;
let destinationMarker;

// Initialize the map
function initMap() {
    // Get coordinates directly from PHP (if available)
    <?php if ($departureGeo && $arrivalGeo): ?>
    const departureLat = <?php echo $departureGeo['lat']; ?>;
    const departureLng = <?php echo $departureGeo['lng']; ?>;
    const destinationLat = <?php echo $arrivalGeo['lat']; ?>;
    const destinationLng = <?php echo $arrivalGeo['lng']; ?>;
    const departureCoords = { lat: departureLat, lng: departureLng };
    const destinationCoords = { lat: destinationLat, lng: destinationLng };
    
    // Use these coordinates directly
    initMapWithCoordinates(departureCoords, destinationCoords);
    <?php else: ?>
    // Fallback to geocoding if coordinates not available
    const departureName = "<?php echo addslashes($ride['departure_location']); ?>";
    const destinationName = "<?php echo addslashes($ride['arrival_location']); ?>";
    
    // Initialize map centered on France
    initMapWithGeocoding(departureName, destinationName);
    <?php endif; ?>
}

// Initialize map with known coordinates
function initMapWithCoordinates(departureCoords, destinationCoords) {
    // Get city names from PHP
    const departureName = "<?php echo addslashes($ride['departure_location']); ?>";
    const destinationName = "<?php echo addslashes($ride['arrival_location']); ?>";
    
    // Initialize map with departure coordinates
    map = new google.maps.Map(document.getElementById("ride-map"), {
        center: departureCoords,
        zoom: 10,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true,
    });
    
    // Initialize directions service and renderer
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        suppressMarkers: true,
        polylineOptions: {
            strokeColor: "#3b5998",
            strokeWeight: 5,
            strokeOpacity: 0.7,
        },
    });
    directionsRenderer.setMap(map);
    
    // Initialize info window
    const infoWindow = new google.maps.InfoWindow();
    
    // Add departure marker
    departureMarker = new google.maps.Marker({
        position: departureCoords,
        map: map,
        icon: {
            url: "https://maps.google.com/mapfiles/ms/icons/green-dot.png",
            scaledSize: new google.maps.Size(40, 40),
        },
        title: departureName,
    });
    
    // Add click listener for departure marker
    departureMarker.addListener("click", () => {
        infoWindow.setContent(`
            <div style="padding: 10px; max-width: 200px;">
                <h3 style="margin-top: 0; color: #3b5998; font-size: 16px;">Point de départ</h3>
                <p style="margin-bottom: 5px;"><strong>${departureName}</strong></p>
                <p style="margin: 0;">Départ à <?php echo (new DateTime($ride['departure_time']))->format('H:i'); ?></p>
            </div>
        `);
        infoWindow.open(map, departureMarker);
    });
    
    // Add destination marker
    destinationMarker = new google.maps.Marker({
        position: destinationCoords,
        map: map,
        icon: {
            url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
            scaledSize: new google.maps.Size(40, 40),
        },
        title: destinationName,
    });
    
    // Add click listener for destination marker
    destinationMarker.addListener("click", () => {
        infoWindow.setContent(`
            <div style="padding: 10px; max-width: 200px;">
                <h3 style="margin-top: 0; color: #3b5998; font-size: 16px;">Destination</h3>
                <p style="margin-bottom: 5px;"><strong>${destinationName}</strong></p>
                <p style="margin: 0;">Arrivée prévue vers <?php 
                    $departureTime = new DateTime($ride['departure_time']);
                    $departureTime->add(new DateInterval("PT{$durationHours}H{$durationMinutes}M"));
                    echo $departureTime->format('H:i'); 
                ?></p>
            </div>
        `);
        infoWindow.open(map, destinationMarker);
    });
    
    // Calculate and display route
    calculateAndDisplayRoute(departureCoords, destinationCoords);
}

// Initialize map using geocoding
function initMapWithGeocoding(departureName, destinationName) {
    // Initialize map centered on France
    map = new google.maps.Map(document.getElementById("ride-map"), {
        center: { lat: 46.603354, lng: 1.888334 },
        zoom: 5,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: true,
    });
    
    // Initialize directions service and renderer
    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        suppressMarkers: true,
        polylineOptions: {
            strokeColor: "#3b5998",
            strokeWeight: 5,
            strokeOpacity: 0.7,
        },
    });
    directionsRenderer.setMap(map);
    
    // Initialize info window
    const infoWindow = new google.maps.InfoWindow();
    
    // Geocode departure and destination
    geocodeAddress(departureName)
        .then((departureCoords) => {
            // Add departure marker
            departureMarker = new google.maps.Marker({
                position: departureCoords,
                map: map,
                icon: {
                    url: "https://maps.google.com/mapfiles/ms/icons/green-dot.png",
                    scaledSize: new google.maps.Size(40, 40),
                },
                title: departureName,
            });
            
            // Add click listener for departure marker
            departureMarker.addListener("click", () => {
                infoWindow.setContent(`
                    <div style="padding: 10px; max-width: 200px;">
                        <h3 style="margin-top: 0; color: #3b5998; font-size: 16px;">Point de départ</h3>
                        <p style="margin-bottom: 5px;"><strong>${departureName}</strong></p>
                        <p style="margin: 0;">Départ à <?php echo (new DateTime($ride['departure_time']))->format('H:i'); ?></p>
                    </div>
                `);
                infoWindow.open(map, departureMarker);
            });
            
            return geocodeAddress(destinationName);
        })
        .then((destinationCoords) => {
            // Add destination marker
            destinationMarker = new google.maps.Marker({
                position: destinationCoords,
                map: map,
                icon: {
                    url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
                    scaledSize: new google.maps.Size(40, 40),
                },
                title: destinationName,
            });
            
            // Add click listener for destination marker
            destinationMarker.addListener("click", () => {
                infoWindow.setContent(`
                    <div style="padding: 10px; max-width: 200px;">
                        <h3 style="margin-top: 0; color: #3b5998; font-size: 16px;">Destination</h3>
                        <p style="margin-bottom: 5px;"><strong>${destinationName}</strong></p>
                        <p style="margin: 0;">Arrivée prévue vers <?php 
                            $departureTime = new DateTime($ride['departure_time']);
                            $departureTime->add(new DateInterval("PT{$durationHours}H{$durationMinutes}M"));
                            echo $departureTime->format('H:i'); 
                        ?></p>
                    </div>
                `);
                infoWindow.open(map, destinationMarker);
            });
            
            // Calculate and display route
            calculateAndDisplayRoute(departureCoords, destinationCoords);
        })
        .catch((error) => {
            console.error("Error loading map:", error);
            
            // Add a message to the map container
            const mapContainer = document.getElementById("ride-map");
            mapContainer.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; background-color: #f5f5f5;">
                    <div style="font-size: 24px; margin-bottom: 10px;"><i class="fas fa-map-marked-alt"></i></div>
                    <div style="text-align: center; padding: 0 20px;">
                        <p>Impossible de charger la carte pour ce trajet.</p>
                        <p>Veuillez réessayer plus tard.</p>
                    </div>
                </div>
            `;
        });
}

// Geocode address to coordinates
function geocodeAddress(address) {
    return new Promise((resolve, reject) => {
        const geocoder = new google.maps.Geocoder();
        
        geocoder.geocode({ address: address, region: 'fr' }, (results, status) => {
            if (status === "OK" && results && results.length > 0) {
                resolve(results[0].geometry.location);
            } else {
                reject(new Error(`Geocoding failed for address: ${address}`));
            }
        });
    });
}

// Calculate and display route between two points
function calculateAndDisplayRoute(origin, destination) {
    directionsService.route(
        {
            origin: origin,
            destination: destination,
            travelMode: google.maps.TravelMode.DRIVING,
        },
        (response, status) => {
            if (status === "OK") {
                directionsRenderer.setDirections(response);
                
                // Get route details
                const route = response.routes[0];
                const leg = route.legs[0];
                
                // Update distance and duration if we don't have them yet
                if (route && leg) {
                    const distanceElement = document.querySelector('.detail-value.distance-value');
                    if (distanceElement) {
                        distanceElement.textContent = leg.distance.text;
                    }
                    
                    const durationElement = document.querySelector('.timeline-duration');
                    if (durationElement) {
                        durationElement.textContent = leg.duration.text;
                    }
                }
                
                // Fit map to route bounds
                const bounds = new google.maps.LatLngBounds();
                bounds.extend(origin);
                bounds.extend(destination);
                map.fitBounds(bounds);
            } else {
                console.error("Directions request failed:", status);
            }
        }
    );
}

// Accordion functionality for mobile
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.ride-driver-section, .ride-details-section, .carbon-footprint-section');
    
    sections.forEach(section => {
        const heading = section.querySelector('h3');
        
        if (heading && window.innerWidth < 768) {
            const content = document.createElement('div');
            content.className = 'section-content';
            
            // Move all elements except the heading into the content div
            Array.from(section.children).forEach(child => {
                if (child !== heading) {
                    content.appendChild(child);
                }
            });
            
            section.appendChild(content);
            
            // Add toggle functionality
            heading.addEventListener('click', () => {
                section.classList.toggle('active');
                content.style.display = section.classList.contains('active') ? 'block' : 'none';
            });
            
            // Initially hide content
            content.style.display = 'none';
        }
    });
});
</script>

<!-- Load Google Maps API -->
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo getenv('GOOGLE_MAPS_API_KEY'); ?>&callback=initMap"></script>
</script>



<?php include 'includes/footer.php'; ?>