<?php
if (!defined('ABSPATH')) {
    exit;
}
$reviews = (new CTRW_Review_Model())->get_reviews('approved');
$settings = get_option('customer_reviews_settings');
$reviews_per_page = $settings['reviews_per_page'] ?? 5;
$display_reviews = array_slice($reviews, 0, $reviews_per_page);
?>

<div class="ctrw-floating-widget">
    <div class="ctrw-floating-tab">
        <div class="ctrw-tab-content">
            <span class="ctrw-tab-icon">★</span>
            <span class="ctrw-tab-text">Reviews</span>
            <span class="ctrw-tab-count"><?= count($display_reviews) ?></span>
        </div>
    </div>
    
    <div class="ctrw-floating-content">
        <div class="ctrw-reviews-container">
            <div class="ctrw-reviews-header">
                <h3 class="ctrw-reviews-title">
                    <span class="ctrw-title-icon">★</span>
                    Customer Reviews
                </h3>
                <button class="ctrw-close-btn" aria-label="Close reviews">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M13 1L1 13M1 1L13 13" stroke="#666" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>
            
            <div class="ctrw-reviews-list">
                <?php foreach ($display_reviews as $review) : 
                    $timestamp = strtotime($review->created_at);
                    $formatted_date = date('m/d/Y', $timestamp);
                    if ($settings['include_time'] ?? false) {
                        $formatted_date .= ' ' . date('H:i', $timestamp);
                    }
                    
                    $show_city = !empty($settings['show_city']);
                    $show_state = !empty($settings['show_state']);
                    $show_title = !isset($settings['enable_review_title']) || !empty($settings['enable_review_title']);
                ?>
                    <div class="ctrw-review-card">
                        <div class="ctrw-review-header">
                            <div class="ctrw-review-rating">
                                <?= str_repeat('★', (int)$review->rating) ?><?= str_repeat('☆', 5 - (int)$review->rating) ?>
                            </div>
                            
                            <div class="ctrw-review-meta">
                                <div class="ctrw-review-author">
                                    <span class="ctrw-author-name"><?= esc_html($review->name) ?></span>
                                    <?php if ($show_city && !empty($review->city)): ?>
                                        <span class="ctrw-review-location">, <?= esc_html($review->city) ?></span>
                                        <?php if ($show_state && !empty($review->state)): ?>
                                            <span class="ctrw-review-location">, <?= esc_html($review->state) ?></span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="ctrw-review-date">
                                    <?= $formatted_date ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ctrw-review-body">
                            <?php if ($show_title && !empty($review->title)): ?>
                                <h4 class="ctrw-review-title"><?= esc_html($review->title) ?></h4>
                            <?php endif; ?>
                            
                            <div class="ctrw-review-content">
                                <?= esc_html($review->comment) ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($review->admin_reply)): ?>
                            <div class="ctrw-admin-reply">
                                <div class="ctrw-reply-header">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="#FFB800" stroke-width="2"/>
                                        <path d="M12 8V12" stroke="#FFB800" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M12 16H12.01" stroke="#FFB800" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                    <span>Author Response</span>
                                </div>
                                <div class="ctrw-reply-content"><?= esc_html($review->admin_reply) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($reviews) > $reviews_per_page): ?>
                <div class="ctrw-reviews-footer">
                    <button class="ctrw-view-all-btn">
                        View All Reviews
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M5 12H19M19 12L12 5M19 12L12 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>



<script>
jQuery(document).ready(function($) {
    // Toggle widget visibility
    $('.ctrw-floating-tab').on('click', function(e) {
        e.stopPropagation();
        $('.ctrw-floating-widget').toggleClass('active');
    });
    
    // Close widget
    $('.ctrw-close-btn').on('click', function(e) {
        e.stopPropagation();
        $('.ctrw-floating-widget').removeClass('active');
    });
    
    // Close when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.ctrw-floating-widget').length) {
            $('.ctrw-floating-widget').removeClass('active');
        }
    });
    
    // View all reviews button handler
    $('.ctrw-view-all-btn').on('click', function() {
        // Implement your view all functionality here
        alert('View all reviews functionality would go here');
    });
});
</script>