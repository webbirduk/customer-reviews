<?php
if (!defined('ABSPATH')) {
    exit;
}
$reviews = (new CTRW_Review_Model())->get_reviews('approved');
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

<style>
.review-widget-container {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    padding: 24px;
    max-width: 550px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #333;
}

.review-header {
    text-align: center;
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 20px;
}

.review-content {
    display: flex;
    align-items: center;
    gap: 30px;
}

.rating-summary-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
    width: 200px;
    border: 1px solid #dfdfdf;
    padding: 20px;
}
.average-rating-score {
    font-size: 3.5rem;
    font-weight: bold;
    line-height: 1;
}

.average-rating-score .max-score {
    font-size: 1.5rem;
    color: #9e9e9e;
    font-weight: normal;
    margin-left: 4px;
}

.star-display {
    font-size: 1.5rem;
    margin-top: 8px;
    letter-spacing: 2px;
}

.star-filled {
    color: #ffb300;
}

.star-half {
    position: relative;
    color: #e0e0e0;
}

.star-half:after {
    content: '★';
    position: absolute;
    left: 0;
    width: 50%;
    overflow: hidden;
    color: #ffb300;
}

.star-empty {
    color: #e0e0e0;
}

.total-reviews-text {
    font-size: 0.875rem;
    color: #757575;
    margin-top: 8px;
}

.rating-breakdown-block {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.rating-bar-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.star-label {
    font-size: 0.9rem;
    color: #ffb300;
    flex-shrink: 0;
    letter-spacing: 1px;
}

.bar-background {
    flex-grow: 1;
    height: 8px;
    background-color: #f1f1f1;
    border-radius: 4px;
    overflow: hidden;
}

.bar-foreground {
    height: 100%;
    background-color: #ffb300;
    border-radius: 4px;
    transition: width 0.6s ease;
}

.review-count {
    font-size: 0.875rem;
    color: #757575;
    min-width: 20px;
    text-align: right;
}

@media (max-width: 480px) {
    .review-widget-container {
        padding: 16px;
    }

    .review-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }

    .rating-summary-block {
        width: 100%;
        align-items: center;
    }

    .rating-breakdown-block {
        width: 100%;
    }

    .star-label {
        width: 70px;
    }

    .rating-bar-row {
        gap: 8px;
    }
}
</style>