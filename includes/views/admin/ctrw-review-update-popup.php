<div id="cr-edit-review-popup" style="width:60%; display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);
    background:#fff; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.5); z-index:1000;">
    <h2><?php esc_html_e('Edit Review', 'ctrw-reviews'); ?></h2>
    <form id="edit-review-form">
        <div style="display:flex; gap:20px;">
            <div style="flex:1;">
                <p>
                    <label for="edit-review-name"><strong><?php esc_html_e('Review Author:', 'ctrw-reviews'); ?></strong></label><br>
                    <input type="text" name="review_name" id="edit-review-name" style="width:100%;">
                </p>
                <p>
                    <label for="edit-review-email"><strong><?php esc_html_e('Reviewers Email:', 'ctrw-reviews'); ?></strong></label><br>
                    <input type="email" name="review_email" id="edit-review-email" style="width:100%;">
                </p>
                <p>
                    <label for="edit-review-website"><strong><?php esc_html_e('Reviewers Website:', 'ctrw-reviews'); ?></strong></label><br>
                    <input type="url" name="review_website" id="edit-review-website" style="width:100%;">
                </p>
                <p>
                    <label for="edit-review-phone"><strong><?php esc_html_e('Reviewers Phone:', 'ctrw-reviews'); ?></strong></label><br>
                    <input type="tel" name="review_phone" id="edit-review-phone" style="width:100%;">
                </p>
                <p>
                    <label for="edit-review-city"><strong><?php esc_html_e('Reviewers City:', 'ctrw-reviews'); ?></strong></label><br>
                    <input type="text" name="review_city" id="edit-review-city" style="width:100%;">
                </p>
                <p>
                    <label for="edit-review-state"><strong><?php esc_html_e('Reviewers State:', 'ctrw-reviews'); ?></strong></label><br>
                    <input type="text" name="review_state" id="edit-review-state" style="width:100%;">
                </p>

               
                <p>
                    <label for="edit-review-status"><strong><?php esc_html_e('Status:', 'ctrw-reviews'); ?></strong></label><br>
                    <select name="review_status" id="edit-review-status" style="width:100%;">
                        <option value="Approved"><?php esc_html_e('Approved', 'ctrw-reviews'); ?></option>
                        <option value="Pending"><?php esc_html_e('Pending', 'ctrw-reviews'); ?></option>
                        <option value="Rejected"><?php esc_html_e('Rejected', 'ctrw-reviews'); ?></option>
                        <option value="Trash"><?php esc_html_e('Trash<', 'ctrw-reviews'); ?>/option>
                    </select>
                </p>
            </div>
            <div style="flex:1;">
                <input type="hidden" name="review_id" id="edit-review-id" value="">
                <input type="hidden" name="review_id" id="update-type" value="">
                <p>
                    <label for="edit-review-title"><strong><?php esc_html_e('Review Title:', 'ctrw-reviews'); ?></strong></label><br>
                    <input type="text" name="review_title" id="edit-review-title" style="width:100%;">
                </p>
                <p>
                    <label for="edit-review-comment"><strong><?php esc_html_e('Review Comment:', 'ctrw-reviews'); ?></strong></label><br>
                    <textarea name="review_comment" id="edit-review-comment" rows="10" style="width:100%;"></textarea>
                </p>

                 <p>
                    <label for="edit-review-rating"><strong><?php esc_html_e('Rating:', 'ctrw-reviews'); ?></strong></label><br>
                    <select name="review_rating" id="edit-review-rating" style="width:100%;">
                        <?php for ($i=1; $i<=5; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                </p>
                
                <p>
                    <label for="edit-review-positionid"><strong><?php esc_html_e('Reviewed Display Post/Page:', 'ctrw-reviews'); ?></strong></label><br>
                    <select name="review_positionid" id="edit-review-positionid" style="width:100%;">
                        <?php
                        $post_types = ['post' => 'Post', 'page' => 'Page', 'product' => 'Product'];
                        foreach ($post_types as $type => $label) {
                            $posts = get_posts(['post_type' => $type, 'numberposts' => -1]);
                            if (!empty($posts)) {
                                echo '<optgroup label="' . esc_attr($label) . 's">';
                                foreach ($posts as $post) {
                                    echo '<option value="' . intval($post->ID) . '">' . esc_html($post->post_title) . '</option>';
                                }
                                echo '</optgroup>';
                            }
                        }
                        ?>
                    </select>
                </p>
            </div>
        </div>
        <div style="margin-top:20px;">
            <button type="submit" id="update-customer-review" class="button button-primary"><?php esc_html_e('Update Review', 'ctrw-reviews'); ?></button>
            <button type="button" class="button" id="close-edit-review-popup"><?php esc_html_e('Cancel', 'ctrw-reviews'); ?></button>
        </div>
    </form>
</div>
