<?php
require_once 'includes/db_connect.php';
require_once 'includes/ride_functions.php';

// Vérifier si l'ID du trajet est fourni
if (!isset($_GET['ride_id']) || empty($_GET['ride_id'])) {
    header('Location: index.php');
    exit;
}

$rideId = $_GET['ride_id'];

// Récupérer les détails du trajet
$ride = getRideById($rideId);

if (!$ride) {
    header('Location: index.php');
    exit;
}

// Page title
$pageTitle = "Points de rendez-vous - {$ride['depart']} à {$ride['destination']}";

// Extra CSS
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
                <a href="ride_details.php?id=<?php echo $rideId; ?>"><i class="fas fa-arrow-left"></i> Retour aux détails du trajet</a>
            </div>
            <h1>Points de rendez-vous</h1>
            <p>Trajet de <?php echo htmlspecialchars($ride['depart']); ?> à <?php echo htmlspecialchars($ride['destination']); ?></p>
        </div>
        
        <div class="ride-details-main">
            <div class="map-controls">
                <button class="map-control-btn" id="showDeparturePoints">
                    <i class="fas fa-map-marker-alt"></i> Points de départ
                </button>
                <button class="map-control-btn" id="showDestinationPoints">
                    <i class="fas fa-flag-checkered"></i> Points d'arrivée
                </button>
                <button class="map-control-btn" id="showBothPoints">
                    <i class="fas fa-route"></i> Itinéraire complet
                </button>
            </div>
            
            <div class="map-container">
                <div id="google-map"></div>
                <div class="search-box">
                    <input
                        id="pac-input"
                        class="search-input"
                        type="text"
                        placeholder="Rechercher un point de rendez-vous..."
                    />
                </div>
            </div>
            
            <div class="meetup-points-container">
                <div class="meetup-points-title">
                    <i class="fas fa-map-marker-alt"></i>
                    <h3>Points de rendez-vous suggérés</h3>
                </div>
                
                <div id="meetup-points-list">
                    <!-- Points will be populated by JavaScript -->
                    <div class="meetup-point-card">
                        <div class="meetup-point-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="meetup-point-info">
                            <div class="meetup-point-name">Gare de Lyon</div>
                            <div class="meetup-point-address">20 Boulevard Diderot, 75012 Paris</div>
                            <div class="meetup-point-details">
                                <div class="meetup-detail">
                                    <i class="fas fa-walking"></i> 5 min à pied
                                </div>
                                <div class="meetup-detail">
                                    <i class="fas fa-parking"></i> Parking disponible
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="add-custom-point" id="add-custom-point">
                        <i class="fas fa-plus-circle"></i> Ajouter un point personnalisé
                    </div>
                </div>
            </div>
            
            <div class="ride-booking-section">
                <a href="#" class="booking-btn" id="confirm-meetup-point">Confirmer le point de rendez-vous</a>
                <a href="ride_details.php?id=<?php echo $rideId; ?>" class="contact-driver-btn">Annuler</a>
            </div>
        </div>
    </div>
</div>

<!-- Google Maps JavaScript -->
<script>
let map;
let directionsService;
let directionsRenderer;
let departureMarker;
let destinationMarker;
let customMarker;
let autocomplete;
let infoWindow;

// Store coordinates
const departureCoords = { lat: null, lng: null };
const destinationCoords = { lat: null, lng: null };

// Selected meetup point
let selectedMeetupPoint = null;

// Meetup points data (will be populated)
let meetupPoints = [];

// Initialize the map
function initMap() {
    // Get coordinates from PHP
    const departureName = "<?php echo addslashes($ride['depart']); ?>";
    const destinationName = "<?php echo addslashes($ride['destination']); ?>";
    
    // Initialize map centered on France
    map = new google.maps.Map(document.getElementById("google-map"), {
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
    infoWindow = new google.maps.InfoWindow();
    
    // Initialize autocomplete
    autocomplete = new google.maps.places.Autocomplete(
        document.getElementById("pac-input"),
        {
            types: ["establishment", "geocode"],
            componentRestrictions: { country: "fr" },
            fields: ["geometry", "name", "formatted_address"],
        }
    );
    
    // Set up event listener for place changed
    autocomplete.addListener("place_changed", () => {
        const place = autocomplete.getPlace();
        
        if (!place.geometry || !place.geometry.location) {
            window.alert("Aucun détail disponible pour: '" + place.name + "'");
            return;
        }
        
        // Add custom marker
        addCustomMarker(place);
    });
    
    // Geocode departure and destination
    geocodeAddress(departureName, true)
        .then((coords) => {
            departureCoords.lat = coords.lat;
            departureCoords.lng = coords.lng;
            
            return geocodeAddress(destinationName, false);
        })
        .then((coords) => {
            destinationCoords.lat = coords.lat;
            destinationCoords.lng = coords.lng;
            
            // Add markers for departure and destination
            addMarkers();
            
            // Calculate and display route
            calculateAndDisplayRoute();
            
            // Find and suggest meetup points
            findMeetupPoints();
        })
        .catch((error) => {
            console.error("Error initializing map:", error);
        });
    
    // Set up control buttons
    document.getElementById("showDeparturePoints").addEventListener("click", showDeparturePoints);
    document.getElementById("showDestinationPoints").addEventListener("click", showDestinationPoints);
    document.getElementById("showBothPoints").addEventListener("click", showBothPoints);
    document.getElementById("add-custom-point").addEventListener("click", focusSearchBox);
    document.getElementById("confirm-meetup-point").addEventListener("click", confirmMeetupPoint);
}

// Geocode an address to get coordinates
function geocodeAddress(address, isDeparture) {
    return new Promise((resolve, reject) => {
        const geocoder = new google.maps.Geocoder();
        
        geocoder.geocode({ address: address }, (results, status) => {
            if (status === "OK" && results[0]) {
                const location = results[0].geometry.location;
                resolve({
                    lat: location.lat(),
                    lng: location.lng(),
                });
            } else {
                reject(`Geocode failed for ${address}: ${status}`);
            }
        });
    });
}

// Add markers for departure and destination
function addMarkers() {
    // Departure marker
    departureMarker = new google.maps.Marker({
        position: departureCoords,
        map: map,
        icon: {
            url: "https://maps.google.com/mapfiles/ms/icons/green-dot.png",
            scaledSize: new google.maps.Size(40, 40),
        },
        title: "<?php echo addslashes($ride['depart']); ?>",
    });
    
    // Add click listener for departure marker
    departureMarker.addListener("click", () => {
        infoWindow.setContent(`
            <div>
                <h3>Point de départ</h3>
                <p><?php echo addslashes($ride['depart']); ?></p>
                <p>Heure de départ: <?php echo (new DateTime($ride['date_heure_depart']))->format('H:i'); ?></p>
            </div>
        `);
        infoWindow.open(map, departureMarker);
    });
    
    // Destination marker
    destinationMarker = new google.maps.Marker({
        position: destinationCoords,
        map: map,
        icon: {
            url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
            scaledSize: new google.maps.Size(40, 40),
        },
        title: "<?php echo addslashes($ride['destination']); ?>",
    });
    
    // Add click listener for destination marker
    destinationMarker.addListener("click", () => {
        infoWindow.setContent(`
            <div>
                <h3>Point d'arrivée</h3>
                <p><?php echo addslashes($ride['destination']); ?></p>
            </div>
        `);
        infoWindow.open(map, destinationMarker);
    });
}

// Calculate and display route
function calculateAndDisplayRoute() {
    directionsService.route(
        {
            origin: departureCoords,
            destination: destinationCoords,
            travelMode: google.maps.TravelMode.DRIVING,
        },
        (response, status) => {
            if (status === "OK") {
                directionsRenderer.setDirections(response);
            } else {
                console.error("Directions request failed:", status);
            }
        }
    );
}

// Add custom marker from search
function addCustomMarker(place) {
    // Remove previous custom marker if exists
    if (customMarker) {
        customMarker.setMap(null);
    }
    
    // Add new marker
    customMarker = new google.maps.Marker({
        position: place.geometry.location,
        map: map,
        animation: google.maps.Animation.DROP,
        icon: {
            url: "https://maps.google.com/mapfiles/ms/icons/blue-dot.png",
            scaledSize: new google.maps.Size(40, 40),
        },
        title: place.name,
    });
    
    // Add info window
    customMarker.addListener("click", () => {
        infoWindow.setContent(`
            <div>
                <h3>${place.name}</h3>
                <p>${place.formatted_address}</p>
                <button id="select-custom-point" style="
                    background-color: #3b5998;
                    color: white;
                    border: none;
                    padding: 5px 10px;
                    border-radius: 4px;
                    cursor: pointer;
                    margin-top: 5px;
                ">Sélectionner ce point</button>
            </div>
        `);
        infoWindow.open(map, customMarker);
        
        // Add event listener after info window is open
        google.maps.event.addListenerOnce(infoWindow, "domready", () => {
            document.getElementById("select-custom-point").addEventListener("click", () => {
                selectCustomPoint(place);
                infoWindow.close();
            });
        });
    });
    
    // Fit map to include new marker
    map.setCenter(place.geometry.location);
    map.setZoom(15);
}

// Select custom point
function selectCustomPoint(place) {
    // Create a custom meetup point
    const customPoint = {
        id: "custom-" + Date.now(),
        name: place.name,
        address: place.formatted_address,
        position: {
            lat: place.geometry.location.lat(),
            lng: place.geometry.location.lng(),
        },
        type: "custom",
        details: [
            { icon: "location-dot", text: "Point personnalisé" }
        ]
    };
    
    // Add to meetup points
    meetupPoints.push(customPoint);
    
    // Select this point
    selectMeetupPoint(customPoint);
    
    // Update the UI
    updateMeetupPointsList();
}

// Find meetup points
function findMeetupPoints() {
    // For now we'll use hardcoded points near the departure and destination
    // In a real app, these would come from a Places API request or backend database
    
    // Calculate a bounding box around departure (roughly 1km)
    const departurePoints = [
        {
            id: "dep-1",
            name: "Gare principale",
            address: `Près de ${departureName}`,
            position: {
                lat: departureCoords.lat + 0.005,
                lng: departureCoords.lng - 0.002
            },
            type: "departure",
            details: [
                { icon: "walking", text: "5 min à pied" },
                { icon: "parking", text: "Parking disponible" }
            ]
        },
        {
            id: "dep-2",
            name: "Station de métro",
            address: `Au centre de ${departureName}`,
            position: {
                lat: departureCoords.lat - 0.003,
                lng: departureCoords.lng + 0.004
            },
            type: "departure",
            details: [
                { icon: "train", text: "Transports en commun" },
                { icon: "shield", text: "Zone sécurisée" }
            ]
        }
    ];
    
    // Calculate a bounding box around destination (roughly 1km)
    const destinationPoints = [
        {
            id: "dest-1",
            name: "Centre commercial",
            address: `Près de ${destinationName}`,
            position: {
                lat: destinationCoords.lat + 0.004,
                lng: destinationCoords.lng + 0.003
            },
            type: "destination",
            details: [
                { icon: "store", text: "Commodités à proximité" },
                { icon: "parking", text: "Grand parking" }
            ]
        },
        {
            id: "dest-2",
            name: "Parc municipal",
            address: `${destinationName} ouest`,
            position: {
                lat: destinationCoords.lat - 0.002,
                lng: destinationCoords.lng - 0.005
            },
            type: "destination",
            details: [
                { icon: "tree", text: "Environnement calme" },
                { icon: "bench", text: "Zone d'attente" }
            ]
        }
    ];
    
    // Combine all points
    meetupPoints = [...departurePoints, ...destinationPoints];
    
    // Add markers for meetup points
    addMeetupPointMarkers();
    
    // Update the UI
    updateMeetupPointsList();
}

// Add markers for meetup points
function addMeetupPointMarkers() {
    meetupPoints.forEach(point => {
        const marker = new google.maps.Marker({
            position: point.position,
            map: map,
            icon: {
                url: point.type === "departure" 
                    ? "https://maps.google.com/mapfiles/ms/icons/blue-dot.png"
                    : "https://maps.google.com/mapfiles/ms/icons/purple-dot.png",
                scaledSize: new google.maps.Size(30, 30),
            },
            title: point.name
        });
        
        // Store marker reference in the point object
        point.marker = marker;
        
        // Add click listener
        marker.addListener("click", () => {
            infoWindow.setContent(`
                <div>
                    <h3>${point.name}</h3>
                    <p>${point.address}</p>
                    <p>${point.details.map(detail => `<i class="fas fa-${detail.icon}"></i> ${detail.text}`).join("<br>")}</p>
                    <button id="select-point-${point.id}" style="
                        background-color: #3b5998;
                        color: white;
                        border: none;
                        padding: 5px 10px;
                        border-radius: 4px;
                        cursor: pointer;
                        margin-top: 5px;
                    ">Sélectionner ce point</button>
                </div>
            `);
            infoWindow.open(map, marker);
            
            // Add event listener after info window is open
            google.maps.event.addListenerOnce(infoWindow, "domready", () => {
                document.getElementById(`select-point-${point.id}`).addEventListener("click", () => {
                    selectMeetupPoint(point);
                    infoWindow.close();
                });
            });
        });
    });
}

// Update the meetup points list in the UI
function updateMeetupPointsList() {
    const listContainer = document.getElementById("meetup-points-list");
    
    // Clear existing content except the last item (add custom point)
    while (listContainer.children.length > 1) {
        listContainer.removeChild(listContainer.firstChild);
    }
    
    // Add each meetup point
    meetupPoints.forEach(point => {
        const pointElement = document.createElement("div");
        pointElement.className = `meetup-point-card${selectedMeetupPoint && selectedMeetupPoint.id === point.id ? ' selected' : ''}`;
        pointElement.dataset.pointId = point.id;
        
        pointElement.innerHTML = `
            <div class="meetup-point-icon">
                <i class="fas fa-${point.type === 'departure' ? 'map-marker-alt' : 'flag-checkered'}"></i>
            </div>
            <div class="meetup-point-info">
                <div class="meetup-point-name">${point.name}</div>
                <div class="meetup-point-address">${point.address}</div>
                <div class="meetup-point-details">
                    ${point.details.map(detail => `
                        <div class="meetup-detail">
                            <i class="fas fa-${detail.icon}"></i> ${detail.text}
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
        
        // Add click event
        pointElement.addEventListener("click", () => {
            selectMeetupPoint(point);
        });
        
        // Insert before the last child (add custom point)
        listContainer.insertBefore(pointElement, listContainer.lastChild);
    });
}

// Select a meetup point
function selectMeetupPoint(point) {
    // If there was a previously selected point, reset its marker
    if (selectedMeetupPoint && selectedMeetupPoint.marker) {
        selectedMeetupPoint.marker.setAnimation(null);
    }
    
    // Set the new selected point
    selectedMeetupPoint = point;
    
    // Animate the marker
    if (point.marker) {
        point.marker.setAnimation(google.maps.Animation.BOUNCE);
        setTimeout(() => {
            point.marker.setAnimation(null);
        }, 2100);
        
        // Center map on the selected point
        map.setCenter(point.position);
        map.setZoom(15);
    }
    
    // Update the UI
    updateMeetupPointsList();
}

// Show only departure points
function showDeparturePoints() {
    meetupPoints.forEach(point => {
        if (point.marker) {
            point.marker.setVisible(point.type === "departure" || point.type === "custom");
        }
    });
    
    // Hide destination marker
    destinationMarker.setVisible(false);
    
    // Show departure marker
    departureMarker.setVisible(true);
    
    // Clear directions
    directionsRenderer.setDirections({ routes: [] });
    
    // Center on departure
    map.setCenter(departureCoords);
    map.setZoom(13);
}

// Show only destination points
function showDestinationPoints() {
    meetupPoints.forEach(point => {
        if (point.marker) {
            point.marker.setVisible(point.type === "destination" || point.type === "custom");
        }
    });
    
    // Hide departure marker
    departureMarker.setVisible(false);
    
    // Show destination marker
    destinationMarker.setVisible(true);
    
    // Clear directions
    directionsRenderer.setDirections({ routes: [] });
    
    // Center on destination
    map.setCenter(destinationCoords);
    map.setZoom(13);
}

// Show all points and route
function showBothPoints() {
    meetupPoints.forEach(point => {
        if (point.marker) {
            point.marker.setVisible(true);
        }
    });
    
    // Show both markers
    departureMarker.setVisible(true);
    destinationMarker.setVisible(true);
    
    // Recalculate and display route
    calculateAndDisplayRoute();
    
    // Fit map to show the route
    const bounds = new google.maps.LatLngBounds();
    bounds.extend(departureCoords);
    bounds.extend(destinationCoords);
    map.fitBounds(bounds);
}

// Focus the search box
function focusSearchBox() {
    document.getElementById("pac-input").focus();
}

// Confirm meetup point selection
function confirmMeetupPoint(e) {
    e.preventDefault();
    
    if (!selectedMeetupPoint) {
        alert("Veuillez sélectionner un point de rendez-vous");
        return;
    }
    
    // In a real app, this would save the selection to the database
    alert(`Vous avez sélectionné "${selectedMeetupPoint.name}" comme point de rendez-vous.`);
    
    // Redirect back to ride details
    window.location.href = `ride_details.php?id=<?php echo $rideId; ?>`;
}

// Load Google Maps API
function loadGoogleMapsAPI() {
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=<?php echo getenv('GOOGLE_MAPS_API_KEY'); ?>&libraries=places&callback=initMap`;
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
}

// Load the Google Maps API when DOM is ready
document.addEventListener('DOMContentLoaded', loadGoogleMapsAPI);
</script>

<?php include 'includes/footer.php'; ?>