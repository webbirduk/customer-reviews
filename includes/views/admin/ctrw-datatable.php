<ul class="subsubsub">
    <?php foreach ($statuses as $key => $label): 
        $count = $counts[$key] ?? 0;
        $class = (isset($_GET['status']) && $_GET['status'] === $key) ? 'current' : '';
    ?>
        <li class="<?= esc_attr($key) ?>">
            <a href="?page=customer-reviews&status=<?= esc_attr($key) ?>" class="<?= esc_attr($class) ?>">
                <?= esc_html($label) ?> <span class="count">(<?= intval($count) ?>)</span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<form method="post">
    <input type="hidden" name="page" value="customer-reviews" />
    
    <div class="tablenav top">
        <div class="alignleft actions">
            <select name="bulk_action">
                <option value="">Bulk Actions</option>
                <option value="approve">Approve</option>
                <option value="reject">Reject</option>
                <option value="trash">Move to Trash</option>
                <?php if ($current_status === 'trash'): ?>
                    <option value="delete_permanently">Delete Permanently</option>
                <?php endif; ?>
            </select>

           <?php
            $selected_review_type = $_POST['review_type'] ?? '';
            ?>
            <select name="review_type">
                <option value="" <?= $selected_review_type === '' ? 'selected' : '' ?>>All Review Types</option>
                <option value="page" <?= $selected_review_type === 'page' ? 'selected' : '' ?>>Page Reviews</option>
                <option value="post" <?= $selected_review_type === 'post' ? 'selected' : '' ?>>Post Reviews</option>
                <option value="product" <?= $selected_review_type === 'product' ? 'selected' : '' ?>>Product Reviews</option>
            </select>

            <input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter">
            <input type="submit" name="apply" id="doaction" class="button action" value="Apply" />
        </div>

        <div class="tablenav-pages">
            <?= paginate_links([
                'base' => add_query_arg([
                    'paged' => '%#%',
                    'review_type' => $review->review_type ?? ''
                ]),
                'format' => '',
                'prev_text' => __('«'),
                'next_text' => __('»'),
                'total' => $total_pages,
                'current' => $page
            ]) ?>
        </div>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="check-column"><input type="checkbox" id="select-all" /></th>
                <th>Review Title</th>
                <th>Author</th>
                <th>Rating</th>
                <th>Review</th>
                <th>Admin Reply</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($all_reviews)): 
                
                $reviews = array_map(function($item) {
                return (array)$item;
                }, $all_reviews);

                ?>
                <?php foreach ($reviews as $review):
                    $rating = intval($review['rating']);
                    $stars = '';
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $rating) {
                            $stars .= '<span style="color: #ffc107;">&#9733;</span>'; // Gold star
                        } else {
                            $stars .= '<span style="color: #ccc;">&#9733;</span>'; // Gray star
                        }
                    }
                ?>
                    <tr>
                        <th scope="row" class="check-column">
                            <input type="checkbox" name="review_ids[]" value="<?= intval($review['id']) ?>" />
                        </th>
                        <td><?= esc_html($review['title']) ?></td>
                        <td><?= esc_html($review['name']) ?></td>
                        <td><?= $stars ?></td>
                        <td><?= esc_html($review['comment']) ?></td>
                        <td><?= esc_html($review['admin_reply']) ?></td>
                        <td>
                            <a href="?page=customer-reviews&status=<?= esc_attr($review['status']) ?>" class="review-status-link">
                                <?= esc_html($review['status']) ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($review['status'] !== 'rejected'): ?>
                                <button type="button" class="button reply-now"
                                    data-review-id="<?= intval($review['id']) ?>"
                                    data-review-author="<?= esc_attr($review['name']) ?>"
                                    data-reply-message="<?= esc_attr($review['admin_reply']) ?>">Reply</button>
                                <button type="button" class="button edit-review"
                                    data-review-id="<?= intval($review['id']) ?>"
                                    data-review-author="<?= esc_attr($review['name']) ?>"
                                    data-review-email="<?= esc_attr($review['email']) ?>"
                                    data-review-phone="<?= esc_attr($review['phone']) ?>"
                                    data-review-website="<?= esc_attr($review['website']) ?>"
                                    data-review-title="<?= esc_attr($review['title']) ?>"
                                    data-review-comment="<?= esc_attr($review['comment']) ?>"
                                    data-review-rating="<?= intval($review['rating']) ?>"
                                    data-review-status="<?= esc_attr($review['status']) ?>"
                                    data-review-city="<?= esc_attr($review['city']) ?>"
                                    data-review-state="<?= esc_attr($review['state']) ?>"
                                    data-review-positionid="<?= intval($review['positionid']) ?>" data-update-type="update">
                                    Edit Review
                                </button>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8">No reviews found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?= paginate_links([
                'base' => add_query_arg([
                    'paged' => '%#%',
                    'review_type' => $_GET['review_type'] ?? ''
                ]),
                'format' => '',
                'prev_text' => __('«'),
                'next_text' => __('»'),
                'total' => $total_pages,
                'current' => $page
            ]) ?>
        </div>
    </div>
</form>

