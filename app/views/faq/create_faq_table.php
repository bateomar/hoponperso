<?php
// Connexion à la base de données
require_once 'includes/db_connect.php';
$db = connectDB();

if (!$db) {
    die("Erreur de connexion à la base de données");
}

try {
    // Créer la table faqs
    $db->exec("CREATE TABLE IF NOT EXISTS faqs (
        id INT PRIMARY KEY AUTO_INCREMENT,
        category VARCHAR(50) NOT NULL,
        question TEXT NOT NULL,
        answer TEXT NOT NULL,
        ordre INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "Table 'faqs' créée avec succès.<br>";
    
    // Vérifier si la table contient déjà des données
    $stmt = $db->query("SELECT COUNT(*) FROM faqs");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        // Insérer des données d'exemple
        
        // Catégorie Général
        $db->exec("INSERT INTO faqs (category, question, answer, ordre) VALUES 
        ('general', 'Qu''est-ce que HopOn ?', 'HopOn est une plateforme de covoiturage qui met en relation des conducteurs ayant des places disponibles dans leur véhicule avec des passagers allant dans la même direction. Notre mission est de rendre les déplacements plus économiques, écologiques et conviviaux.', 1),
        ('general', 'Comment fonctionne HopOn ?', 'C''est simple ! Les conducteurs publient leurs trajets en indiquant leur itinéraire, la date, l''heure et le prix. Les passagers peuvent rechercher un trajet correspondant à leurs besoins, réserver une place et payer en ligne. Le jour J, ils se retrouvent au point de rendez-vous convenu.', 2),
        ('general', 'HopOn est-il disponible partout en France ?', 'Oui, HopOn est disponible sur l''ensemble du territoire français, y compris les DOM-TOM. Notre plateforme est particulièrement active dans les grandes agglomérations et pour les trajets interurbains.', 3)");
        
        // Catégorie Réservation
        $db->exec("INSERT INTO faqs (category, question, answer, ordre) VALUES 
        ('reservation', 'Comment réserver un trajet ?', 'Pour réserver un trajet, recherchez d''abord votre itinéraire dans la barre de recherche. Parcourez les résultats, comparez les prix et les horaires, puis cliquez sur \"Réserver\". Confirmez vos informations personnelles et procédez au paiement pour finaliser votre réservation.', 1),
        ('reservation', 'Puis-je annuler ma réservation ?', 'Oui, vous pouvez annuler votre réservation jusqu''à 24 heures avant le départ. Les conditions de remboursement dépendent du délai d''annulation : remboursement intégral jusqu''à 72h avant le départ, 50% entre 72h et 24h, et aucun remboursement pour les annulations de dernière minute.', 2),
        ('reservation', 'Comment contacter le conducteur avant le trajet ?', 'Une fois votre réservation confirmée, vous pouvez accéder à la messagerie interne de HopOn pour communiquer avec le conducteur. C''est l''occasion de préciser le lieu exact de rendez-vous ou toute information utile pour faciliter la rencontre.', 3)");
        
        // Catégorie Paiement
        $db->exec("INSERT INTO faqs (category, question, answer, ordre) VALUES 
        ('paiement', 'Quels moyens de paiement sont acceptés ?', 'HopOn accepte les cartes bancaires (Visa, MasterCard), PayPal, et les paiements via Apple Pay et Google Pay. Tous les paiements sont sécurisés par notre partenaire de confiance pour garantir la protection de vos données.', 1),
        ('paiement', 'Quand l''argent est-il versé au conducteur ?', 'Le conducteur reçoit l''argent 24 heures après la réalisation du trajet. Ce délai permet de s''assurer que le trajet s''est bien déroulé et que tous les passagers sont satisfaits du service.', 2),
        ('paiement', 'Y a-t-il des frais de service ?', 'Oui, HopOn prélève des frais de service de 10% sur chaque trajet pour assurer le fonctionnement de la plateforme, l''assistance client et les garanties de remboursement. Ces frais sont transparents et toujours indiqués avant la finalisation de la réservation.', 3)");
        
        // Catégorie Sécurité
        $db->exec("INSERT INTO faqs (category, question, answer, ordre) VALUES 
        ('securite', 'Comment HopOn assure-t-il ma sécurité ?', 'La sécurité est notre priorité. Tous les utilisateurs doivent vérifier leur identité et leurs coordonnées. Les profils comportent des évaluations et avis laissés par d''autres utilisateurs. Nous proposons également un système de partage d''itinéraire en temps réel avec vos proches pour plus de tranquillité.', 1),
        ('securite', 'Comment fonctionne le système d''évaluation ?', 'Après chaque trajet, conducteurs et passagers peuvent s''évaluer mutuellement avec une note de 1 à 5 étoiles et laisser un commentaire. Ces évaluations contribuent à créer une communauté de confiance et permettent aux utilisateurs de faire des choix éclairés.', 2),
        ('securite', 'Que faire en cas de problème pendant un trajet ?', 'En cas de problème, vous pouvez contacter notre service d''assistance 24/7 via l''application ou le site web. Pour les urgences, utilisez le bouton SOS dans l''application qui vous connectera directement avec les services d''urgence appropriés tout en partageant votre localisation.', 3)");
        
        echo "Données d'exemple insérées avec succès.<br>";
    } else {
        echo "La table 'faqs' contient déjà des données.<br>";
    }
    
    echo "La configuration de la FAQ est terminée.<br>";
    echo "<a href='faq_direct.php'>Accéder à la FAQ</a>";
    
} catch (PDOException $e) {
    die("Erreur lors de la création de la table : " . $e->getMessage());
}
?>