<?php
/**
 * Fonctions pour le partage d'itinéraire
 */

/**
 * Récupère tous les contacts de confiance d'un utilisateur
 * 
 * @param int $utilisateur_id ID de l'utilisateur
 * @return array Liste des contacts
 */
function getContactsConfiance($utilisateur_id) {
    $db = connectDB();
    
    $contacts = [];
    
    if (!$db) {
        return $contacts;
    }
    
    $query = "SELECT * FROM contacts_confiance WHERE utilisateur_id = ? ORDER BY nom ASC";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $stmt->execute([$utilisateur_id]);
        $contacts = $stmt->fetchAll();
    }
    
    return $contacts;
}

/**
 * Ajoute un nouveau contact de confiance
 * 
 * @param int $utilisateur_id ID de l'utilisateur
 * @param string $nom Nom du contact
 * @param string $telephone Numéro de téléphone du contact
 * @param string $email Email du contact
 * @param string $relation Relation avec le contact
 * @return int|bool ID du contact créé ou false en cas d'erreur
 */
function ajouterContactConfiance($utilisateur_id, $nom, $telephone, $email, $relation) {
    $db = connectDB();
    
    if (!$db) {
        return false;
    }
    
    $query = "INSERT INTO contacts_confiance (utilisateur_id, nom, telephone, email, relation) VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $success = $stmt->execute([$utilisateur_id, $nom, $telephone, $email, $relation]);
        
        if ($success) {
            $contact_id = $db->lastInsertId();
            return $contact_id;
        }
    }
    
    return false;
}

/**
 * Met à jour un contact de confiance
 * 
 * @param int $contact_id ID du contact
 * @param string $nom Nom du contact
 * @param string $telephone Numéro de téléphone du contact
 * @param string $email Email du contact
 * @param string $relation Relation avec le contact
 * @return bool Succès ou échec
 */
function modifierContactConfiance($contact_id, $nom, $telephone, $email, $relation) {
    $db = connectDB();
    
    if (!$db) {
        return false;
    }
    
    $query = "UPDATE contacts_confiance SET nom = ?, telephone = ?, email = ?, relation = ? WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $success = $stmt->execute([$nom, $telephone, $email, $relation, $contact_id]);
        return $success;
    }
    
    return false;
}

/**
 * Supprime un contact de confiance
 * 
 * @param int $contact_id ID du contact
 * @param int $utilisateur_id ID de l'utilisateur (pour vérification)
 * @return bool Succès ou échec
 */
function supprimerContactConfiance($contact_id, $utilisateur_id) {
    $db = connectDB();
    
    if (!$db) {
        return false;
    }
    
    $query = "DELETE FROM contacts_confiance WHERE id = ? AND utilisateur_id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $success = $stmt->execute([$contact_id, $utilisateur_id]);
        return $success;
    }
    
    return false;
}

/**
 * Crée un nouveau suivi de trajet partagé
 * 
 * @param int $trajet_id ID du trajet
 * @param int $utilisateur_id ID de l'utilisateur
 * @param int $duree_expiration Durée de validité en heures
 * @return array|bool Informations du suivi créé ou false en cas d'erreur
 */
function creerTrajetPartage($trajet_id, $utilisateur_id, $duree_expiration = 48) {
    $db = connectDB();
    
    if (!$db) {
        return false;
    }
    
    // Générer un code de suivi unique
    $code_suivi = genererCodeSuivi();
    
    // Calculer la date d'expiration
    $date_expiration = date('Y-m-d H:i:s', strtotime("+{$duree_expiration} hours"));
    
    $query = "INSERT INTO trajets_partages (trajet_id, utilisateur_id, code_suivi, date_expiration) 
              VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $success = $stmt->execute([$trajet_id, $utilisateur_id, $code_suivi, $date_expiration]);
        
        if ($success) {
            $partage_id = $db->lastInsertId();
            
            return [
                'id' => $partage_id,
                'trajet_id' => $trajet_id,
                'utilisateur_id' => $utilisateur_id,
                'code_suivi' => $code_suivi,
                'date_expiration' => $date_expiration
            ];
        }
    }
    
    return false;
}

/**
 * Partage un trajet avec des contacts
 * 
 * @param int $trajet_partage_id ID du trajet partagé
 * @param array $contact_ids IDs des contacts
 * @param string $methode_notification Méthode de notification (email, sms, both)
 * @param string $message_personnalise Message personnalisé
 * @return bool Succès ou échec
 */
function partagerTrajetAvecContacts($trajet_partage_id, $contact_ids, $methode_notification, $message_personnalise = '') {
    $db = connectDB();
    
    if (!$db || empty($contact_ids)) {
        return false;
    }
    
    $query = "INSERT INTO partage_contacts (trajet_partage_id, contact_id, methode_notification, message_personnalise) 
              VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $success = true;
        
        foreach ($contact_ids as $contact_id) {
            $result = $stmt->execute([$trajet_partage_id, $contact_id, $methode_notification, $message_personnalise]);
            
            if (!$result) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    return false;
}

/**
 * Enregistre une position pour le suivi en temps réel
 * 
 * @param int $trajet_partage_id ID du trajet partagé
 * @param float $latitude Latitude
 * @param float $longitude Longitude
 * @param int $precision_metres Précision en mètres
 * @param float $vitesse Vitesse en km/h
 * @return bool Succès ou échec
 */
function enregistrerPosition($trajet_partage_id, $latitude, $longitude, $precision_metres = null, $vitesse = null) {
    $db = connectDB();
    
    if (!$db) {
        return false;
    }
    
    $query = "INSERT INTO positions_suivi (trajet_partage_id, latitude, longitude, precision_metres, vitesse) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $success = $stmt->execute([$trajet_partage_id, $latitude, $longitude, $precision_metres, $vitesse]);
        return $success;
    }
    
    return false;
}

/**
 * Récupère les dernières positions d'un trajet partagé
 * 
 * @param int $trajet_partage_id ID du trajet partagé
 * @param int $limit Nombre maximum de positions à récupérer
 * @return array Liste des positions
 */
function getDernieresPositions($trajet_partage_id, $limit = 10) {
    $db = connectDB();
    
    $positions = [];
    
    if (!$db) {
        return $positions;
    }
    
    $query = "SELECT * FROM positions_suivi 
              WHERE trajet_partage_id = ? 
              ORDER BY timestamp DESC 
              LIMIT ?";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $stmt->execute([$trajet_partage_id, $limit]);
        $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return $positions;
}

/**
 * Récupère les détails d'un trajet partagé par son code de suivi
 * 
 * @param string $code_suivi Code de suivi
 * @return array|bool Détails du trajet ou false si non trouvé
 */
function getTrajetPartageParCode($code_suivi) {
    $db = connectDB();
    
    if (!$db) {
        return false;
    }
    
    $query = "SELECT tp.*, t.*, u.nom as passager_nom, u.prenom as passager_prenom,
              d.nom as conducteur_nom, d.prenom as conducteur_prenom  
              FROM trajets_partages tp
              JOIN trajets t ON tp.trajet_id = t.id
              JOIN utilisateurs u ON tp.utilisateur_id = u.id
              JOIN utilisateurs d ON t.conducteur_id = d.id
              WHERE tp.code_suivi = ? AND tp.statut = 'actif' AND tp.date_expiration > NOW()";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $stmt->execute([$code_suivi]);
        $trajet = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($trajet) {
            return $trajet;
        }
    }
    
    return false;
}

/**
 * Vérifie si un utilisateur a partagé un trajet
 * 
 * @param int $trajet_id ID du trajet
 * @param int $utilisateur_id ID de l'utilisateur
 * @return array|bool Détails du partage ou false si non trouvé
 */
function verifierTrajetPartage($trajet_id, $utilisateur_id) {
    $db = connectDB();
    
    if (!$db) {
        return false;
    }
    
    $query = "SELECT * FROM trajets_partages 
              WHERE trajet_id = ? AND utilisateur_id = ? AND statut = 'actif'";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $stmt->execute([$trajet_id, $utilisateur_id]);
        $partage = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($partage) {
            return $partage;
        }
    }
    
    return false;
}

/**
 * Récupère les contacts avec qui un trajet est partagé
 * 
 * @param int $trajet_partage_id ID du trajet partagé
 * @return array Liste des contacts
 */
function getContactsTrajetPartage($trajet_partage_id) {
    $db = connectDB();
    
    $contacts = [];
    
    if (!$db) {
        return $contacts;
    }
    
    $query = "SELECT pc.*, cc.* 
              FROM partage_contacts pc
              JOIN contacts_confiance cc ON pc.contact_id = cc.id
              WHERE pc.trajet_partage_id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $stmt->execute([$trajet_partage_id]);
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    return $contacts;
}

/**
 * Termine le suivi d'un trajet partagé
 * 
 * @param int $trajet_partage_id ID du trajet partagé
 * @return bool Succès ou échec
 */
function terminerSuiviTrajet($trajet_partage_id) {
    $db = connectDB();
    
    if (!$db) {
        return false;
    }
    
    $query = "UPDATE trajets_partages SET statut = 'termine' WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $success = $stmt->execute([$trajet_partage_id]);
        return $success;
    }
    
    return false;
}

/**
 * Annule le suivi d'un trajet partagé
 * 
 * @param int $trajet_partage_id ID du trajet partagé
 * @return bool Succès ou échec
 */
function annulerSuiviTrajet($trajet_partage_id) {
    $db = connectDB();
    
    if (!$db) {
        return false;
    }
    
    $query = "UPDATE trajets_partages SET statut = 'annule' WHERE id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $success = $stmt->execute([$trajet_partage_id]);
        return $success;
    }
    
    return false;
}

/**
 * Génère un code de suivi unique
 * 
 * @return string Code de suivi
 */
function genererCodeSuivi() {
    $db = connectDB();
    
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $code = '';
    
    // Préfixe TRK + 8 caractères aléatoires
    $code = 'TRK';
    
    for ($i = 0; $i < 8; $i++) {
        $code .= $chars[rand(0, strlen($chars) - 1)];
    }
    
    // Vérifier si le code existe déjà
    if ($db) {
        $query = "SELECT id FROM trajets_partages WHERE code_suivi = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt) {
            $stmt->execute([$code]);
            $existe = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Si le code existe déjà, en générer un nouveau
            if ($existe) {
                return genererCodeSuivi(); // Récursivité pour générer un nouveau code
            }
        }
    }
    
    return $code;
}

/**
 * Récupère les trajets partagés d'un utilisateur
 * 
 * @param int $utilisateur_id ID de l'utilisateur
 * @return array Liste des trajets partagés
 */
function getTrajetsPartagesUtilisateur($utilisateur_id) {
    $db = connectDB();
    
    $trajets = [];
    
    if (!$db) {
        return $trajets;
    }
    
    $query = "SELECT tp.*, t.*, 
              CONCAT(u.prenom, ' ', u.nom) as conducteur_nom,
              (SELECT COUNT(*) FROM partage_contacts pc WHERE pc.trajet_partage_id = tp.id) as nb_contacts
              FROM trajets_partages tp
              JOIN trajets t ON tp.trajet_id = t.id
              JOIN utilisateurs u ON t.conducteur_id = u.id
              WHERE tp.utilisateur_id = ?
              ORDER BY t.date_heure_depart DESC";
    $stmt = $db->prepare($query);
    
    if ($stmt) {
        $stmt->execute([$utilisateur_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($rows as $row) {
            // Récupérer les contacts avec qui le trajet est partagé
            $row['contacts'] = getContactsTrajetPartage($row['id']);
            $trajets[] = $row;
        }
    }
    
    return $trajets;
}