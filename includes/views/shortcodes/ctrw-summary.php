<?php
if (!defined('ABSPATH')) {
    exit;
}
$reviews = (new CTRW_Review_Model())->get_reviews('approved');
$settings = get_option('customer_reviews_settings');
?>

<div class="ctrw-summary-container">
    <div class="ctrw-summary-header">
        <p class="ctrw-summary-title">Customer Review Summary</p>

          <?php
            $average_rating = 0;
            $total_reviews = count($reviews);
            if ($total_reviews > 0) {
                $total_rating = 0;
                foreach ($reviews as $review) {
                    $total_rating += (int)$review->rating;
                }
                $average_rating = $total_rating / $total_reviews;
            }
            ?>
        
        <div class="ctrw-overall-rating">
            <div class="ctrw-average-box">
                <span class="ctrw-average-number"><?= number_format($average_rating, 1) ?></span>
                <span class="ctrw-average-out-of">/5</span>
            </div>
            
            <div class="ctrw-stars-large">
                <?php
                $full_stars = floor($average_rating);
                $has_half_star = ($average_rating - $full_stars) >= 0.5;
                
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $full_stars) {
                        echo '<span class="ctrw-star ctrw-filled">★</span>';
                    } elseif ($i == $full_stars + 1 && $has_half_star) {
                        echo '<span class="ctrw-star ctrw-half">★</span>';
                    } else {
                        echo '<span class="ctrw-star ctrw-empty">★</span>';
                    }
                }
                ?>
            </div>
            
            <div class="ctrw-total-reviews">
                Based on <?= $total_reviews ?> <?= ($total_reviews === 1) ? 'review' : 'reviews' ?>
            </div>
        </div>
    </div>
    
    <div class="ctrw-rating-breakdown">
       <?php
            $rating_counts = array(0, 0, 0, 0, 0); // For 1-5 stars
            foreach ($reviews as $review) {
                $rating = (int)$review->rating;
                if ($rating >= 1 && $rating <= 5) {
                    $rating_counts[$rating - 1]++;
                }
            }
         for ($i = 5; $i >= 1; $i--) : 
            $count = $rating_counts[$i - 1];
            $percentage = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
        ?>
            <div class="ctrw-rating-row">
                <div class="ctrw-rating-label">
                    <span class="ctrw-rating-stars">
                        <?= str_repeat('★', $i) ?><?= str_repeat('☆', 5 - $i) ?>
                    </span>
                </div>
                
                <div class="ctrw-rating-bar-container">
                    <div class="ctrw-rating-bar" style="width: <?= $percentage ?>%"></div>
                </div>
                
                <div class="ctrw-rating-count">
                    <?= $count ?>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</div>

<style>
.ctrw-summary-container {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    background: #ffffff;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
    max-width: 480px;
    margin: 0 auto;
}

.ctrw-summary-header {
    text-align: center;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid #f0f0f0;
}

.ctrw-summary-title {
    font-size: 18px;
    font-weight: 600;
    color: #333333;
    margin: 0 0 16px 0;
}

.ctrw-overall-rating {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.ctrw-average-box {
    display: flex;
    align-items: baseline;
    margin-bottom: 8px;
}

.ctrw-average-number {
    font-size: 38px;
    font-weight: 700;
    color: #333333;
    line-height: 1;
}

.ctrw-average-out-of {
    font-size: 20px;
    color: #888888;
    margin-left: 2px;
}

.ctrw-stars-large {
    font-size: 24px;
    letter-spacing: 2px;
    margin-bottom: 8px;
    line-height: 1;
}

.ctrw-star {
    display: inline-block;
    position: relative;
}

.ctrw-filled {
    color: #FFB800;
}

.ctrw-half {
    color: #e0e0e0;
    position: relative;
}

.ctrw-half:after {
    content: '★';
    position: absolute;
    left: 0;
    width: 50%;
    overflow: hidden;
    color: #FFB800;
}

.ctrw-empty {
    color: #e0e0e0;
}

.ctrw-total-reviews {
    font-size: 14px;
    color: #666666;
}

.ctrw-rating-breakdown {
    display: flex;
    flex-direction: column;
    gap: 12px;
    line-height: 14px;
}

.ctrw-rating-row {
    display: flex;
    align-items: center;
    gap: 12px;
}

.ctrw-rating-label {
    width: 80px;
}

.ctrw-rating-stars {
    font-size: 14px;
    color: #FFB800;
    white-space: nowrap;
}

.ctrw-rating-bar-container {
    flex: 1;
    height: 8px;
    background: #f5f5f5;
    border-radius: 4px;
    overflow: hidden;
}

.ctrw-rating-bar {
    height: 100%;
    background: #FFB800;
    border-radius: 4px;
    transition: width 0.6s ease;
}

.ctrw-rating-count {
    width: 30px;
    font-size: 14px;
    color: #666666;
    text-align: right;
}

@media (max-width: 480px) {
    .ctrw-summary-container {
        padding: 16px;
    }
    
    .ctrw-rating-row {
        gap: 8px;
    }
    
    .ctrw-rating-label {
        width: 70px;
    }
}
</style>