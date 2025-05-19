<?php
// FILE: app/views/trip/create.php (Texte en Français)
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
$userVehicles = $GLOBALS['userVehicles'] ?? [];
$formData = $GLOBALS['form_data'] ?? []; // For repopulating on error

// $GLOBALS['pageName'] should be 'create_trip' (or similar) set by TripController::showCreateTripForm()
require_once __DIR__ . '/../partials/header.php';
?>
<main class="container create-trip-container">
    <div class="create-trip-form card">
        <h1>Proposer un Nouveau Trajet</h1>

        <form action="<?= $base_url ?>/trip/create" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="departure_location">Lieu de départ</label>
                    <input type="text" id="departure_location" name="departure_location" value="<?= htmlspecialchars($formData['departure_location'] ?? '') ?>" placeholder="Ex : Paris, Tour Eiffel" required>
                </div>
                <div class="form-group">
                    <label for="arrival_location">Lieu d'arrivée</label>
                    <input type="text" id="arrival_location" name="arrival_location" value="<?= htmlspecialchars($formData['arrival_location'] ?? '') ?>" placeholder="Ex : Lyon, Gare Part-Dieu" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="departure_date">Date de départ</label>
                    <input type="date" id="departure_date" name="departure_date" value="<?= htmlspecialchars($formData['departure_date'] ?? '') ?>" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label for="departure_time">Heure de départ</label>
                    <input type="time" id="departure_time" name="departure_time" value="<?= htmlspecialchars($formData['departure_time'] ?? '') ?>" required>
                </div>
            </div>

             <div class="form-row">
                <div class="form-group">
                    <label for="arrival_date">Date d'arrivée estimée (Optionnel)</label>
                    <input type="date" id="arrival_date" name="arrival_date" value="<?= htmlspecialchars($formData['arrival_date'] ?? '') ?>" min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label for="arrival_time">Heure d'arrivée estimée (Optionnel)</label>
                    <input type="time" id="arrival_time" name="arrival_time" value="<?= htmlspecialchars($formData['arrival_time'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="price">Prix par place (€)</label>
                    <input type="number" id="price" name="price" min="0" step="0.50" value="<?= htmlspecialchars($formData['price'] ?? '10.00') ?>" required placeholder="Ex : 25,50">
                </div>
                <div class="form-group">
                    <label for="seats_offered">Places à proposer</label>
                    <input type="number" id="seats_offered" name="seats_offered" min="1" max="8" value="<?= htmlspecialchars($formData['seats_offered'] ?? '3') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="vehicle_id">Véhicule (Optionnel)</label>
                <select name="vehicle_id" id="vehicle_id">
                    <option value="">Sélectionnez votre véhicule (optionnel)</option>
                    <?php if (!empty($userVehicles)): ?>
                        <?php foreach ($userVehicles as $vehicle): ?>
                            <option value="<?= $vehicle['id'] ?>" <?= (isset($formData['vehicle_id']) && $formData['vehicle_id'] == $vehicle['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model'] . ' (' . ($vehicle['license_plate'] ?? 'N/P') . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <small>Si votre véhicule n'est pas listé, vous pouvez l'<a href="<?= $base_url ?>/profile/vehicles">ajouter depuis votre profil</a>.</small>
            </div>

            <div class="form-group">
                <label for="trip_details">Détails du trajet (Optionnel)</label>
                <textarea id="trip_details" name="trip_details" rows="4" placeholder="Ex : Point de RDV précis, politique bagages, flexibilité détours..."><?= htmlspecialchars($formData['trip_details'] ?? '') ?></textarea>
            </div>

             <div class="form-group checkbox-group">
                 <input type="checkbox" id="allow_instant_booking" name="allow_instant_booking" value="1" <?= isset($formData['allow_instant_booking']) && $formData['allow_instant_booking'] == '1' ? 'checked' : '' ?>>
                 <label for="allow_instant_booking">Autoriser la réservation instantanée (les passagers réservent sans votre confirmation manuelle)</label>
             </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Publier le Trajet</button>
                <a href="<?= $base_url ?>/trips" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>