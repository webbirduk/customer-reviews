<div id="cr-reply-popup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);
    background:#fff; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.5); z-index:1000;">
    <h2>Reply to Review</h2>
    <form id="reply-form">
        <input type="hidden" name="review_id" id="reply-review-id" value="">
        <p><strong>To:</strong> <span id="reply-review-author"></span></p>
        <textarea name="reply_message" id="reply-message" rows="5" style="width:100%;" placeholder="Write your reply here..."></textarea>
        <br><br>
        <button type="submit" class="button button-primary">Send Reply</button>
        <button type="button" class="button" id="close-reply-popup">Cancel</button>
    </form>
</div>
