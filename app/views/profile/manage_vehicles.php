<?php
// FILE: app/views/profile/manage_vehicles.php (Texte en Français)
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
$vehicles = $GLOBALS['vehicles'] ?? [];
// $GLOBALS['pageName'] should be 'manage_vehicles' set by ProfileController::showVehicles()
require_once __DIR__ . '/../partials/header.php';
?>
<main class="container manage-vehicles-container">
    <div class="manage-vehicles-header">
        <h1>Gérer Mes Véhicules</h1>
        <a href="<?= $base_url ?>/profile/add-vehicle" class="btn btn-primary">Ajouter un Nouveau Véhicule</a>
    </div>

    <?php if (empty($vehicles)): ?>
        <p class="no-vehicles-message card">Vous n'avez pas encore enregistré de véhicule.</p>
    <?php else: ?>
        <ul class="vehicle-list">
            <?php foreach ($vehicles as $vehicle): ?>
                <li class="vehicle-item">
                    <i class="fas fa-car vehicle-icon"></i>
                    <div class="vehicle-details">
                        <h3>
                            <?= htmlspecialchars($vehicle['make']) ?> <?= htmlspecialchars($vehicle['model']) ?>
                            <?php if ($vehicle['is_default']): ?>
                                <span class="default-badge">Par Défaut</span>
                            <?php endif; ?>
                        </h3>
                        <p>Couleur : <?= htmlspecialchars($vehicle['color'] ?? 'N/P') ?></p>
                        <p>Année : <?= htmlspecialchars($vehicle['year'] ?? 'N/P') ?></p>
                        <p>Places offertes : <?= htmlspecialchars($vehicle['seats_available']) ?></p>
                        <p>Type : <?= htmlspecialchars($vehicle['type'] ?? 'N/P') ?></p>
                        <p>Immatriculation : <?= htmlspecialchars($vehicle['license_plate'] ?? 'N/P') ?></p>
                    </div>
                    <div class="vehicle-actions">
                        <?php if (empty($vehicle['is_default'])): // Check if explicitly not default or null ?>
                            <form action="<?= $base_url ?>/profile/set-default-vehicle/<?= $vehicle['id'] ?>" method="POST" style="display:inline;">
                                <button type="submit" class="btn btn-secondary btn-sm">Définir par défaut</button>
                            </form>
                        <?php endif; ?>
                        <a href="<?= $base_url ?>/profile/edit-vehicle/<?= $vehicle['id'] ?>" class="btn btn-secondary btn-sm">Modifier</a>
                        <form action="<?= $base_url ?>/profile/delete-vehicle/<?= $vehicle['id'] ?>" method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce véhicule ? Cette action est irréversible.');">
                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <div style="margin-top: 30px; text-align: center;">
         <a href="<?= $base_url ?>/profile" class="btn btn-outline">← Retour au Profil</a>
    </div>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>