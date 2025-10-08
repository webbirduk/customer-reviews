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
    <?php wp_nonce_field('ctrw_bulk_action_nonce', 'ctrw_bulk_nonce'); ?>

    <div class="tablenav top">
        <div class="alignleft actions">
            <select name="bulk_action">
                <option value=""><?php esc_html_e('Bulk Actions', 'ctrw-reviews'); ?></option>
                <option value="approve"><?php esc_html_e('Approve', 'ctrw-reviews'); ?></option>
                <option value="pending"><?php esc_html_e('Pending', 'ctrw-reviews'); ?></option>
                <option value="reject"><?php esc_html_e('Reject', 'ctrw-reviews'); ?></option>
                <option value="trash"><?php esc_html_e('Move to Trash', 'ctrw-reviews'); ?></option>
                <?php if ($current_status === 'trash'): ?>
                    <option value="delete_permanently"><?php esc_html_e('Delete Permanently', 'ctrw-reviews'); ?></option>
                <?php endif; ?>
            </select>

           <?php
            $selected_review_type = $_POST['review_type'] ?? '';
            ?>
            <select name="review_type">
                <option value="" <?= $selected_review_type === '' ? 'selected' : '' ?>><?php esc_html_e('All Review Types', 'ctrw-reviews'); ?></option>
                <option value="page" <?= $selected_review_type === 'page' ? 'selected' : '' ?>><?php esc_html_e('Page Reviews', 'ctrw-reviews'); ?></option>
                <option value="post" <?= $selected_review_type === 'post' ? 'selected' : '' ?>><?php esc_html_e('Post Reviews', 'ctrw-reviews'); ?></option>
                <option value="product" <?= $selected_review_type === 'product' ? 'selected' : '' ?>><?php esc_html_e('Product Reviews', 'ctrw-reviews'); ?></option>
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
                <th><?php esc_html_e('Review Title', 'ctrw-reviews'); ?></th>
                <th><?php esc_html_e('Author', 'ctrw-reviews'); ?></th>
                <th><?php esc_html_e('Rating', 'ctrw-reviews'); ?></th>
                <th><?php esc_html_e('Review', 'ctrw-reviews'); ?></th>
                <th><?php esc_html_e('Admin Reply', 'ctrw-reviews'); ?></th>
                <th><?php esc_html_e('Status', 'ctrw-reviews'); ?></th>
                <th><?php esc_html_e('Action', 'ctrw-reviews'); ?></th>
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
                                <button type="button" class="button ctrw-reply-now"
                                    data-review-id="<?= intval($review['id']) ?>"><?php esc_html_e('Reply', 'ctrw-reviews'); ?></button>
                                <button type="button" class="button edit-review"
                                    data-review-id="<?= intval($review['id']) ?>"
                                    data-update-type="update">
                                    <?php esc_html_e('Edit Review', 'ctrw-reviews'); ?>
                                </button>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8"><?php esc_html_e('No reviews found.', 'ctrw-reviews'); ?></td></tr>
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

