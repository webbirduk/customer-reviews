jQuery(document).ready(function($) {

    // Initialize color pickers on the settings page
    if ($('#star_color').length) {
        $('#star_color').wpColorPicker();
    }
    if ($('#comment_box_fill_color').length) {
        $('#comment_box_fill_color').wpColorPicker();
    }

    $('.reply-now').on('click', function() {
        var reviewId = $(this).data('review-id');
        var $button = $(this);
        $button.prop('disabled', true).text('Loading...'); // Add loading feedback

        $.ajax({
            url: ctrw_admin_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'get_review_details',
                review_id: reviewId,
                nonce: ctrw_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    var reviewData = response.data;
                    $('#reply-review-id').val(reviewData.id);
                    $('#reply-review-author').text(reviewData.name);
                    $('#reply-message').val(reviewData.admin_reply || '');
                    $('#cr-reply-popup').show();
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function() {
                alert('An error occurred while fetching review details.');
            },
            complete: function() {
                $button.prop('disabled', false).text('Reply'); // Restore button
            }
        });
    });


    $('#close-reply-popup').on('click', function() {
        $('#cr-reply-popup').hide();
    });

    $('#reply-form').on('submit', function(event) {
        event.preventDefault();
        let reviewId = $('#reply-review-id').val();
        let replyMessage = $('#reply-message').val();

        if (!replyMessage.trim()) {
            alert('Reply message cannot be empty.');
            return;
        }

        $.ajax({
            url: ctrw_admin_ajax.ajax_url, 
            method: 'POST',
            data: {
                action: 'save_review_reply',
                review_id: reviewId,
                reply_message: replyMessage,
                nonce: ctrw_admin_ajax.nonce 
            },
            success: function(response) {
                if (response.success) {
                    alert('Reply submitted successfully.');
                    $('#cr-reply-popup').hide();
                    location.reload();
                } else {
                    alert('Failed to submit reply.');
                }
            },
            error: function() {
                alert('An error occurred while submitting the reply.');
            }
        });
    });

    // Edit review popup logic (handles both ADD and EDIT)
    $('.edit-review').on('click', function() {
        var $button = $(this);
        var updateType = $button.data('update-type');
        var reviewId = $button.data('review-id');

        // Reset the form before showing it
        $('#edit-review-form')[0].reset();
        $('#edit-review-id').val(''); // Clear hidden ID field

        if (updateType === 'add') {
            // --- HANDLE ADD ---
            // Simply open the popup with a blank form
            $('#update-type').val('add');
            $('#cr-edit-review-popup h2').text('Add New Review'); // Optional: Change title for clarity
            $('#cr-edit-review-popup').show();

        } else {
            // --- HANDLE EDIT ---
            // Fetch the existing review data via AJAX
            $button.prop('disabled', true).text('Loading...');

            $.ajax({
                url: ctrw_admin_ajax.ajax_url,
                method: 'POST',
                data: {
                    action: 'get_review_details',
                    review_id: reviewId,
                    nonce: ctrw_admin_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var reviewData = response.data;
                        $('#update-type').val('update');
                        $('#edit-review-id').val(reviewData.id);
                        $('#edit-review-name').val(reviewData.name);
                        $('#edit-review-email').val(reviewData.email);
                        $('#edit-review-phone').val(reviewData.phone);
                        $('#edit-review-website').val(reviewData.website);
                        $('#edit-review-comment').val(reviewData.comment);
                        $('#edit-review-city').val(reviewData.city);
                        $('#edit-review-state').val(reviewData.state);
                        $('#edit-review-status').val(reviewData.status);
                        $('#edit-review-rating').val(reviewData.rating);
                        $('#edit-review-title').val(reviewData.title);
                        $('#edit-review-positionid').val(reviewData.positionid);
                        $('#cr-edit-review-popup h2').text('Edit Review'); // Restore title
                        $('#cr-edit-review-popup').show();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred while fetching review details.');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Edit Review');
                }
            });
        }
    });

    $('#close-edit-review-popup').on('click', function() {
        $('#cr-edit-review-popup').hide();
    });

    $('#edit-review-form').on('submit', function(event) {
        event.preventDefault();
        $.ajax({
            url: ctrw_admin_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'edit_customer_review',
                id: $('#edit-review-id').val(),
                update_type: $('#update-type').val(),
                name: $('#edit-review-name').val(),
                email: $('#edit-review-email').val(),
                phone: $('#edit-review-phone').val(),
                website: $('#edit-review-website').val(),
                comment: $('#edit-review-comment').val(),
                city: $('#edit-review-city').val(),
                state: $('#edit-review-state').val(),
                status: $('#edit-review-status').val(),
                rating: $('#edit-review-rating').val(),
                title: $('#edit-review-title').val(),
                positionid: $('#edit-review-positionid').val(),
                nonce: ctrw_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Review updated successfully.');
                    $('#cr-edit-review-popup').hide();
                    location.reload();
                } else {
                    alert('Failed to update review.');
                }
            },
            error: function() {
                alert('An error occurred while updating the review.');
            }
        });
    });

    // Import popup logic
    $('#import-customer-reviews').on('click', function() {
        $('#ctrw-import-popup').show();
    });

    $('#close-ctrw-import-popup').on('click', function() {
        $('#ctrw-import-popup').hide();
    });

    $('#ctrw-import-form').on('submit', function(event) {
        event.preventDefault();
        let selectedPlugin = $('#ctrw_import_plugin').val();
        $.ajax({
            url: ctrw_admin_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'ctrw_import_review_from_others',
                ctrw_import_review: selectedPlugin,
                nonce: ctrw_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message || 'Imports completed successfully.');
                    $('#ctrw-import-popup').hide();
                    location.reload();
                } else {
                     alert(response.data.message || 'Failed to import reviews.');
                }
            },
            error: function() {
                alert('An error occurred during import.');
            }
        });
    });

    // Bulk action select-all checkbox
    $('#select-all').on('click', function() {
        let isChecked = $(this).prop('checked');
        $('input[name="review_ids[]"]').prop('checked', isChecked);
    });

    // NEW: Settings page tab functionality
    $('.nav-tab-wrapper .nav-tab').on('click', function(e) {
        e.preventDefault();
        
        // Handle active states
        $('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show/hide tab content
        var tabId = $(this).data('tab');
        $('.tab-section').hide();
        $('#tab-' + tabId).show();
    });


    $('#ctrw-form-settings').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        formData += '&nonce=' + ctrw_admin_ajax.nonce;
        formData += '&action=ctrw_save_settings';            
        $.ajax({
            type: 'POST',
            url: ctrw_admin_ajax.ajax_url,
            data: formData,
            success: function(response) {
                $('#ctrw-success-msg').fadeIn().delay(2000).fadeOut();
            },
            error: function() {
                 $('#ctrw-error-msg').fadeIn().delay(3000).fadeOut();
            }
        });
    });
    
    // Shortcode copy button functionality
    $('.copy-button').on('click', function() {
        var textToCopy = $(this).data('clipboard-text');
        navigator.clipboard.writeText(textToCopy).then(() => {
            // Optional: give user feedback
            var originalText = $(this).text();
            $(this).text('Copied!');
            setTimeout(() => {
                $(this).text(originalText);
            }, 1500);
        });
    });


    // Column visibility toggling for the reviews list table
    function ctrw_toggle_columns() {
        $('.ctrw-toggle-col').each(function() {
            var col = $(this).data('col');
            var checked = $(this).is(':checked');
            var idx = {
                'review-title': 2, 'author': 3, 'rating': 4, 'review': 5,
                'admin-reply': 6, 'status': 7, 'action': 8
            }[col];
            if (idx) {
                $('.wp-list-table th:nth-child(' + idx + '), .wp-list-table td:nth-child(' + idx + ')')
                    .toggle(checked);
            }
        });
    }

    // Bind change event to the checkboxes in Screen Options
    $(document).on('change', '.ctrw-toggle-col', function() {
        ctrw_toggle_columns();
        var data = {};
        $('.ctrw-toggle-col').each(function() {
            data[$(this).data('col')] = $(this).is(':checked') ? 1 : 0;
        });
        // Save the setting via AJAX
        $.post(ajaxurl, { 
            action: 'ctrw_save_column_visibility', 
            columns: data, 
            _wpnonce: ctrw_admin_ajax.nonce // Use the localized nonce
        });
    });

    // Run on page load to apply saved settings
    ctrw_toggle_columns();
});