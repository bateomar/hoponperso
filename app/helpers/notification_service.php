<?php
/**
 * Service de gestion des notifications pour HopOn
 * 
 * Ce fichier contient les fonctions nécessaires pour envoyer des notifications
 * par email et SMS aux utilisateurs concernant leurs trajets.
 */

/**
 * Envoie un email à l'utilisateur
 * 
 * @param string $to Adresse email du destinataire
 * @param string $subject Sujet de l'email
 * @param string $message Corps du message HTML
 * @return bool Succès ou échec de l'envoi
 */
function sendEmail($to, $subject, $message) {
    // Headers de l'email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: HopOn <noreply@hopon.com>" . "\r\n";
    
    // Dans un environnement de production, utilisez un service d'email comme:
    // - PHPMailer pour SMTP
    // - SendGrid ou Mailgun via leur API
    
    // Pour le développement, nous utilisons la fonction mail() de PHP
    return mail($to, $subject, $message, $headers);
}

/**
 * Envoie un SMS à l'utilisateur via un service tiers
 * 
 * @param string $phoneNumber Numéro de téléphone du destinataire
 * @param string $message Contenu du SMS
 * @return bool Succès ou échec de l'envoi
 */
function sendSMS($phoneNumber, $message) {
    // Dans un environnement de production, utilisez un service comme:
    // - Twilio
    // - Nexmo/Vonage
    // - OVH SMS
    
    // Pour le développement, nous simulons l'envoi
    return true;
}

/**
 * Génère le contenu HTML d'un email
 * 
 * @param string $title Titre principal de l'email
 * @param string $content Corps du message
 * @param array $actionButton Bouton d'action [texte, url]
 * @param string $footer Texte de pied de page
 * @return string HTML formaté pour l'email
 */
function generateEmailTemplate($title, $content, $actionButton = null, $footer = '') {
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>' . $title . '</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                margin: 0;
                padding: 0;
                background-color: #f7f7f7;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #ffffff;
            }
            .header {
                text-align: center;
                padding: 20px 0;
                border-bottom: 1px solid #eeeeee;
            }
            .logo {
                max-width: 150px;
                height: auto;
            }
            .content {
                padding: 30px 20px;
            }
            .footer {
                text-align: center;
                padding: 20px;
                font-size: 12px;
                color: #888888;
                border-top: 1px solid #eeeeee;
            }
            .button {
                display: inline-block;
                padding: 10px 20px;
                margin: 20px 0;
                background-color: #3b5998;
                color: #ffffff;
                text-decoration: none;
                border-radius: 4px;
                font-weight: bold;
            }
            h1 {
                color: #3b5998;
                font-size: 24px;
                margin-top: 0;
            }
            p {
                margin: 0 0 15px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <img src="https://www.example.com/assets/images/hopon_logo.jpg" alt="HopOn Logo" class="logo">
            </div>
            <div class="content">
                <h1>' . $title . '</h1>
                ' . $content . '
                ' . ($actionButton ? '<a href="' . $actionButton[1] . '" class="button">' . $actionButton[0] . '</a>' : '') . '
            </div>
            <div class="footer">
                ' . $footer . '
                <p>© ' . date('Y') . ' HopOn. Tous droits réservés.</p>
                <p>Vous recevez cet email car vous êtes inscrit sur HopOn.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

/**
 * Envoie une notification de confirmation de réservation
 * 
 * @param array $user Informations sur l'utilisateur
 * @param array $ride Informations sur le trajet
 * @return bool Succès ou échec de l'envoi
 */
function sendBookingConfirmation($user, $ride) {
    $title = "Confirmation de votre réservation de covoiturage";
    
    $content = "
    <p>Bonjour " . htmlspecialchars($user['prenom']) . ",</p>
    <p>Votre réservation pour le trajet de <strong>" . htmlspecialchars($ride['depart']) . "</strong> à <strong>" . htmlspecialchars($ride['destination']) . "</strong> le <strong>" . (new DateTime($ride['date_heure_depart']))->format('d/m/Y à H:i') . "</strong> a bien été confirmée.</p>
    <p>Détails du trajet :</p>
    <ul>
        <li>Conducteur : " . htmlspecialchars($ride['driver_firstname'] . ' ' . $ride['driver_lastname']) . "</li>
        <li>Prix : " . htmlspecialchars($ride['prix']) . " €</li>
        <li>Point de rendez-vous : " . (isset($ride['meetup_point']) ? htmlspecialchars($ride['meetup_point']) : 'À définir') . "</li>
    </ul>
    <p>Vous pouvez consulter les détails de votre réservation en cliquant sur le bouton ci-dessous.</p>";
    
    $actionButton = ["Voir ma réservation", "https://www.example.com/ride_details.php?id=" . $ride['id']];
    
    $footer = "<p>Pour toute question, n'hésitez pas à contacter le conducteur ou notre service client.</p>";
    
    $message = generateEmailTemplate($title, $content, $actionButton, $footer);
    
    return sendEmail($user['email'], $title, $message);
}

/**
 * Envoie un rappel de trajet à l'utilisateur
 * 
 * @param array $user Informations sur l'utilisateur
 * @param array $ride Informations sur le trajet
 * @return bool Succès ou échec de l'envoi
 */
function sendRideReminder($user, $ride) {
    $title = "Rappel : Votre trajet a lieu bientôt";
    
    $departureTime = new DateTime($ride['date_heure_depart']);
    $content = "
    <p>Bonjour " . htmlspecialchars($user['prenom']) . ",</p>
    <p>Nous vous rappelons que votre trajet de <strong>" . htmlspecialchars($ride['depart']) . "</strong> à <strong>" . htmlspecialchars($ride['destination']) . "</strong> est prévu le <strong>" . $departureTime->format('d/m/Y à H:i') . "</strong>.</p>";
    
    // Si le trajet est aujourd'hui, on ajoute des informations supplémentaires
    $today = new DateTime();
    if ($departureTime->format('Y-m-d') === $today->format('Y-m-d')) {
        $content .= "<p><strong>Votre trajet a lieu aujourd'hui !</strong></p>";
    }
    
    $content .= "
    <p>Détails du trajet :</p>
    <ul>
        <li>Conducteur : " . htmlspecialchars($ride['driver_firstname'] . ' ' . $ride['driver_lastname']) . "</li>
        <li>Point de rendez-vous : " . (isset($ride['meetup_point']) ? htmlspecialchars($ride['meetup_point']) : 'À définir') . "</li>
        <li>Numéro du conducteur : " . (isset($ride['driver_phone']) ? htmlspecialchars($ride['driver_phone']) : 'Non disponible') . "</li>
    </ul>
    <p>Passez un excellent trajet !</p>";
    
    $actionButton = ["Voir mon trajet", "https://www.example.com/ride_details.php?id=" . $ride['id']];
    
    $footer = "<p>N'oubliez pas de contacter le conducteur en cas de retard ou d'imprévu.</p>";
    
    $message = generateEmailTemplate($title, $content, $actionButton, $footer);
    
    // Envoi par email
    $emailSent = sendEmail($user['email'], $title, $message);
    
    // Si un numéro de téléphone est disponible, on envoie aussi un SMS
    $smsSent = true;
    if (!empty($user['phone'])) {
        $smsContent = "Rappel HopOn : Votre trajet de " . $ride['depart'] . " à " . $ride['destination'] . " a lieu le " . $departureTime->format('d/m/Y à H:i') . ". Point de rendez-vous : " . (isset($ride['meetup_point']) ? $ride['meetup_point'] : 'À définir');
        $smsSent = sendSMS($user['phone'], $smsContent);
    }
    
    return $emailSent && $smsSent;
}

/**
 * Envoie une notification de mise à jour du conducteur
 * 
 * @param array $user Informations sur l'utilisateur
 * @param array $ride Informations sur le trajet
 * @param string $updateType Type de mise à jour
 * @param string $updateMessage Message de mise à jour
 * @return bool Succès ou échec de l'envoi
 */
function sendDriverUpdateNotification($user, $ride, $updateType, $updateMessage) {
    $title = "Mise à jour de votre trajet";
    
    $content = "
    <p>Bonjour " . htmlspecialchars($user['prenom']) . ",</p>
    <p>Le conducteur de votre trajet de <strong>" . htmlspecialchars($ride['depart']) . "</strong> à <strong>" . htmlspecialchars($ride['destination']) . "</strong> le <strong>" . (new DateTime($ride['date_heure_depart']))->format('d/m/Y à H:i') . "</strong> a effectué une mise à jour.</p>
    <p><strong>Type de mise à jour :</strong> " . htmlspecialchars($updateType) . "</p>
    <p><strong>Message du conducteur :</strong></p>
    <p>" . nl2br(htmlspecialchars($updateMessage)) . "</p>
    <p>Vous pouvez consulter les détails de votre trajet en cliquant sur le bouton ci-dessous.</p>";
    
    $actionButton = ["Voir mon trajet", "https://www.example.com/ride_details.php?id=" . $ride['id']];
    
    $footer = "<p>Si cette mise à jour impacte votre participation, veuillez contacter le conducteur ou notre service client.</p>";
    
    $message = generateEmailTemplate($title, $content, $actionButton, $footer);
    
    // Envoi par email
    $emailSent = sendEmail($user['email'], $title, $message);
    
    // Si un numéro de téléphone est disponible et que la mise à jour est importante, on envoie aussi un SMS
    $smsSent = true;
    if (!empty($user['phone']) && in_array($updateType, ['changement horaire', 'annulation', 'changement point de rendez-vous'])) {
        $smsContent = "HopOn - Mise à jour importante : " . $updateType . " pour votre trajet du " . (new DateTime($ride['date_heure_depart']))->format('d/m/Y à H:i') . ". Consultez votre email pour plus d'informations.";
        $smsSent = sendSMS($user['phone'], $smsContent);
    }
    
    return $emailSent && $smsSent;
}

/**
 * Planifie l'envoi de rappels pour un trajet
 * 
 * Cette fonction doit être appelée lorsqu'une réservation est créée
 * ou lorsque les paramètres de notification sont modifiés
 * 
 * @param int $rideId ID du trajet
 * @param int $userId ID de l'utilisateur
 * @return bool Succès ou échec de la planification
 */
function scheduleRideReminders($rideId, $userId) {
    // Dans une implémentation réelle, vous utiliseriez un système de tâches programmées
    // comme Cron, Laravel Scheduler, ou un service tiers comme Heroku Scheduler
    
    // Pour les besoins de cette démonstration, nous allons simplement simuler la planification
    
    // 1. Récupérer les informations du trajet et de l'utilisateur
    global $db;
    
    // Requête pour obtenir les détails du trajet
    $rideQuery = "
    SELECT r.*, 
           u.id as driver_id, u.nom as driver_lastname, u.prenom as driver_firstname,
           u.telephone as driver_phone
    FROM trajets r
    JOIN utilisateurs u ON r.conducteur_id = u.id
    WHERE r.id = ?";
    
    $rideStmt = $db->prepare($rideQuery);
    $rideStmt->bind_param("i", $rideId);
    $rideStmt->execute();
    $rideResult = $rideStmt->get_result();
    
    if ($rideResult->num_rows === 0) {
        return false;
    }
    
    $ride = $rideResult->fetch_assoc();
    
    // Requête pour obtenir les informations de l'utilisateur
    $userQuery = "SELECT * FROM utilisateurs WHERE id = ?";
    $userStmt = $db->prepare($userQuery);
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        return false;
    }
    
    $user = $userResult->fetch_assoc();
    
    // 2. Récupérer les préférences de notification de l'utilisateur
    $prefQuery = "SELECT * FROM notifications_preferences WHERE utilisateur_id = ?";
    $prefStmt = $db->prepare($prefQuery);
    $prefStmt->bind_param("i", $userId);
    $prefStmt->execute();
    $prefResult = $prefStmt->get_result();
    
    $preferences = [];
    if ($prefResult->num_rows > 0) {
        $preferences = $prefResult->fetch_assoc();
    } else {
        // Préférences par défaut si non définies
        $preferences = [
            'ride_reminder' => 1,
            'ride_reminder_time' => 24, // 24 heures avant
        ];
    }
    
    // 3. Planifier les rappels en fonction des préférences
    if ($preferences['ride_reminder'] == 1) {
        $departureTime = new DateTime($ride['date_heure_depart']);
        $reminderTime = (int)$preferences['ride_reminder_time'];
        
        // Calculer la date du rappel
        $reminderDate = clone $departureTime;
        $reminderDate->sub(new DateInterval("PT{$reminderTime}H"));
        
        // Dans une application réelle, vous enregistreriez cette tâche dans une table de tâches programmées
        // ou utiliseriez un système de files d'attente
        
        // Pour notre démo, si le rappel doit être envoyé maintenant ou dans le passé, on l'envoie immédiatement
        $now = new DateTime();
        if ($reminderDate <= $now) {
            return sendRideReminder($user, $ride);
        }
        
        // Sinon, on simule l'enregistrement de la tâche
        return true;
    }
    
    return true;
}
?>