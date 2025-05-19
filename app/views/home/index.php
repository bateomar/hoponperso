<?php
// FILE: app/views/home/index.php

$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
require_once __DIR__ . '/../partials/header.php';
?>

<!-- Section avec image de fond -->
<div class="search-section" style="background-image: url('/images/backdrop_recherche.png');">
    <div class="search-bar-container">
        <form action="<?= $base_url ?>/trips" method="get" class="search-bar">
            <div class="search-field">
                <label for="departure"><i class="fas fa-map-marker-alt"></i> Départ</label>
                <input type="text" name="departure_location" placeholder="e.g., Paris" required>
            </div>
            <div class="search-field">
                <label for="arrival"><i class="fas fa-map-marker-alt"></i> Destination</label>
                <input type="text" name="arrival_location" placeholder="e.g., Lyon" required>
            </div>
            <div class="search-field">
                <label for="date"><i class="fas fa-calendar-alt"></i> Date</label>
                <input type="date" name="departure_date">
            </div>
            <button type="submit" class="search-button">Ne pas appuyer</button>
        </form>
    </div>
</div>


<!-- Popular Routes Section -->
<div class="trajets-popular-overview container">
    <h2>Trajets populaires du moment</h2>
    <div class="trajets-wrapper">
        <?php
        $popularRoutes = $GLOBALS['popular_routes'] ?? [];

        if (!empty($popularRoutes)):
            foreach ($popularRoutes as $route): // Assuming $route comes from TripModel::getPopularRoutesDetails
                // Calculate remaining seats
                $seatsTrulyAvailable = ($route['seats_offered'] ?? 0) - ($route['seats_booked'] ?? 0);
            ?>
                <a href="<?= $base_url ?>/trip/<?= $route['id'] ?>" class="trajet-card-link">
                    <div class="trajet-card"> 
                        <div class="card-image-placeholder">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h4><?= htmlspecialchars($route['departure_location']) ?> <i class="fas fa-arrow-right"></i> <?= htmlspecialchars($route['arrival_location']) ?></h4>
                        <p class="trajet-date-time">
                            <i class="fas fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($route['departure_time'])) ?>
                            à <?= date('H:i', strtotime($route['departure_time'])) ?>
                        </p>
                        <div class="trajet-meta">
                            <span class="trajet-price"><?= number_format($route['price'], 2, ',', ' ') ?> €</span>
                            <span class="trajet-seats">
                                <i class="fas fa-chair"></i>
                                <?= htmlspecialchars($seatsTrulyAvailable) ?> place<?= $seatsTrulyAvailable > 1 ? 's' : '' ?>
                            </span>
                        </div>
                    </div>
                </a>
            <?php endforeach;
        else: ?>
            <p class="no-popular-trips">Aucun trajet populaire disponible pour le moment. <a href="<?= $base_url ?>/trips">Voir tous les trajets</a>.</p>
        <?php endif; ?>
    </div>
    <?php if (!empty($popularRoutes)): ?>
        <div class="view-all-trips-link">
            <a href="<?= $base_url ?>/trips" class="btn btn-secondary">Voir tous les trajets disponibles</a>
        </div>
    <?php endif; ?>
</div>

<!-- Intro Section -->
<div class="intro-section">
    <div class="intro-text">
        <h2>Une manière simple, rapide, économique et amusante de voyager ensemble</h2>
        <p>
            HopOn connecte les personnes qui ont besoin de se déplacer avec des conducteurs ayant des places libres
        </p>
        <p>
            Covoiturage de confiance, entièrement assuré
        </p>
    </div>
</div>

<!-- Image Carousel -->
<div class="carousel-container">
    <div class="carousel">
        <?php for ($i = 1; $i <= 10; $i++): ?>
            <?php // Use $base_url for image paths ?>
            <img src="/images/city<?= $i ?>.jpg" alt="Ville <?= $i ?>" class="<?= $i === 1 ? 'active' : '' ?>">
        <?php endfor; ?>
    </div>
    <button class="prev">❮</button>
    <button class="next">❯</button>
</div>

<!-- Motivation Section -->
<div class="motivation-section">
    <h3>Pourquoi aimeriez-vous voyager avec HopOn ?</h3>
</div>

<!-- Info Cards Section -->
<div class="info-section">
    <div class="info-card">
        <h3>Des trajets flexibles<br>et abordables</h3>
        <p>Que vous voyagiez pour le travail ou le plaisir, trouvez le covoiturage parfait en quelques clics, à des prix compétitifs.</p>
    </div>
    <div class="info-card">
        <h3>Un voyage en toute<br>sérénité</h3>
        <p>Avec HopOn, voyagez l’esprit tranquille ! Profils vérifiés, avis authentiques et partenaires de confiance : nous mettons tout en œuvre pour garantir votre sécurité et votre confort.</p>
    </div>
    <div class="info-card">
        <h3>Trouvez votre trajet<br>en un instant</h3>
        <p>Grâce à notre plateforme intuitive, recherchez, comparez et réservez facilement votre trajet idéal. Il n’a jamais été aussi simple de voyager à moindre coût.</p>
    </div>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>

<script src="<?= $base_url ?>/script/carousel.js" defer></script>

<?php // Body and HTML closing tags are in footer.php ?>