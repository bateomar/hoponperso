<?php
// FILE: app/views/profile/add_edit_vehicle.php (Texte en Français)
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
$action = $GLOBALS['form_action'] ?? 'add'; // 'add' ou 'edit'
$vehicle = $GLOBALS['vehicle'] ?? null;    // Données du véhicule existant pour l'édition
$formData = $GLOBALS['form_data'] ?? $vehicle; // Pour la pré-remplissage, priorise les données du formulaire en cas d'erreur

$formActionUrl = ($action === 'edit' && isset($vehicle['id']))
                    ? $base_url . '/profile/edit-vehicle/' . $vehicle['id']
                    : $base_url . '/profile/add-vehicle';

$pageTitle = ($action === 'edit') ? 'Modifier le Véhicule' : 'Ajouter un Nouveau Véhicule';

// $GLOBALS['pageName'] devrait être 'add_edit_vehicle' (défini par ProfileController)
require_once __DIR__ . '/../partials/header.php';
?>
<main class="container vehicle-form-container">
    <div class="vehicle-form card">
        <h2><?= $pageTitle ?></h2>

        <form action="<?= $formActionUrl ?>" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="make">Marque</label>
                    <input type="text" id="make" name="make" value="<?= htmlspecialchars($formData['make'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="model">Modèle</label>
                    <input type="text" id="model" name="model" value="<?= htmlspecialchars($formData['model'] ?? '') ?>" required>
                </div>
            </div>

             <div class="form-row">
                <div class="form-group">
                    <label for="color">Couleur</label>
                    <input type="text" id="color" name="color" value="<?= htmlspecialchars($formData['color'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="year">Année</label>
                    <input type="number" id="year" name="year" min="1950" max="<?= date('Y') + 1 ?>" step="1" value="<?= htmlspecialchars($formData['year'] ?? '') ?>" placeholder="Ex : 2020">
                </div>
             </div>

             <div class="form-row">
                <div class="form-group">
                    <label for="type">Type (Optionnel)</label>
                    <input type="text" id="type" name="type" value="<?= htmlspecialchars($formData['type'] ?? '') ?>" placeholder="Ex : Berline, SUV, Citadine">
                </div>
                 <div class="form-group">
                    <label for="license_plate">Plaque d'immatriculation (Optionnel)</label>
                    <input type="text" id="license_plate" name="license_plate" value="<?= htmlspecialchars($formData['license_plate'] ?? '') ?>" placeholder="Ex : AA-123-BB">
                </div>
             </div>

             <div class="form-group">
                <label for="seats_available">Places disponibles pour les passagers</label>
                <input type="number" id="seats_available" name="seats_available" min="1" max="8" step="1" value="<?= htmlspecialchars($formData['seats_available'] ?? '3') ?>" required>
                <small>Nombre de places que vous proposez, sans compter la vôtre.</small>
             </div>

              <div class="form-group checkbox-group">
                 <input type="checkbox" id="is_default" name="is_default" value="1" <?= (isset($formData['is_default']) && $formData['is_default'] == '1') || (isset($vehicle['is_default']) && $vehicle['is_default'] && !isset($formData['is_default'])) ? 'checked' : '' ?>>
                 <label for="is_default">Définir comme véhicule par défaut pour proposer des trajets</label>
              </div>


            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= ($action === 'edit') ? 'Mettre à jour le Véhicule' : 'Ajouter le Véhicule' ?></button>
                <a href="<?= $base_url ?>/profile/vehicles" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>