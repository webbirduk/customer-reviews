jQuery(document).ready(function ($) {
    const $reviewForm = $("#customer-reviews-form");
    const $reviewsContainer = $("#reviews-container");

    // Submit Review Form
    $reviewForm.on("submit", function (e) {
        e.preventDefault();


        let formData = $reviewForm.serialize();
        formData += "&action=submit_review";

        $.ajax({
            url: ctrw_ajax.ajax_url,
            method: "POST",
            data: formData,
            success: function (data) {

                console.log(data);
                let $message = $("#review-message");
                if (data.success) {
                    $message.html("✅ Review submitted successfully!").css("color", "green");
                    $reviewForm[0].reset();
                } else {
                    $message.html("❌ Error submitting review.").css("color", "red");
                }
            }
        });
    });

    // Admin Reply to Review
    $reviewsContainer.on("submit", ".admin-reply-form", function (e) {
        e.preventDefault();

        let $form = $(this);
        let formData = $form.serialize();

        $.ajax({
            url: review_ajax.ajax_url,
            method: "POST",
            data: formData,
            success: function (data) {
                if (data.success) {
                    $form.replaceWith(`<p><strong>Admin Reply:</strong> ${data.reply}</p>`);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error:", error);
            }
        });
    });

    
$('.floating-reviews-toggle').click(function() {
        $('.floating-reviews-container').toggleClass('active');
    });
    
    $('.floating-reviews-close').click(function() {
        $('.floating-reviews-container').removeClass('active');
    });
    
    // Close when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('.floating-reviews-container').length) {
            $('.floating-reviews-container').removeClass('active');
        }
    });
    
});

