<?php
if (!defined('ABSPATH')) {
    exit;
}

class Review_View {
    
    public function display_reviews($reviews, $counts, $current_status) {

        
    foreach ($reviews as &$review) {
        $post_type = get_post_type($review->positionid);
        $review->review_type = $post_type ? $post_type : 'unknown';
    }

    // Pagination and filtering
    $per_page = get_user_meta(get_current_user_id(), 'ctrw_reviews_per_page', true);
    $per_page = $per_page ? (int)$per_page : 10;
    $page = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
    $offset = ($page - 1) * $per_page;

    $selected_review_type = $_POST['review_type'] ?? '';

    if ($selected_review_type) {
        $reviews = array_filter($reviews, function($review) use ($selected_review_type) {
            return isset($review->review_type) && $review->review_type === $selected_review_type;
        });
    }

    $total_reviews = count($reviews);
    $all_reviews = array_slice($reviews, $offset, $per_page);
    $total_pages = ceil($total_reviews / $per_page);

    $statuses = [
        'all' => 'All',
        'approved' => 'Approve',
        'reject' => 'Reject',
        'pending' => 'Pending',
        'trash' => 'Trash'
    ];
    echo '<h1 class="wp-heading-inline">Customer Reviews</h1>
    <button type="button" class="button edit-review" data-update-type="add">Add Customer Reviews</button>
    <button type="button" class="button import-review" id="import-customer-reviews">Import Customer Reviews</button>
    <hr class="wp-header-end">';

    // Provide variables to the included template files
    include 'views/admin/ctrw-datatable.php';
    include 'views/admin/ctrw-reply-popup.php';
    include 'views/admin/ctrw-review-update-popup.php';
    include 'views/admin/ctrw-import-popup.php';

    echo '</div>';
}

    
}
?>
