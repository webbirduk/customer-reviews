jQuery(document).ready(function($) {

    $('#star_color').on('input', function() {
        $(this).attr('value', $(this).val());
    });

      $('.reply-now').on('click', function() {

      $('#reply-review-id').val($(this).data('review-id'));
      $('#reply-review-author').text($(this).data('review-author'));
      $('#reply-message').val($(this).data('reply-message') || '');
      $('#cr-reply-popup').show();
      });

    $('.edit-review').on('click', function() {
      
        $('#edit-review-id').val($(this).data('review-id'));
        $('#update-type').val($(this).data('update-type'));
        $('#edit-review-name').val($(this).data('review-author'));
        $('#edit-review-email').val($(this).data('review-email'));
        $('#edit-review-phone').val($(this).data('review-phone'));
        $('#edit-review-website').val($(this).data('review-website'));
        $('#edit-review-comment').val($(this).data('review-comment'));
        $('#edit-review-city').val($(this).data('review-city'));
        $('#edit-review-state').val($(this).data('review-state'));
        $('#edit-review-status').val($(this).data('review-status'));
        $('#edit-review-rating').val($(this).data('review-rating'));
        $('#edit-review-title').val($(this).data('review-title'));
        $('#edit-review-positionid').val($(this).data('review-positionid'));

        $('#cr-edit-review-popup').show();
    });

    $('#close-edit-review-popup').on('click', function() {
        $('#cr-edit-review-popup').hide();
    });

    $('#update-customer-review').on('click', function(event) {
        
        // Prevent the default form submission
        event.preventDefault();
       
        let reviewId = $('#edit-review-id').val();
        let updateType = $('#update-type').val();
        let reviewName = $('#edit-review-name').val();
        let reviewEmail = $('#edit-review-email').val();
        let reviewPhone = $('#edit-review-phone').val();
        let reviewWebsite = $('#edit-review-website').val();
        let reviewComment = $('#edit-review-comment').val();
        let reviewCity = $('#edit-review-city').val();
        let reviewState = $('#edit-review-state').val();
        let reviewStatus = $('#edit-review-status').val();
        let reviewRating = $('#edit-review-rating').val();
        let reviewTitle = $('#edit-review-title').val();
        let reviewPositionId = $('#edit-review-positionid').val();
        


        $.ajax({
            url: ctrw_admin_ajax.ajax_url,
            method: 'POST',
            data: {
                action: 'edit_customer_review',
                id: reviewId,
                update_type: updateType,
                name: reviewName,
                email: reviewEmail,
                phone: reviewPhone,
                website: reviewWebsite,
                comment: reviewComment,
                city: reviewCity,
                state: reviewState,
                status: reviewStatus,
                rating: reviewRating,
                title: reviewTitle,
                positionid: reviewPositionId
            },
            success: function(response) {
                 console.log(response.data);
                if (response.success) {
                    alert('Review updated successfully.');
                    $('#cr-edit-review-popup').hide();
                    location.reload();
                } else {
                    console.log(response.data);
                    alert('Failed to update review: ' + response.data);
                }
            },
            error: function() {
                alert('An error occurred while updating the review.');
            }
        });
    });

      $('#close-reply-popup').on('click', function() {
      $('#cr-reply-popup').hide();
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
           },
            success: function(response) {
                console.log(response);
                if (response.success) {
                    alert('Imports completed successfully.');
                    $('#ctrw-import-popup').hide();
                   
                } 
            },
            error: function() {
                alert('An error occurred during import.');
            }
        });
    });

    $('#close-ctrw-import-popup').on('click', function() {
        $('#ctrw-import-popup').hide();
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
              reply_message: replyMessage
          },
          success: function(response) {
              if (response.success) {
                  alert('Reply submitted successfully.');
                  $('#cr-reply-popup').hide();
              location.reload();
              } else {
                  alert('Failed to submit reply: ' + response.data);
              }
          },
          error: function() {
              alert('An error occurred while submitting the reply.');
          }
      });
      });

      $('#select-all').on('click', function() {
      let isChecked = $(this).prop('checked');
      $('input[name=\"review_ids[]\"]').prop('checked', isChecked);
      });
    $('#import-customer-reviews').on('click', function() {
       
        $('#ctrw-import-popup').show();
    });



      $('#ctrw-general-tab, #ctrw-review-form-tab, #ctrw-advanced-tab, #ctrw-shortcodes-tab').on('click', function(e) {
    e.preventDefault();
    let tabId = $(this).attr('id');
    let tabName = '';
    if (tabId === 'ctrw-general-tab') tabName = 'general';
    else if (tabId === 'ctrw-review-form-tab') tabName = 'review_form';
    else if (tabId === 'ctrw-advanced-tab') tabName = 'advanced';
    else if (tabId === 'ctrw-shortcodes-tab') tabName = 'shortcodes';
    $('#ctrw-active-tab').val(tabName);
});

    $('#review_display_type').on('change', function() {
        var infoText = '';
        switch ($(this).val()) {
            case 'list':
                infoText = 'Displays reviews in a standard list format on the product page.';
                break;
            case 'slider':
                infoText = 'Displays reviews in a slider/carousel on the product page.';
                break;
            case 'floating':
                infoText = 'Displays reviews in a floating widget on the product page.';
                break;
        }
        $('#review_display_info').text(infoText);
    });


        $('#ctrw-form-settings').on('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            var formData = $(this).serialize();
            
            // Add security nonce using your existing object
            formData += '&security=' + ctrw_admin_ajax.nonce;
            
            // Add action
            formData += '&action=ctrw_save_settings';            
            // AJAX request
            $.ajax({
                type: 'POST',
                url: ctrw_admin_ajax.ajax_url, // Note: ajax_url instead of ajaxurl
                data: formData,
                success: function(response) {
                   
                    $('#ctrw-success-msg').fadeIn().delay(2000).fadeOut();
                
                }
            });
        });


  });





// Example usage: Add this to an element in your HTML
// <a href="#" onclick="showTab(event, 'general')">General</a>