<?php
require_once 'includes/db_connect.php';
require_once 'includes/sharing_functions.php';

// Établir la connexion à la base de données en utilisant la fonction du fichier db_connect.php
$db = connectDB();

// Vérifier si la connexion a réussi
if (!$db) {
    die("La connexion à la base de données n'a pas pu être établie. Veuillez vérifier vos paramètres de connexion.");
}

// Vérifier si les tables nécessaires existent
$requiredTables = ['contacts_confiance', 'trajets_partages', 'partage_contacts', 'positions_suivi'];
$tablesExist = true;

// Obtenir la liste des tables existantes
$stmt = $db->query("SHOW TABLES");
$existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($requiredTables as $table) {
    if (!in_array($table, $existingTables)) {
        $tablesExist = false;
        break;
    }
}

// Si les tables n'existent pas, rediriger vers le script de configuration
if (!$tablesExist) {
    header('Location: setup_sharing_db.php');
    exit;
}

// Pour la démo, on utilise un ID utilisateur statique
// Dans une application réelle, récupérer l'ID de l'utilisateur connecté
$utilisateur_id = 1;

// Vérifier si l'utilisateur est connecté
$isLoggedIn = true; // Pour la démo

// Récupérer les contacts de l'utilisateur
$userContacts = getContactsConfiance($utilisateur_id);

// Récupérer les trajets de l'utilisateur
// Dans une application réelle, utiliser une requête SQL pour obtenir les trajets
$query = "SELECT t.*, u.nom as conducteur_nom, u.prenom as conducteur_prenom, 
         tp.id as trajet_partage_id, tp.code_suivi
         FROM trajets t
         JOIN utilisateurs u ON t.conducteur_id = u.id
         LEFT JOIN trajets_partages tp ON t.id = tp.trajet_id AND tp.utilisateur_id = ?
         WHERE t.date_heure_depart > NOW()
         ORDER BY t.date_heure_depart ASC
         LIMIT 10";

$stmt = $db->prepare($query);
$userRides = [];

if ($stmt) {
    $stmt->execute([$utilisateur_id]);
    $rows = $stmt->fetchAll();
    
    foreach ($rows as $row) {
        // Formater les données
        $shared_with = [];
        
        // Si le trajet est partagé, récupérer les contacts
        if (!empty($row['trajet_partage_id'])) {
            $contacts = getContactsTrajetPartage($row['trajet_partage_id']);
            foreach ($contacts as $contact) {
                $shared_with[] = $contact['contact_id'];
            }
        }
        
        $userRides[] = [
            'id' => $row['id'],
            'depart' => $row['ville_depart'],
            'destination' => $row['ville_arrivee'],
            'date_heure_depart' => $row['date_heure_depart'],
            'prix' => $row['prix'],
            'driver_name' => $row['conducteur_prenom'] . ' ' . $row['conducteur_nom'],
            'shared_with' => $shared_with,
            'trajet_partage_id' => $row['trajet_partage_id'],
            'code_suivi' => $row['code_suivi']
        ];
    }
}

// Traitement de l'ajout d'un contact
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_contact'])) {
    // Valider les entrées
    $nom = trim($_POST['contact_name']);
    $telephone = trim($_POST['contact_phone']);
    $email = trim($_POST['contact_email']);
    $relation = trim($_POST['contact_relation']);
    
    // Vérifier que tous les champs sont remplis
    if (!empty($nom) && !empty($telephone) && !empty($email) && !empty($relation)) {
        // Ajouter le contact à la base de données
        $contact_id = ajouterContactConfiance($utilisateur_id, $nom, $telephone, $email, $relation);
        
        if ($contact_id) {
            // Rediriger avec un message de succès
            header('Location: ride_sharing.php?success=contact_added');
            exit;
        } else {
            header('Location: ride_sharing.php?error=db_error');
            exit;
        }
    } else {
        header('Location: ride_sharing.php?error=missing_fields');
        exit;
    }
}

// Traitement de la suppression d'un contact
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_contact'])) {
    $contact_id = $_POST['contact_id'];
    
    if (supprimerContactConfiance($contact_id, $utilisateur_id)) {
        header('Location: ride_sharing.php?success=contact_deleted');
        exit;
    } else {
        header('Location: ride_sharing.php?error=delete_failed');
        exit;
    }
}

// Traitement de la modification d'un contact
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_contact'])) {
    $contact_id = $_POST['contact_id'];
    $nom = trim($_POST['contact_name']);
    $telephone = trim($_POST['contact_phone']);
    $email = trim($_POST['contact_email']);
    $relation = trim($_POST['contact_relation']);
    
    if (modifierContactConfiance($contact_id, $nom, $telephone, $email, $relation)) {
        header('Location: ride_sharing.php?success=contact_updated');
        exit;
    } else {
        header('Location: ride_sharing.php?error=update_failed');
        exit;
    }
}

// Traitement du partage d'un trajet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['share_ride'])) {
    $rideId = $_POST['ride_id'];
    $contactIds = isset($_POST['contacts']) ? $_POST['contacts'] : [];
    $shareMethod = $_POST['share_method'];
    $customMessage = isset($_POST['custom_message']) ? trim($_POST['custom_message']) : '';
    
    // Vérifier si le trajet est déjà partagé
    $trajetPartage = verifierTrajetPartage($rideId, $utilisateur_id);
    
    if (!$trajetPartage) {
        // Créer un nouveau partage de trajet
        $trajetPartage = creerTrajetPartage($rideId, $utilisateur_id);
        
        if (!$trajetPartage) {
            header('Location: ride_sharing.php?error=share_failed');
            exit;
        }
    }
    
    // Partager avec les contacts sélectionnés
    if (!empty($contactIds)) {
        $success = partagerTrajetAvecContacts($trajetPartage['id'], $contactIds, $shareMethod, $customMessage);
        
        if ($success) {
            header('Location: ride_sharing.php?success=ride_shared');
            exit;
        } else {
            header('Location: ride_sharing.php?error=share_failed');
            exit;
        }
    } else {
        header('Location: ride_sharing.php?error=no_contacts');
        exit;
    }
}

// Récupérer le trajet sélectionné pour le partage (si défini)
$selectedRide = null;
if (isset($_GET['ride_id'])) {
    $rideId = $_GET['ride_id'];
    foreach ($userRides as $ride) {
        if ($ride['id'] == $rideId) {
            $selectedRide = $ride;
            break;
        }
    }
}

// Vérifier si un message de succès doit être affiché
$showSuccess = false;
$successMessage = '';

if (isset($_GET['success'])) {
    $showSuccess = true;
    
    switch ($_GET['success']) {
        case 'contact_added':
            $successMessage = 'Le contact a été ajouté avec succès.';
            break;
        case 'ride_shared':
            $successMessage = 'Le trajet a été partagé avec succès avec vos contacts.';
            break;
        case 'tracking_started':
            $successMessage = 'Le suivi en temps réel a été activé pour ce trajet.';
            break;
    }
}

// Page title
$pageTitle = "Partage d'itinéraire";

// Extra CSS
$extraCss = '<link rel="stylesheet" href="assets/css/ride-sharing.css">';

include 'includes/header.php';
?>

<div class="content-container share-container">
    <div class="share-content">
        <div class="share-header">
            <h1><i class="fas fa-share-alt"></i> Partage d'itinéraire</h1>
            <p>Partagez votre trajet avec vos proches pour une sécurité accrue</p>
        </div>
        
        <div class="share-main">
            <?php if ($showSuccess): ?>
            <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 1rem; border-radius: var(--radius); margin-bottom: 1rem;">
                <?php echo $successMessage; ?>
            </div>
            <?php endif; ?>
            
            <!-- Section Contacts de confiance -->
            <div class="share-section">
                <h2><i class="fas fa-address-book"></i> Contacts de confiance</h2>
                <p class="share-description">
                    Ajoutez des contacts qui recevront des informations sur vos trajets.
                    Ces personnes pourront suivre votre progression en temps réel lors de vos déplacements.
                </p>
                
                <div class="contacts-container">
                    <?php if (empty($userContacts)): ?>
                    <div class="empty-contacts">
                        <i class="fas fa-users"></i>
                        <p>Vous n'avez pas encore ajouté de contacts de confiance.</p>
                        <button type="button" id="show-add-contact-form-btn" class="submit-button">
                            <i class="fas fa-plus"></i> Ajouter un contact
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="contact-list">
                        <?php foreach ($userContacts as $contact): ?>
                        <div class="contact-item">
                            <div class="contact-avatar">
                                <?php echo strtoupper(substr($contact['nom'], 0, 1)); ?>
                            </div>
                            <div class="contact-info">
                                <div class="contact-name"><?php echo $contact['nom']; ?></div>
                                <div class="contact-detail">
                                    <?php echo $contact['telephone']; ?> &bull; <?php echo $contact['email']; ?> &bull; <?php echo $contact['relation']; ?>
                                </div>
                            </div>
                            <div class="contact-actions">
                                <button type="button" class="action-btn edit" data-id="<?php echo $contact['id']; ?>" 
                                        data-nom="<?php echo htmlspecialchars($contact['nom']); ?>"
                                        data-telephone="<?php echo htmlspecialchars($contact['telephone']); ?>"
                                        data-email="<?php echo htmlspecialchars($contact['email']); ?>"
                                        data-relation="<?php echo htmlspecialchars($contact['relation']); ?>"
                                        title="Modifier">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form method="post" action="" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce contact ?');">
                                    <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                    <button type="submit" name="delete_contact" class="action-btn delete" title="Supprimer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="margin-top: 1rem;">
                        <button type="button" id="show-add-contact-form-btn" class="submit-button">
                            <i class="fas fa-plus"></i> Ajouter un contact
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Formulaire d'ajout de contact (masqué par défaut) -->
                    <div class="add-contact-form" id="add-contact-form" style="display: none;">
                        <h3 style="margin-top: 0;">Ajouter un contact de confiance</h3>
                        
                        <form method="post" action="" class="share-form">
                            <div class="form-group">
                                <label for="contact_name">Nom complet</label>
                                <input type="text" id="contact_name" name="contact_name" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contact_phone">Téléphone</label>
                                    <input type="tel" id="contact_phone" name="contact_phone" required>
                                </div>
                                <div class="form-group">
                                    <label for="contact_email">Email</label>
                                    <input type="email" id="contact_email" name="contact_email" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="contact_relation">Relation</label>
                                <select id="contact_relation" name="contact_relation" required>
                                    <option value="">Sélectionnez une relation</option>
                                    <option value="Famille">Famille</option>
                                    <option value="Ami">Ami</option>
                                    <option value="Collègue">Collègue</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            
                            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                <button type="submit" name="add_contact" class="submit-button">
                                    <i class="fas fa-save"></i> Enregistrer le contact
                                </button>
                                <button type="button" id="cancel-add-contact" class="cancel-button">Annuler</button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Formulaire de modification de contact (masqué par défaut) -->
                    <div class="add-contact-form" id="edit-contact-form" style="display: none;">
                        <h3 style="margin-top: 0;">Modifier un contact de confiance</h3>
                        
                        <form method="post" action="" class="share-form">
                            <input type="hidden" id="edit_contact_id" name="contact_id">
                            
                            <div class="form-group">
                                <label for="edit_contact_name">Nom complet</label>
                                <input type="text" id="edit_contact_name" name="contact_name" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_contact_phone">Téléphone</label>
                                    <input type="tel" id="edit_contact_phone" name="contact_phone" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_contact_email">Email</label>
                                    <input type="email" id="edit_contact_email" name="contact_email" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_contact_relation">Relation</label>
                                <select id="edit_contact_relation" name="contact_relation" required>
                                    <option value="">Sélectionnez une relation</option>
                                    <option value="Famille">Famille</option>
                                    <option value="Ami">Ami</option>
                                    <option value="Collègue">Collègue</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            
                            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                <button type="submit" name="edit_contact" class="submit-button">
                                    <i class="fas fa-save"></i> Mettre à jour le contact
                                </button>
                                <button type="button" id="cancel-edit-contact" class="cancel-button">Annuler</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Section Trajets partagés -->
            <div class="share-section">
                <h2><i class="fas fa-route"></i> Trajets partagés</h2>
                <p class="share-description">
                    Sélectionnez les trajets que vous souhaitez partager avec vos contacts de confiance.
                    Vous pouvez également activer le suivi en temps réel pour une sécurité maximale.
                </p>
                
                <div class="rides-container">
                    <?php if (empty($userRides)): ?>
                    <div class="empty-contacts">
                        <i class="fas fa-car"></i>
                        <p>Vous n'avez pas de trajets à venir.</p>
                        <a href="index.php" class="submit-button">
                            <i class="fas fa-search"></i> Rechercher des trajets
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="ride-list">
                        <?php foreach ($userRides as $ride): ?>
                        <div class="ride-card">
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
                                        <i class="fas fa-euro-sign"></i>
                                    </div>
                                    <div>Prix: <?php echo $ride['prix']; ?> €</div>
                                </div>
                                
                                <div class="ride-detail-item">
                                    <div class="ride-detail-icon">
                                        <i class="fas fa-share-alt"></i>
                                    </div>
                                    <div>
                                        <?php if (empty($ride['shared_with'])): ?>
                                        <div class="ride-share-status inactive">
                                            <i class="fas fa-times-circle"></i> Non partagé
                                        </div>
                                        <?php else: ?>
                                        <div class="ride-share-status active">
                                            <i class="fas fa-check-circle"></i> Partagé avec <?php echo count($ride['shared_with']); ?> contact(s)
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="ride-map-preview" id="map-preview-<?php echo $ride['id']; ?>"></div>
                            
                            <div class="ride-actions">
                                <a href="ride_details.php?id=<?php echo $ride['id']; ?>" class="cancel-button">
                                    <i class="fas fa-info-circle"></i> Détails
                                </a>
                                <button type="button" class="share-button open-share-modal" data-ride-id="<?php echo $ride['id']; ?>">
                                    <i class="fas fa-share-alt"></i> Partager
                                </button>
                                <a href="live_tracking.php?ride_id=<?php echo $ride['id']; ?>" class="submit-button">
                                    <i class="fas fa-broadcast-tower"></i> Suivi en direct
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de partage -->
<div class="share-modal" id="share-modal">
    <div class="share-modal-content">
        <div class="share-modal-header">
            <h2>Partager ce trajet</h2>
            <button type="button" class="share-modal-close">&times;</button>
        </div>
        <div class="share-modal-body">
            <form method="post" action="" id="share-ride-form">
                <input type="hidden" name="share_ride" value="1">
                <input type="hidden" name="ride_id" id="modal-ride-id" value="">
                
                <div class="form-group">
                    <label>Sélectionnez vos contacts de confiance</label>
                    
                    <?php if (empty($userContacts)): ?>
                    <div style="padding: 1rem; text-align: center; color: var(--light-text);">
                        <p>Vous n'avez pas encore ajouté de contacts de confiance.</p>
                        <button type="button" id="add-contact-from-modal" class="submit-button" style="margin-top: 1rem;">
                            <i class="fas fa-plus"></i> Ajouter un contact
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="contact-list" style="margin-top: 1rem;">
                        <?php foreach ($userContacts as $contact): ?>
                        <div class="contact-item">
                            <label style="display: flex; align-items: center; cursor: pointer; width: 100%;">
                                <input type="checkbox" name="contacts[]" value="<?php echo $contact['id']; ?>" style="margin-right: 1rem;">
                                <div class="contact-avatar">
                                    <?php echo strtoupper(substr($contact['nom'], 0, 1)); ?>
                                </div>
                                <div class="contact-info">
                                    <div class="contact-name"><?php echo $contact['nom']; ?></div>
                                    <div class="contact-detail">
                                        <?php echo $contact['telephone']; ?> &bull; <?php echo $contact['email']; ?>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group" style="margin-top: 1.5rem;">
                    <label>Choisissez la méthode de partage</label>
                    
                    <div class="share-methods">
                        <div class="share-method selected" data-method="email">
                            <div class="share-method-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="share-method-info">
                                <div class="share-method-title">Notification par Email</div>
                                <div class="share-method-description">
                                    Envoyer un email avec les détails du trajet et un lien pour suivre votre progression.
                                </div>
                            </div>
                        </div>
                        
                        <div class="share-method" data-method="sms">
                            <div class="share-method-icon">
                                <i class="fas fa-sms"></i>
                            </div>
                            <div class="share-method-info">
                                <div class="share-method-title">Notification par SMS</div>
                                <div class="share-method-description">
                                    Envoyer un SMS avec les détails du trajet et un lien pour suivre votre progression.
                                </div>
                            </div>
                        </div>
                        
                        <div class="share-method" data-method="both">
                            <div class="share-method-icon">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div class="share-method-info">
                                <div class="share-method-title">Email et SMS</div>
                                <div class="share-method-description">
                                    Envoyer une notification par email et SMS pour une sécurité maximale.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="share_method" id="share-method" value="email">
                </div>
                
                <div class="form-group">
                    <label>Message personnalisé (optionnel)</label>
                    <textarea name="custom_message" rows="3" placeholder="Ajoutez un message personnalisé pour vos contacts..."></textarea>
                </div>
                
                <div class="share-tracking-link">
                    <label>Lien de suivi public</label>
                    <div class="tracking-link-input">
                        <input type="text" value="https://hopon.example.com/track/ABC123XYZ" readonly>
                        <button type="button" class="copy-link-btn" title="Copier le lien">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <div class="tracking-link-expire">
                        Ce lien expire 24h après votre arrivée à destination.
                    </div>
                </div>
            </form>
        </div>
        <div class="share-modal-footer">
            <button type="button" class="cancel-button" id="cancel-share">Annuler</button>
            <button type="submit" form="share-ride-form" class="submit-button">
                <i class="fas fa-share-alt"></i> Partager
            </button>
        </div>
    </div>
</div>

<!-- Google Maps JavaScript -->
<script>
// Fonction pour initialiser toutes les cartes de prévisualisation
function initMaps() {
    <?php foreach ($userRides as $ride): ?>
    // Carte pour le trajet <?php echo $ride['id']; ?>
    const map<?php echo $ride['id']; ?> = new google.maps.Map(
        document.getElementById('map-preview-<?php echo $ride['id']; ?>'),
        {
            center: { lat: 46.603354, lng: 1.888334 }, // Centre de la France
            zoom: 5,
            mapTypeControl: false,
            streetViewControl: false,
            zoomControl: false,
            fullscreenControl: false
        }
    );
    
    // Géocodage des villes
    geocodeCity('<?php echo addslashes($ride['depart']); ?>')
        .then(departureCoords => {
            // Marqueur de départ
            new google.maps.Marker({
                position: departureCoords,
                map: map<?php echo $ride['id']; ?>,
                icon: {
                    url: "https://maps.google.com/mapfiles/ms/icons/green-dot.png",
                    scaledSize: new google.maps.Size(30, 30),
                },
                title: '<?php echo addslashes($ride['depart']); ?>'
            });
            
            return geocodeCity('<?php echo addslashes($ride['destination']); ?>')
                .then(destinationCoords => {
                    // Marqueur de destination
                    new google.maps.Marker({
                        position: destinationCoords,
                        map: map<?php echo $ride['id']; ?>,
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
                    
                    routeLine.setMap(map<?php echo $ride['id']; ?>);
                    
                    // Ajuster la vue pour montrer la ligne
                    const bounds = new google.maps.LatLngBounds();
                    bounds.extend(departureCoords);
                    bounds.extend(destinationCoords);
                    map<?php echo $ride['id']; ?>.fitBounds(bounds);
                });
        })
        .catch(error => {
            console.error('Erreur lors du géocodage pour le trajet <?php echo $ride['id']; ?>:', error);
        });
    <?php endforeach; ?>
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

// Charger Google Maps API
function loadGoogleMapsAPI() {
    const script = document.createElement('script');
    script.src = `https://maps.googleapis.com/maps/api/js?key=<?php echo getenv('GOOGLE_MAPS_API_KEY'); ?>&callback=initMaps`;
    script.async = true;
    script.defer = true;
    document.head.appendChild(script);
}

// Gestionnaires d'événements pour le formulaire d'ajout de contact
document.addEventListener('DOMContentLoaded', function() {
    const addContactForm = document.getElementById('add-contact-form');
    const editContactForm = document.getElementById('edit-contact-form');
    const showAddContactFormBtn = document.getElementById('show-add-contact-form-btn');
    const cancelAddContactBtn = document.getElementById('cancel-add-contact');
    const cancelEditContactBtn = document.getElementById('cancel-edit-contact');
    
    // Gestion du formulaire d'ajout
    if (showAddContactFormBtn) {
        showAddContactFormBtn.addEventListener('click', function() {
            // Masquer le formulaire d'édition s'il est visible
            if (editContactForm) {
                editContactForm.style.display = 'none';
            }
            
            addContactForm.style.display = 'block';
            showAddContactFormBtn.style.display = 'none';
        });
    }
    
    if (cancelAddContactBtn) {
        cancelAddContactBtn.addEventListener('click', function() {
            addContactForm.style.display = 'none';
            showAddContactFormBtn.style.display = 'inline-block';
        });
    }
    
    // Gestion du formulaire d'édition
    const editButtons = document.querySelectorAll('.action-btn.edit');
    
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const contactId = this.getAttribute('data-id');
            const nom = this.getAttribute('data-nom');
            const telephone = this.getAttribute('data-telephone');
            const email = this.getAttribute('data-email');
            const relation = this.getAttribute('data-relation');
            
            // Remplir le formulaire avec les données existantes
            document.getElementById('edit_contact_id').value = contactId;
            document.getElementById('edit_contact_name').value = nom;
            document.getElementById('edit_contact_phone').value = telephone;
            document.getElementById('edit_contact_email').value = email;
            document.getElementById('edit_contact_relation').value = relation;
            
            // Masquer le formulaire d'ajout s'il est visible
            if (addContactForm) {
                addContactForm.style.display = 'none';
            }
            
            // Masquer le bouton d'ajout
            if (showAddContactFormBtn) {
                showAddContactFormBtn.style.display = 'none';
            }
            
            // Afficher le formulaire d'édition
            editContactForm.style.display = 'block';
            
            // Faire défiler jusqu'au formulaire
            editContactForm.scrollIntoView({ behavior: 'smooth' });
        });
    });
    
    if (cancelEditContactBtn) {
        cancelEditContactBtn.addEventListener('click', function() {
            editContactForm.style.display = 'none';
            showAddContactFormBtn.style.display = 'inline-block';
        });
    }
    
    // Gestionnaires d'événements pour le modal de partage
    const shareModal = document.getElementById('share-modal');
    const openShareModalBtns = document.querySelectorAll('.open-share-modal');
    const closeShareModalBtn = document.querySelector('.share-modal-close');
    const cancelShareBtn = document.getElementById('cancel-share');
    const modalRideId = document.getElementById('modal-ride-id');
    
    openShareModalBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const rideId = this.getAttribute('data-ride-id');
            modalRideId.value = rideId;
            
            // Présélectionner les contacts déjà partagés
            const checkboxes = document.querySelectorAll('input[name="contacts[]"]');
            <?php 
            echo "const sharedWith = {";
            foreach ($userRides as $ride) {
                echo $ride['id'] . ": [" . implode(',', $ride['shared_with']) . "],";
            }
            echo "};";
            ?>
            
            checkboxes.forEach(checkbox => {
                const contactId = parseInt(checkbox.value);
                if (sharedWith[rideId] && sharedWith[rideId].includes(contactId)) {
                    checkbox.checked = true;
                } else {
                    checkbox.checked = false;
                }
            });
            
            shareModal.classList.add('active');
        });
    });
    
    if (closeShareModalBtn) {
        closeShareModalBtn.addEventListener('click', function() {
            shareModal.classList.remove('active');
        });
    }
    
    if (cancelShareBtn) {
        cancelShareBtn.addEventListener('click', function() {
            shareModal.classList.remove('active');
        });
    }
    
    // Sélection de la méthode de partage
    const shareMethods = document.querySelectorAll('.share-method');
    const shareMethodInput = document.getElementById('share-method');
    
    shareMethods.forEach(method => {
        method.addEventListener('click', function() {
            shareMethods.forEach(m => m.classList.remove('selected'));
            this.classList.add('selected');
            shareMethodInput.value = this.getAttribute('data-method');
        });
    });
    
    // Bouton pour copier le lien de suivi
    const copyLinkBtn = document.querySelector('.copy-link-btn');
    const trackingLinkInput = document.querySelector('.tracking-link-input input');
    
    if (copyLinkBtn && trackingLinkInput) {
        copyLinkBtn.addEventListener('click', function() {
            trackingLinkInput.select();
            document.execCommand('copy');
            
            // Feedback visuel
            const originalIcon = this.innerHTML;
            this.innerHTML = '<i class="fas fa-check"></i>';
            
            setTimeout(() => {
                this.innerHTML = originalIcon;
            }, 2000);
        });
    }
    
    // Ajouter un contact depuis le modal
    const addContactFromModalBtn = document.getElementById('add-contact-from-modal');
    
    if (addContactFromModalBtn) {
        addContactFromModalBtn.addEventListener('click', function() {
            shareModal.classList.remove('active');
            
            // Afficher le formulaire d'ajout de contact
            if (addContactForm && showAddContactFormBtn) {
                addContactForm.style.display = 'block';
                showAddContactFormBtn.style.display = 'none';
                
                // Faire défiler jusqu'au formulaire
                addContactForm.scrollIntoView({ behavior: 'smooth' });
            }
        });
    }
    
    // Charger Google Maps
    loadGoogleMapsAPI();
});
</script>

<?php include 'includes/footer.php'; ?>