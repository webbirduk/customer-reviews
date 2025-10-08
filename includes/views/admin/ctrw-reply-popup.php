<div id="cr-reply-popup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);
    background:#fff; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.5); z-index:1000;">
    <h2><?php esc_html_e('Reply to Review', 'ctrw-reviews'); ?></h2>
    <form id="reply-form">
        <input type="hidden" name="review_id" id="reply-review-id" value="">
        <p><strong><?php esc_html_e('To:', 'ctrw-reviews'); ?></strong> <span id="reply-review-author"></span></p>
        <textarea name="reply_message" id="reply-message" rows="5" style="width:100%;" placeholder="Write your reply here..."></textarea>
        <br><br>
        <button type="submit" class="button button-primary"><?php esc_html_e('Send Reply', 'ctrw-reviews'); ?></button>
        <button type="button" class="button" id="close-reply-popup"><?php esc_html_e('Cancel', 'ctrw-reviews'); ?></button>
    </form>
</div>
