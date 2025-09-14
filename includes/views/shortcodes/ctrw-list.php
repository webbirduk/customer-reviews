<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get all approved reviews
$all_reviews = (new CTRW_Review_Model())->get_reviews('approved');

// Filter reviews for current page/product
$filtered_reviews = [];
$current_post_id = get_queried_object_id();
foreach ($all_reviews as $review) {
    if ($review->positionid == $current_post_id) {
        $filtered_reviews[] = $review;
    }
}

// Pagination settings
$reviews_per_page = get_option('customer_reviews_settings')['reviews_per_page'] ?? 10;
$current_page = max(1, get_query_var('review_page', 1));
$total_reviews = count($filtered_reviews);
$total_pages = ceil($total_reviews / $reviews_per_page);
$offset = ($current_page - 1) * $reviews_per_page;
$reviews = array_slice($filtered_reviews, $offset, $reviews_per_page);
?>


<div class="customer-reviews-form-container">
    <div id="reviews-container" data-post-id="<?php echo $current_post_id; ?>">
        <div class="review-list">
            <?php if (!empty($reviews)) : ?>
                <?php foreach ($reviews as $review) : ?>
                    <?php
                    $settings = get_option('customer_reviews_settings');
                    $show_city = !empty($settings['show_city']);
                    $show_state = !empty($settings['show_state']);
                    $show_title = !isset($settings['enable_review_title']) || !empty($settings['enable_review_title']);
                    $date_format = $settings['date_format'] ?? 'MM/DD/YYYY';
                    $include_time = $settings['include_time'] ?? false;
                    
                    $formatted_date = '';
                    if (!empty($review->created_at)) {
                        $timestamp = strtotime($review->created_at);
                        switch ($date_format) {
                            case 'DD/MM/YYYY': $formatted_date = date('d/m/Y', $timestamp); break;
                            case 'YYYY/MM/DD': $formatted_date = date('Y/m/d', $timestamp); break;
                            default: $formatted_date = date('m/d/Y', $timestamp); break;
                        }
                        if ($include_time) $formatted_date .= ' ' . date('H:i', $timestamp);
                    }
                    ?>
                    
                    <div class="review-author-details">
                        <span class="review-author">
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
                                <?= str_repeat('<span class="star filled">★</span>', $review->rating); ?>
                                <?= str_repeat('<span class="star empty">★</span>', 5 - $review->rating); ?>
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
                            <p><?= esc_html($review->comment); ?></p>
                        </div>
                        
                        <?php if (!empty($review->admin_reply)) : ?>
                            <div class="admin-response">
                                <strong>Author Response</strong>
                                <p><?= esc_html($review->admin_reply); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                
                <?php if ($total_pages > 1) : ?>
                    <div class="reviews-pagination">
                        <?php if ($current_page > 1) : ?>
                            <a href="#" class="prev-page" data-page="<?= $current_page - 1; ?>">« Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <?php if ($i == $current_page) : ?>
                                <span class="current-page"><?= $i; ?></span>
                            <?php else : ?>
                                <a href="#" class="page-number" data-page="<?= $i; ?>"><?= $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($current_page < $total_pages) : ?>
                            <a href="#" class="next-page" data-page="<?= $current_page + 1; ?>">Next »</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="no-reviews-message">No reviews found for this page.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $(document).on('click', '.reviews-pagination a', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var target = $('#reviews-container');
        var scrollTo = target.offset().top - 20;
        var page = $(this).data('page');
        var postId = target.data('post-id');
        
        target.addClass('loading');
        
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'load_reviews_ajax',
                page: page,
                post_id: postId
            },
            success: function(response) {
                target.html(response);
                $('html, body').animate({scrollTop: scrollTo}, 300);
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            },
            complete: function() {
                target.removeClass('loading');
            }
        });
    });
});
</script>