<?php
$title = htmlspecialchars($trip['departure_location'] . ' - ' . $trip['arrival_location'] . ' | HopOn');
$description = 'Trajet de ' . htmlspecialchars($trip['departure_location'] . ' à ' . $trip['arrival_location']) . ' le ' . date('d/m/Y', strtotime($trip['departure_time']));
$additionalCss = ['/assets/css/ride-details.css', '/assets/css/google-maps.css'];
$additionalJs = [
    'https://maps.googleapis.com/maps/api/js?key=' . getenv('GOOGLE_MAPS_API_KEY') . '&libraries=places'
];
include 'app/views/partials/header.php';
?>

<div class="container trip-details-container">
    <div class="trip-header">
        <div class="trip-title">
            <h1>
                <span class="from"><?php echo htmlspecialchars($trip['departure_location']); ?></span>
                <i class="fas fa-long-arrow-alt-right"></i>
                <span class="to"><?php echo htmlspecialchars($trip['arrival_location']); ?></span>
            </h1>
            <div class="trip-date">
                <i class="fas fa-calendar-alt"></i>
                <span><?php echo date('l d F Y', strtotime($trip['departure_time'])); ?></span>
            </div>
        </div>
        
        <div class="trip-actions">
            <a href="/trips/<?php echo $trip['id']; ?>/share" class="btn btn-share">
                <i class="fas fa-share-alt"></i> Partager l'itinéraire
            </a>
            <a href="http://localhost/HopOn/app/views/trip_reservation/trip_reservation.php?id=<?php echo $trip['id']; ?>" class="btn btn-primary">
                <i class="fas fa-ticket-alt"></i> Réserver
            </a>
        </div>
    </div>
    
    <div class="trip-content">
        <div class="trip-main">
            <div class="trip-card">
                <div class="trip-info">
                    <div class="trip-route">
                        <div class="trip-point departure">
                            <div class="time"><?php echo date('H:i', strtotime($trip['departure_time'])); ?></div>
                            <div class="point-marker">
                                <i class="fas fa-circle"></i>
                                <div class="route-line"></div>
                            </div>
                            <div class="location">
                                <h3><?php echo htmlspecialchars($trip['departure_location']); ?></h3>
                                <p class="address">Point de départ précis communiqué après réservation</p>
                            </div>
                        </div>
                        
                        <div class="trip-point arrival">
                            <div class="time"><?php echo date('H:i', strtotime($trip['arrival_time_estimated'])); ?></div>
                            <div class="point-marker">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div class="location">
                                <h3><?php echo htmlspecialchars($trip['arrival_location']); ?></h3>
                                <p class="address">Point d'arrivée précis communiqué après réservation</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="trip-details">
                        <div class="detail-item">
                            <i class="fas fa-clock"></i>
                            <div class="detail-content">
                                <span class="label">Durée estimée</span>
                                <span class="value">
                                    <?php 
                                    $departureTime = new DateTime($trip['departure_time']);
                                    $arrivalTime = new DateTime($trip['arrival_time_estimated']);
                                    $duration = $departureTime->diff($arrivalTime);
                                    echo $duration->h . 'h ' . $duration->i . 'min';
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-car"></i>
                            <div class="detail-content">
                                <span class="label">Véhicule</span>
                                <span class="value"><?php echo htmlspecialchars($trip['vehicle_brand'] . ' ' . $trip['vehicle_model']); ?></span>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-users"></i>
                            <div class="detail-content">
                                <span class="label">Places disponibles</span>
                                <span class="value"><?php echo ($trip['seats_offered'] - $trip['seats_booked']); ?>/<?php echo $trip['seats_offered']; ?></span>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <i class="fas fa-euro-sign"></i>
                            <div class="detail-content">
                                <span class="label">Prix par passager</span>
                                <span class="value price"><?php echo number_format($trip['price'], 2); ?> €</span>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($trip['trip_details'])): ?>
                    <div class="trip-description">
                        <h3>Informations complémentaires</h3>
                        <p><?php echo nl2br(htmlspecialchars($trip['trip_details'])); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="trip-card map-card">
                <h2>Itinéraire</h2>
                <div id="route-map" class="trip-map"></div>
            </div>
            
            <div class="trip-card carbon-footprint">
                <h2>Empreinte carbone</h2>
                <div class="carbon-content">
                    <div class="carbon-stats">
                        <div class="carbon-stat">
                            <div class="stat-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo round($carbon['per_person_footprint'] / 1000, 2); ?> kg</div>
                                <div class="stat-label">CO² par personne</div>
                            </div>
                        </div>
                        
                        <div class="carbon-stat">
                            <div class="stat-icon">
                                <i class="fas fa-car"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo round($carbon['total_footprint'] / 1000, 2); ?> kg</div>
                                <div class="stat-label">CO² total du trajet</div>
                            </div>
                        </div>
                        
                        <div class="carbon-stat">
                            <div class="stat-icon">
                                <i class="fas fa-seedling"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-value"><?php echo round($carbon['savings'] / 1000, 2); ?> kg</div>
                                <div class="stat-label">CO² économisé</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="carbon-equivalents">
                        <h3>Votre impact</h3>
                        <p>En partageant ce trajet, vous économisez l'équivalent de :</p>
                        <ul>
                            <li><i class="fas fa-tree"></i> <strong><?php echo $carbon['equivalents']['trees_day']; ?></strong> jours d'absorption d'un arbre</li>
                            <li><i class="fas fa-mobile-alt"></i> <strong><?php echo $carbon['equivalents']['smartphone_charges']; ?></strong> recharges de smartphone</li>
                            <li><i class="fas fa-lightbulb"></i> <strong><?php echo $carbon['equivalents']['light_bulb_hours']; ?></strong> heures d'ampoule LED</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="trip-sidebar">
            <div class="trip-card driver-card">
                <div class="card-header">
                    <h2>Conducteur</h2>
                </div>
                
                <div class="driver-profile">
                    <div class="driver-avatar">
                        <?php if (!empty($trip['driver_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($trip['driver_picture']); ?>" alt="Photo de <?php echo htmlspecialchars($trip['driver_firstname']); ?>">
                        <?php else: ?>
                            <div class="avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="driver-info">
                        <h4><?php echo htmlspecialchars($trip['driver_firstname'] . ' ' . $trip['driver_lastname']); ?></h4>
                        <div class="driver-rating">
                            <span class="rating-stars">
                                <?php 
                                $rating = round($trip['avg_rating']);
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $rating) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </span>
                            <span class="rating-value"><?php echo number_format($trip['avg_rating'], 1); ?></span>
                        </div>
                        <a href="#" class="driver-profile-link">Voir le profil</a>
                    </div>
                </div>
                
                <div class="driver-details">
                    <div class="detail-item">
                        <i class="fas fa-user-check"></i>
                        <span>Membre depuis <?php echo date('F Y', strtotime($trip['driver_joined'])); ?></span>
                    </div>
                    
                    <?php if ($trip['rating_count'] > 0): ?>
                    <div class="detail-item">
                        <i class="fas fa-comment"></i>
                        <span><?php echo $trip['rating_count']; ?> avis</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="trip-card vehicle-card">
                <div class="card-header">
                    <h2>Véhicule</h2>
                </div>
                
                <div class="vehicle-details">
                    <div class="vehicle-image">
                        <i class="fas fa-car"></i>
                    </div>
                    
                    <div class="vehicle-info">
                        <h3><?php echo htmlspecialchars($trip['vehicle_brand'] . ' ' . $trip['vehicle_model']); ?></h3>
                        <div class="vehicle-specs">
                            <div class="spec-item">
                                <i class="fas fa-palette"></i>
                                <span><?php echo htmlspecialchars($trip['vehicle_color']); ?></span>
                            </div>
                            
                            <div class="spec-item">
                                <i class="fas fa-calendar-alt"></i>
                                <span><?php echo htmlspecialchars($trip['vehicle_year']); ?></span>
                            </div>
                            
                            <?php if (!empty($trip['vehicle_features'])): ?>
                            <div class="spec-item">
                                <i class="fas fa-cog"></i>
                                <span>Options: <?php echo htmlspecialchars($trip['vehicle_features']); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="trip-card price-card">
                <div class="card-header">
                    <h2>Prix</h2>
                </div>
                
                <div class="price-details">
                    <div class="price-total">
                        <span class="price-value"><?php echo number_format($trip['price'], 2); ?> €</span>
                        <span class="price-person">par personne</span>
                    </div>
                    
                    <div class="price-includes">
                        <h4>Le prix inclut :</h4>
                        <ul>
                            <li><i class="fas fa-check"></i> Frais de service HopOn</li>
                            <li><i class="fas fa-check"></i> Assurance AXA covoiturage</li>
                            <li><i class="fas fa-check"></i> Assistance routière 24/7</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize map and directions
        initMap();
    });
    
    function initMap() {
        // Create map
        const map = new google.maps.Map(document.getElementById('route-map'), {
            zoom: 10,
            center: {lat: 48.8566, lng: 2.3522}, // Paris by default
            mapTypeId: 'roadmap',
            mapTypeControl: false,
            fullscreenControl: true,
            streetViewControl: false
        });
        
        // Create directions service and renderer
        const directionsService = new google.maps.DirectionsService();
        const directionsRenderer = new google.maps.DirectionsRenderer({
            map: map,
            suppressMarkers: false,
            polylineOptions: {
                strokeColor: '#4CAF50',
                strokeWeight: 5,
                strokeOpacity: 0.8
            }
        });
        
        // Get coordinates from the trip data
        const departureLocation = "<?php echo addslashes($trip['departure_location']); ?>";
        const arrivalLocation = "<?php echo addslashes($trip['arrival_location']); ?>";
        
        // Set up the request
        const request = {
            origin: departureLocation,
            destination: arrivalLocation,
            travelMode: google.maps.TravelMode.DRIVING
        };
        
        // Get directions
        directionsService.route(request, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                // Display the route
                directionsRenderer.setDirections(result);
                
                // Set the map center to the start of the route
                map.setCenter(result.routes[0].legs[0].start_location);
            }
        });
    }
</script>

<?php include 'app/views/partials/footer.php'; ?>