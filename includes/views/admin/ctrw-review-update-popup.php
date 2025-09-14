<div id="cr-edit-review-popup" style="width:60%; display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);
    background:#fff; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.5); z-index:1000;">
    <h2>Edit Review</h2>
    <form id="edit-review-form">
        <div style="display:flex; gap:20px;">
            <div style="flex:1;">
                <p>
                    <label for="edit-review-name"><strong>Review Author:</strong></label><br>
                    <input type="text" name="review_name" id="edit-review-name" style="width:100%;">
                </p>
                <p>
                    <label for="edit-review-email"><strong>Reviewers Email:</strong></label><br>
                    <input type="email" name="review_email" id="edit-review-email" style="width:100%;">
                </p>
                <p>
                    <label for="edit-review-website"><strong>Reviewers Website:</strong></label><br>
                    <input type="url" name="review_website" id="edit-review-website" style="width:100%;">
                </p>
                <p>
                    <label for="edit-review-phone"><strong>Reviewers Phone:</strong></label><br>
                    <input type="tel" name="review_phone" id="edit-review-phone" style="width:100%;">
                </p>
                <p>
                    <label for="edit-review-city"><strong>Reviewers City:</strong></label><br>
                    <input type="text" name="review_city" id="edit-review-city" style="width:100%;">
                </p>
                <p>
                    <label for="edit-review-state"><strong>Reviewers State:</strong></label><br>
                    <input type="text" name="review_state" id="edit-review-state" style="width:100%;">
                </p>

               
                <p>
                    <label for="edit-review-status"><strong>Status:</strong></label><br>
                    <select name="review_status" id="edit-review-status" style="width:100%;">
                        <option value="approved">Approved</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                        <option value="trash">Trash</option>
                    </select>
                </p>
            </div>
            <div style="flex:1;">
                <input type="hidden" name="review_id" id="edit-review-id" value="">
                <input type="hidden" name="review_id" id="update-type" value="">
                <p>
                    <label for="edit-review-title"><strong>Review Title:</strong></label><br>
                    <input type="text" name="review_title" id="edit-review-title" style="width:100%;">
                </p>
                <p>
                    <label for="edit-review-comment"><strong>Review Comment:</strong></label><br>
                    <textarea name="review_comment" id="edit-review-comment" rows="10" style="width:100%;"></textarea>
                </p>

                 <p>
                    <label for="edit-review-rating"><strong>Rating:</strong></label><br>
                    <select name="review_rating" id="edit-review-rating" style="width:100%;">
                        <?php for ($i=1; $i<=5; $i++): ?>
                            <option value="<?= $i ?>"><?= $i ?> Star<?= $i > 1 ? 's' : '' ?></option>
                        <?php endfor; ?>
                    </select>
                </p>
                
                <p>
                    <label for="edit-review-positionid"><strong>Reviewed Display Post/Page:</strong></label><br>
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
            <button type="submit" id="update-customer-review" class="button button-primary">Update Review</button>
            <button type="button" class="button" id="close-edit-review-popup">Cancel</button>
        </div>
    </form>
</div>
