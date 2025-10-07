<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get plugin settings to determine reviews per page.
$settings = get_option('customer_reviews_settings', []);
$reviews_per_page = $settings['reviews_per_page'] ?? 10;

// Determine the current page and post ID, prioritizing AJAX data over standard query variables.
if (defined('DOING_AJAX') && DOING_AJAX) {
    // This is an AJAX request, so get data from the POST variables.
    $current_page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $current_post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
} else {
    // This is a standard page load.
    $current_page = max(1, get_query_var('review_page', 1));
    $current_post_id = get_queried_object_id();
}

// Get all approved reviews from the database.
$all_reviews = (new CTRW_Review_Model())->get_reviews('approved');

// Filter the reviews to only include those for the current post/page.
$filtered_reviews = [];
foreach ($all_reviews as $review) {
    if ($review->positionid == $current_post_id) {
        $filtered_reviews[] = $review;
    }
}

// Perform pagination on the now-filtered list of reviews.
$total_reviews = count($filtered_reviews);
$total_pages = ceil($total_reviews / $reviews_per_page);
$offset = ($current_page - 1) * $reviews_per_page;
$reviews_for_page = array_slice($filtered_reviews, $offset, $reviews_per_page);

?>

<?php /* The main container is only output on the initial page load, not on subsequent AJAX loads. */ ?>
<?php if (! (defined('DOING_AJAX') && DOING_AJAX) ) : ?>
<div class="customer-reviews-form-container">
    <div id="reviews-container" data-post-id="<?php echo esc_attr($current_post_id); ?>">
<?php endif; ?>

        <div class="review-list">
            <?php if (!empty($reviews_for_page)) : ?>
                <?php foreach ($reviews_for_page as $review) : ?>
                    <?php
                    // Individual review display settings.
                    $show_city = !empty($settings['show_city']);
                    $show_state = !empty($settings['show_state']);
                    $show_title = !isset($settings['enable_review_title']) || !empty($settings['enable_review_title']);
                    $date_format_setting = $settings['date_format'] ?? 'MM/DD/YYYY';
                    $include_time = $settings['include_time'] ?? false;
                    
                    $formatted_date = '';
                    if (!empty($review->created_at)) {
                        $timestamp = strtotime($review->created_at);
                        $date_format = str_replace(['MM', 'DD', 'YYYY'], ['m', 'd', 'Y'], $date_format_setting);
                        $formatted_date = date($date_format, $timestamp);
                        if ($include_time) {
                            $formatted_date .= ' ' . date('H:i', $timestamp);
                        }
                    }
                    ?>
                    
                    <div class="review-author-details">
                        <span class="review-author" style="font-weight: <?= esc_attr($settings['name_font_weight'] ?? 'bold'); ?>;">
                            Posted By <?= esc_html($review->name); ?>
                            <?php if ($show_city && !empty($review->city)) echo ', ' . esc_html($review->city); ?>
                            <?php if ($show_state && !empty($review->state)) echo ', ' . esc_html($review->state); ?>
                        </span>
                        <div class="review-date">
                            Post Date<?= $include_time ? '/Time' : '' ?>: <?= esc_html($formatted_date); ?>
                        </div>
                    </div>

                    <div class="review-item" style="background-color: <?= esc_attr($settings['comment_box_fill_color'] ?? '#f5f5f5'); ?>;">
                        <div class="review-header">
                            <span class="stars">
                                <?= str_repeat('<span class="star filled">★</span>', (int)$review->rating); ?>
                                <?= str_repeat('<span class="star empty">★</span>', 5 - (int)$review->rating); ?>
                            </span>
                            <?php if ($show_title && !empty($review->title)) : ?>
                                <div class="review-title"><?= esc_html($review->title); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="review-content" style="
                            font-size: <?= esc_attr($settings['comment_font_size'] ?? 14); ?>px;
                            font-style: <?= esc_attr($settings['comment_font_style'] ?? 'normal'); ?>;
                            line-height: <?= esc_attr($settings['comment_line_height'] ?? 23); ?>px;
                        ">
                            <p><?= nl2br(esc_html($review->comment)); ?></p>
                        </div>
                        
                        <?php if (!empty($review->admin_reply)) : ?>
                            <div class="admin-response">
                                <strong>Author Response</strong>
                                <p><?= nl2br(esc_html($review->admin_reply)); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($total_pages > 1) : ?>
                    <div class="reviews-pagination">
                        <?php if ($current_page > 1) : ?>
                            <a href="#" class="prev page-numbers" data-page="<?= $current_page - 1; ?>">« Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <?php if ($i == $current_page) : ?>
                                <span aria-current="page" class="page-numbers current"><?= $i; ?></span>
                            <?php else : ?>
                                <a href="#" class="page-numbers" data-page="<?= $i; ?>"><?= $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages) : ?>
                            <a href="#" class="next page-numbers" data-page="<?= $current_page + 1; ?>">Next »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php else : ?>
                <div class="no-reviews-message">No reviews found for this page.</div>
            <?php endif; ?>
        </div>

<?php if (! (defined('DOING_AJAX') && DOING_AJAX) ) : ?>
    </div>
</div>
<?php endif; ?>