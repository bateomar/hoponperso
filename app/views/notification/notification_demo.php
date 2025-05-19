<?php
require_once 'includes/db_connect.php';
require_once 'includes/notification_service.php';

// Vérifier si l'utilisateur est connecté (normalement)
$isLoggedIn = true; // Pour la démo

// Dans une application réelle, ces données viendraient de la base de données
$demoUser = [
    'id' => 1,
    'prenom' => 'Jean',
    'nom' => 'Dupont',
    'email' => 'jean.dupont@example.com',
    'phone' => '0612345678'
];

$demoRide = [
    'id' => 123,
    'depart' => 'Paris',
    'destination' => 'Lyon',
    'date_heure_depart' => date('Y-m-d H:i:s', strtotime('+2 days')),
    'prix' => '25.00',
    'nombre_places' => 3,
    'conducteur_id' => 2,
    'driver_firstname' => 'Marie',
    'driver_lastname' => 'Martin',
    'driver_phone' => '0698765432',
    'meetup_point' => 'Gare de Lyon, Paris'
];

$demoUpdateMessage = "Bonjour, en raison de travaux sur l'autoroute, je vais prendre un itinéraire alternatif qui pourrait ajouter 15 minutes au trajet. Merci de votre compréhension.";

// Prévisualiser les notifications
$notifications = [
    'booking_confirmation' => [
        'title' => 'Confirmation de réservation',
        'html' => null,
        'function' => function() use ($demoUser, $demoRide) {
            ob_start();
            $title = "Confirmation de votre réservation de covoiturage";
            
            $content = "
            <p>Bonjour " . htmlspecialchars($demoUser['prenom']) . ",</p>
            <p>Votre réservation pour le trajet de <strong>" . htmlspecialchars($demoRide['depart']) . "</strong> à <strong>" . htmlspecialchars($demoRide['destination']) . "</strong> le <strong>" . (new DateTime($demoRide['date_heure_depart']))->format('d/m/Y à H:i') . "</strong> a bien été confirmée.</p>
            <p>Détails du trajet :</p>
            <ul>
                <li>Conducteur : " . htmlspecialchars($demoRide['driver_firstname'] . ' ' . $demoRide['driver_lastname']) . "</li>
                <li>Prix : " . htmlspecialchars($demoRide['prix']) . " €</li>
                <li>Point de rendez-vous : " . (isset($demoRide['meetup_point']) ? htmlspecialchars($demoRide['meetup_point']) : 'À définir') . "</li>
            </ul>
            <p>Vous pouvez consulter les détails de votre réservation en cliquant sur le bouton ci-dessous.</p>";
            
            $actionButton = ["Voir ma réservation", "https://www.example.com/ride_details.php?id=" . $demoRide['id']];
            
            $footer = "<p>Pour toute question, n'hésitez pas à contacter le conducteur ou notre service client.</p>";
            
            echo generateEmailTemplate($title, $content, $actionButton, $footer);
            return ob_get_clean();
        }
    ],
    'ride_reminder' => [
        'title' => 'Rappel de trajet',
        'html' => null,
        'function' => function() use ($demoUser, $demoRide) {
            ob_start();
            $title = "Rappel : Votre trajet a lieu bientôt";
            
            $departureTime = new DateTime($demoRide['date_heure_depart']);
            $content = "
            <p>Bonjour " . htmlspecialchars($demoUser['prenom']) . ",</p>
            <p>Nous vous rappelons que votre trajet de <strong>" . htmlspecialchars($demoRide['depart']) . "</strong> à <strong>" . htmlspecialchars($demoRide['destination']) . "</strong> est prévu le <strong>" . $departureTime->format('d/m/Y à H:i') . "</strong>.</p>";
            
            $content .= "
            <p>Détails du trajet :</p>
            <ul>
                <li>Conducteur : " . htmlspecialchars($demoRide['driver_firstname'] . ' ' . $demoRide['driver_lastname']) . "</li>
                <li>Point de rendez-vous : " . (isset($demoRide['meetup_point']) ? htmlspecialchars($demoRide['meetup_point']) : 'À définir') . "</li>
                <li>Numéro du conducteur : " . (isset($demoRide['driver_phone']) ? htmlspecialchars($demoRide['driver_phone']) : 'Non disponible') . "</li>
            </ul>
            <p>Passez un excellent trajet !</p>";
            
            $actionButton = ["Voir mon trajet", "https://www.example.com/ride_details.php?id=" . $demoRide['id']];
            
            $footer = "<p>N'oubliez pas de contacter le conducteur en cas de retard ou d'imprévu.</p>";
            
            echo generateEmailTemplate($title, $content, $actionButton, $footer);
            return ob_get_clean();
        }
    ],
    'driver_update' => [
        'title' => 'Mise à jour du conducteur',
        'html' => null,
        'function' => function() use ($demoUser, $demoRide, $demoUpdateMessage) {
            ob_start();
            $title = "Mise à jour de votre trajet";
            
            $content = "
            <p>Bonjour " . htmlspecialchars($demoUser['prenom']) . ",</p>
            <p>Le conducteur de votre trajet de <strong>" . htmlspecialchars($demoRide['depart']) . "</strong> à <strong>" . htmlspecialchars($demoRide['destination']) . "</strong> le <strong>" . (new DateTime($demoRide['date_heure_depart']))->format('d/m/Y à H:i') . "</strong> a effectué une mise à jour.</p>
            <p><strong>Type de mise à jour :</strong> Changement d'itinéraire</p>
            <p><strong>Message du conducteur :</strong></p>
            <p>" . nl2br(htmlspecialchars($demoUpdateMessage)) . "</p>
            <p>Vous pouvez consulter les détails de votre trajet en cliquant sur le bouton ci-dessous.</p>";
            
            $actionButton = ["Voir mon trajet", "https://www.example.com/ride_details.php?id=" . $demoRide['id']];
            
            $footer = "<p>Si cette mise à jour impacte votre participation, veuillez contacter le conducteur ou notre service client.</p>";
            
            echo generateEmailTemplate($title, $content, $actionButton, $footer);
            return ob_get_clean();
        }
    ]
];

// Générer le HTML pour chaque notification
foreach ($notifications as $key => &$notification) {
    $notification['html'] = $notification['function']();
}

// Si demande d'envoi de test
$sentStatus = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
    $type = $_POST['notification_type'];
    $email = $_POST['test_email'];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $sentStatus = [
            'success' => false,
            'message' => 'Adresse email invalide.'
        ];
    } else {
        // Envoyer l'email de test
        $success = sendEmail(
            $email,
            'Test - ' . $notifications[$type]['title'],
            $notifications[$type]['html']
        );
        
        $sentStatus = [
            'success' => $success,
            'message' => $success 
                ? 'Email de test envoyé avec succès à ' . $email 
                : 'Échec de l\'envoi de l\'email de test.'
        ];
    }
}

// Page title
$pageTitle = "Démo des notifications";

// Extra CSS
$extraCss = '<link rel="stylesheet" href="assets/css/notifications.css">';

include 'includes/header.php';
?>

<div class="content-container notifications-container">
    <div class="notifications-content">
        <div class="notifications-header">
            <h1>Démo des notifications</h1>
            <p>Cette page vous permet de prévisualiser les différents types de notifications et d'envoyer des tests.</p>
        </div>
        
        <div class="notifications-main">
            <?php if ($sentStatus): ?>
            <div class="alert <?php echo $sentStatus['success'] ? 'alert-success' : 'alert-danger'; ?>" style="margin-bottom: 20px; padding: 10px; border-radius: 4px; background-color: <?php echo $sentStatus['success'] ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $sentStatus['success'] ? '#155724' : '#721c24'; ?>; border: 1px solid <?php echo $sentStatus['success'] ? '#c3e6cb' : '#f5c6cb'; ?>;">
                <?php echo $sentStatus['message']; ?>
            </div>
            <?php endif; ?>
            
            <div style="margin-bottom: 20px;">
                <form method="post" action="" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <div>
                        <label for="notification_type" style="display: block; margin-bottom: 5px;">Type de notification :</label>
                        <select name="notification_type" id="notification_type" style="padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                            <?php foreach ($notifications as $key => $notification): ?>
                            <option value="<?php echo $key; ?>"><?php echo $notification['title']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="test_email" style="display: block; margin-bottom: 5px;">Email de test :</label>
                        <input type="email" name="test_email" id="test_email" placeholder="votre@email.com" required style="padding: 8px; border-radius: 4px; border: 1px solid #ddd; width: 250px;">
                    </div>
                    <div style="align-self: flex-end;">
                        <button type="submit" name="send_test" value="1" style="background-color: var(--primary-color); color: white; border: none; border-radius: 4px; padding: 9px 15px; cursor: pointer;">Envoyer un test</button>
                    </div>
                </form>
            </div>
            
            <div class="notifications-section">
                <h2>Prévisualisations des notifications</h2>
                
                <div id="preview-tabs" style="display: flex; gap: 10px; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <?php foreach ($notifications as $key => $notification): ?>
                    <button 
                        class="preview-tab" 
                        data-target="<?php echo $key; ?>"
                        style="background: none; border: none; padding: 8px 15px; cursor: pointer; font-weight: <?php echo $key === 'booking_confirmation' ? 'bold' : 'normal'; ?>; color: <?php echo $key === 'booking_confirmation' ? 'var(--primary-color)' : 'inherit'; ?>; border-bottom: <?php echo $key === 'booking_confirmation' ? '2px solid var(--primary-color)' : '2px solid transparent'; ?>;"
                    >
                        <?php echo $notification['title']; ?>
                    </button>
                    <?php endforeach; ?>
                </div>
                
                <div id="preview-content">
                    <?php foreach ($notifications as $key => $notification): ?>
                    <div 
                        id="preview-<?php echo $key; ?>" 
                        class="preview-pane"
                        style="display: <?php echo $key === 'booking_confirmation' ? 'block' : 'none'; ?>; border: 1px solid #eee; padding: 15px; border-radius: 4px; background-color: #f9f9f9; margin-bottom: 20px; max-height: 600px; overflow-y: auto;"
                    >
                        <iframe 
                            srcdoc="<?php echo htmlspecialchars($notification['html']); ?>" 
                            style="width: 100%; height: 600px; border: none;"
                        ></iframe>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tabs pour la prévisualisation
    const tabs = document.querySelectorAll('.preview-tab');
    const panes = document.querySelectorAll('.preview-pane');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.getAttribute('data-target');
            
            // Reset all tabs
            tabs.forEach(t => {
                t.style.fontWeight = 'normal';
                t.style.color = 'inherit';
                t.style.borderBottom = '2px solid transparent';
            });
            
            // Activate clicked tab
            this.style.fontWeight = 'bold';
            this.style.color = 'var(--primary-color)';
            this.style.borderBottom = '2px solid var(--primary-color)';
            
            // Hide all panes
            panes.forEach(pane => {
                pane.style.display = 'none';
            });
            
            // Show target pane
            document.getElementById('preview-' + target).style.display = 'block';
            
            // Update select box to match tab
            document.getElementById('notification_type').value = target;
        });
    });
    
    // Sync select box with tabs
    document.getElementById('notification_type').addEventListener('change', function() {
        const value = this.value;
        document.querySelector(`.preview-tab[data-target="${value}"]`).click();
    });
});
</script>

<?php include 'includes/footer.php'; ?>