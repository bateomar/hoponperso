<?php
// FILE: app/views/profile/view_ratings.php
$base_url = defined('BASE_PATH') ? rtrim(BASE_PATH, '/') : '';
$ratings = $GLOBALS['ratings'] ?? [];
$averageRating = $GLOBALS['averageRating'] ?? 0;
$ratingsCount = $GLOBALS['ratingsCount'] ?? 0;

require_once __DIR__ . '/../partials/header.php';
?>
<main class="container view-ratings-container">
    <h1>My Ratings Received</h1>

    <?php if ($ratingsCount > 0): ?>
         <div class="overall-rating-summary card">
             <div class="rating-stars">
                 <i class="fas fa-star"></i> <?= number_format($averageRating, 1) ?>/5
             </div>
             <div class="rating-count">
                 Based on <?= $ratingsCount ?> rating(s)
             </div>
         </div>
    <?php endif; ?>

    <?php if (empty($ratings)): ?>
        <p class="no-ratings-message card">You have not received any ratings yet.</p>
    <?php else: ?>
        <ul class="ratings-list">
            <?php foreach ($ratings as $rating): ?>
                <li class="rating-item">
                    <div class="rating-header">
                        <div class="rater-info">
                            <img src="<?= $base_url ?>/images/default_avatar.png" alt="Rater avatar" class="rater-avatar"> {/* Replace with actual rater avatar if available */}
                            <span class="rater-name"><?= htmlspecialchars($rating['rater_first_name'] ?? 'User') ?></span>
                        </div>
                        <div class="rating-details">
                            <span class="rating-score">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= $rating['score'] ? 'rated' : 'unrated' ?>"></i>
                                <?php endfor; ?>
                                (<?= htmlspecialchars($rating['score']) ?>/5)
                            </span>
                            <span class="rating-date"><?= date('F j, Y', strtotime($rating['created_at'])) ?></span>
                        </div>
                    </div>
                    <?php if (!empty($rating['comment'])): ?>
                        <p class="rating-comment"><?= nl2br(htmlspecialchars($rating['comment'])) ?></p>
                    <?php endif; ?>
                     <?php if (!empty($rating['trip_id'])): ?>
                        <small class="trip-link">Related Trip: <a href="<?= $base_url ?>/trip/<?= $rating['trip_id'] ?>">View Trip</a></small>
                     <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php // Add pagination controls here if implementing pagination ?>
    <?php endif; ?>
     <div style="margin-top: 20px;">
         <a href="<?= $base_url ?>/profile" class="btn btn-outline">← Back to Profile</a>
    </div>
</main>
<?php require_once __DIR__ . '/../partials/footer.php'; ?>