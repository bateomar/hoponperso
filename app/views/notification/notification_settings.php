<?php
require_once 'includes/db_connect.php';

// Vérifier si l'utilisateur est connecté
$isLoggedIn = false; // Dans une application réelle, vérifiez la session

// Dans une application réelle, récupérez les paramètres de l'utilisateur depuis la base de données
$userSettings = [
    'email' => 'utilisateur@example.com',
    'phone' => '0600000000',
    'notifications' => [
        'booking_confirmation' => true,
        'ride_reminder' => true,
        'driver_update' => true,
        'ride_changes' => true,
        'new_message' => false,
        'rating_reminder' => true
    ],
    'reminder_times' => [
        'ride_reminder' => '24', // heures avant
        'booking_confirmation' => 'immediately'
    ]
];

// Traitement du formulaire de mise à jour des paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Dans une application réelle, mettez à jour la base de données
    // avec les nouvelles préférences de l'utilisateur
    
    // Simuler une mise à jour réussie
    $updateSuccess = true;
}

// Page title
$pageTitle = "Paramètres de notification";

// Extra CSS
$extraCss = '<link rel="stylesheet" href="assets/css/notifications.css">';

include 'includes/header.php';
?>

<div class="content-container notifications-container">
    <div class="notifications-content">
        <div class="notifications-header">
            <h1>Paramètres de notification</h1>
        </div>
        
        <div class="notifications-main">
            <?php if (isset($updateSuccess)): ?>
            <div class="alert alert-success">
                Vos paramètres de notification ont été mis à jour avec succès.
            </div>
            <?php endif; ?>
            
            <form method="post" action="">
                <div class="notifications-section">
                    <h2><i class="fas fa-bell"></i> Notifications de réservation</h2>
                    <p class="notifications-description">
                        Gérez les notifications que vous recevez concernant vos réservations de covoiturage.
                    </p>
                    
                    <div class="notification-option">
                        <div class="notification-option-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="notification-option-info">
                            <div class="notification-option-title">Confirmation de réservation</div>
                            <div class="notification-option-description">
                                Recevez une notification lorsque votre réservation est confirmée.
                            </div>
                            <div class="time-select">
                                <span class="time-select-label">Quand recevoir :</span>
                                <select name="booking_confirmation_time" id="booking_confirmation_time">
                                    <option value="immediately" <?php echo $userSettings['reminder_times']['booking_confirmation'] === 'immediately' ? 'selected' : ''; ?>>Immédiatement</option>
                                </select>
                            </div>
                        </div>
                        <div class="notification-option-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="booking_confirmation" <?php echo $userSettings['notifications']['booking_confirmation'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="notification-option">
                        <div class="notification-option-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="notification-option-info">
                            <div class="notification-option-title">Rappel de trajet</div>
                            <div class="notification-option-description">
                                Soyez notifié avant le départ de votre trajet.
                            </div>
                            <div class="time-select">
                                <span class="time-select-label">Quand recevoir :</span>
                                <select name="ride_reminder_time" id="ride_reminder_time">
                                    <option value="1" <?php echo $userSettings['reminder_times']['ride_reminder'] === '1' ? 'selected' : ''; ?>>1 heure avant</option>
                                    <option value="3" <?php echo $userSettings['reminder_times']['ride_reminder'] === '3' ? 'selected' : ''; ?>>3 heures avant</option>
                                    <option value="12" <?php echo $userSettings['reminder_times']['ride_reminder'] === '12' ? 'selected' : ''; ?>>12 heures avant</option>
                                    <option value="24" <?php echo $userSettings['reminder_times']['ride_reminder'] === '24' ? 'selected' : ''; ?>>24 heures avant</option>
                                    <option value="48" <?php echo $userSettings['reminder_times']['ride_reminder'] === '48' ? 'selected' : ''; ?>>2 jours avant</option>
                                </select>
                            </div>
                        </div>
                        <div class="notification-option-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="ride_reminder" <?php echo $userSettings['notifications']['ride_reminder'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="notifications-section">
                    <h2><i class="fas fa-info-circle"></i> Notifications d'informations</h2>
                    <p class="notifications-description">
                        Restez informé des changements et mises à jour concernant vos trajets.
                    </p>
                    
                    <div class="notification-option">
                        <div class="notification-option-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="notification-option-info">
                            <div class="notification-option-title">Mises à jour du conducteur</div>
                            <div class="notification-option-description">
                                Recevez des notifications lorsque le conducteur envoie des mises à jour ou change d'informations.
                            </div>
                        </div>
                        <div class="notification-option-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="driver_update" <?php echo $userSettings['notifications']['driver_update'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="notification-option">
                        <div class="notification-option-icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div class="notification-option-info">
                            <div class="notification-option-title">Changements de trajet</div>
                            <div class="notification-option-description">
                                Soyez notifié lorsque l'horaire ou l'itinéraire de votre trajet est modifié.
                            </div>
                        </div>
                        <div class="notification-option-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="ride_changes" <?php echo $userSettings['notifications']['ride_changes'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="notifications-section">
                    <h2><i class="fas fa-comment"></i> Notifications sociales</h2>
                    <p class="notifications-description">
                        Gérez les notifications concernant les interactions sociales sur la plateforme.
                    </p>
                    
                    <div class="notification-option">
                        <div class="notification-option-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="notification-option-info">
                            <div class="notification-option-title">Nouveaux messages</div>
                            <div class="notification-option-description">
                                Recevez une notification lorsque vous recevez un nouveau message.
                            </div>
                        </div>
                        <div class="notification-option-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="new_message" <?php echo $userSettings['notifications']['new_message'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="notification-option">
                        <div class="notification-option-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="notification-option-info">
                            <div class="notification-option-title">Rappel d'évaluation</div>
                            <div class="notification-option-description">
                                Recevez un rappel pour évaluer votre trajet après son achèvement.
                            </div>
                        </div>
                        <div class="notification-option-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="rating_reminder" <?php echo $userSettings['notifications']['rating_reminder'] ? 'checked' : ''; ?>>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="notifications-section contact-info-section">
                    <h2><i class="fas fa-address-card"></i> Informations de contact</h2>
                    <p class="notifications-description">
                        Mettez à jour vos coordonnées pour recevoir les notifications.
                    </p>
                    
                    <div class="contact-info-form">
                        <div class="form-group">
                            <label for="email">Adresse email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($userSettings['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Numéro de téléphone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($userSettings['phone']); ?>">
                        </div>
                        
                        <button type="submit" class="save-button">Enregistrer les paramètres</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle d'affichage des sélecteurs de temps en fonction de l'état des checkboxes
    const rideReminderCheckbox = document.querySelector('input[name="ride_reminder"]');
    const rideReminderTimeSelect = document.getElementById('ride_reminder_time');
    
    function updateTimeSelectVisibility(checkbox, timeSelect) {
        if (checkbox && timeSelect) {
            timeSelect.parentElement.style.display = checkbox.checked ? 'flex' : 'none';
        }
    }
    
    // Initialiser l'état
    updateTimeSelectVisibility(rideReminderCheckbox, rideReminderTimeSelect);
    
    // Ajouter des listeners pour les changements
    if (rideReminderCheckbox) {
        rideReminderCheckbox.addEventListener('change', function() {
            updateTimeSelectVisibility(this, rideReminderTimeSelect);
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>