<?php
if (!defined('ABSPATH')) {
    exit;
}
$reviews = (new CTRW_Model())->get_reviews('approved');
$settings = get_option('customer_reviews_settings');

// Calculate average rating and rating counts
$average_rating = 0;
$total_reviews = count($reviews);
$rating_counts = array(0, 0, 0, 0, 0); // For 1-5 stars
if ($total_reviews > 0) {
    $total_rating = 0;
    foreach ($reviews as $review) {
        $rating = (int)$review->rating;
        $total_rating += $rating;
        if ($rating >= 1 && $rating <= 5) {
            $rating_counts[$rating - 1]++;
        }
    }
    $average_rating = $total_rating / $total_reviews;
}
?>

<div class="review-widget-container">
    <div class="review-header">
        Customer Reviews Summary
    </div>
    <div class="review-content">
        <div class="rating-summary-block">
            <div class="average-rating-score">
                <?= number_format($average_rating, 1) ?><span class="max-score">/5</span>
            </div>
            <div class="star-display">
                <?php
                $full_stars = floor($average_rating);
                $has_half_star = ($average_rating - $full_stars) >= 0.5;
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $full_stars) {
                        echo '<span class="star-filled">★</span>';
                    } elseif ($i == $full_stars + 1 && $has_half_star) {
                        echo '<span class="star-half">★</span>';
                    } else {
                        echo '<span class="star-empty">★</span>';
                    }
                }
                ?>
            </div>
            <div class="total-reviews-text">
                Based on <?= $total_reviews ?> <?= ($total_reviews === 1) ? 'review' : 'reviews' ?>
            </div>
        </div>
        <div class="rating-breakdown-block">
            <?php for ($i = 5; $i >= 1; $i--) : 
                $count = $rating_counts[$i - 1];
                $percentage = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
            ?>
                <div class="rating-bar-row">
                    <div class="star-label">
                        <?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?>
                    </div>
                    <div class="bar-background">
                        <div class="bar-foreground" style="width: <?= $percentage ?>%;"></div>
                    </div>
                    <div class="review-count"><?= $count ?></div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>