jQuery(document).ready(function ($) {
    // Section: Review Submission Form
    const $reviewForm = $("#customer-reviews-form");
    if ($reviewForm.length) {
        $reviewForm.on("submit", function (e) {
            e.preventDefault();
            const $submitButton = $(this).find('#comment-submit');
            const $message = $("#review-message");
            
            let formData = $reviewForm.serialize();
            formData += "&action=submit_review";
            formData += "&nonce=" + ctrw_ajax.nonce; 

            // Add feedback to user
            $submitButton.prop('disabled', true).val('Submitting...');
            $message.html('').hide();

            $.ajax({
                url: ctrw_ajax.ajax_url,
                method: "POST",
                data: formData,
                dataType: 'json', // Expect a JSON response
                success: function (response) {
                    if (response.success) {
                        $message.html("✅ " + response.data.message).css("color", "green").fadeIn();
                        $reviewForm[0].reset();
                    } else {
                        // Display specific error message from server if available
                        const errorMessage = response.data.message || 'Error submitting review.';
                        $message.html("❌ " + errorMessage).css("color", "red").fadeIn();
                    }
                },
                error: function () {
                    $message.html("❌ An unexpected error occurred. Please try again.").css("color", "red").fadeIn();
                },
                complete: function() {
                    // Re-enable the button
                    $submitButton.prop('disabled', false).val('Submit');
                }
            });
        });
    }

    // Section: Review List Pagination (AJAX)
    $(document).on('click', '.reviews-pagination a', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const target = $('#reviews-container');
        if (!target.length) return;

        const scrollTo = target.offset().top - 50; // Add some offset
        const page = $(this).data('page');
        const postId = target.data('post-id');
        
        target.addClass('loading').css('opacity', 0.5);
        
        $.ajax({
            url: ctrw_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'load_reviews_ajax',
                page: page,
                post_id: postId,
                nonce: ctrw_ajax.nonce // Add nonce for security
            },
            dataType: 'json', // Expect a JSON response
            success: function(response) {
                // The PHP now sends a JSON object with an 'html' property
                if (response.success && response.data.html) {
                    target.html(response.data.html);
                    $('html, body').animate({scrollTop: scrollTo}, 300);
                } else {
                    console.error('AJAX Error: Invalid response from server.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
            },
            complete: function() {
                target.removeClass('loading').css('opacity', 1);
            }
        });
    });

    // Section: Floating Widget
    const $floatingWidget = $('.ctrw-floating-widget');
    if ($floatingWidget.length) {
        $('.ctrw-floating-tab').on('click', function(e) {
            e.stopPropagation();
            $floatingWidget.toggleClass('active');
        });
        
        $('.ctrw-close-btn').on('click', function(e) {
            e.stopPropagation();
            $floatingWidget.removeClass('active');
        });
        
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.ctrw-floating-widget').length) {
                $floatingWidget.removeClass('active');
            }
        });
    }

    // Section: Review Slider
    const sliderContainer = document.querySelector('.ctrw-slider-container');
    if (sliderContainer) {
       const slidesContainer = sliderContainer.querySelector('.ctrw-slider-slides');
       if (!slidesContainer) return; // Exit if slider is not properly structured
       
       const slides = slidesContainer.querySelectorAll('.ctrw-slider-slide');
       const prevButton = sliderContainer.querySelector('.ctrw-slider-prev');
       const nextButton = sliderContainer.querySelector('.ctrw-slider-next');
       const dotsContainer = sliderContainer.querySelector('.ctrw-slider-controls-dots');
      
       if (slides.length === 0) return;

       let currentIndex = 0;
       const slideCount = slides.length;
       
       function updateSlider() {
           const slideWidth = slides[0].offsetWidth;
           const gap = 20; // As defined in your CSS
           const offset = currentIndex * (slideWidth + gap);
           slidesContainer.style.transform = `translateX(-${offset}px)`;

           // Update dots
           if (dotsContainer) {
               const dots = dotsContainer.querySelectorAll('.ctrw-slider-dot');
               dots.forEach((dot, index) => {
                   dot.classList.toggle('ctrw-slider-active', index === currentIndex);
               });
           }

           // Update buttons
           if(prevButton) prevButton.disabled = currentIndex === 0;
           if(nextButton) nextButton.disabled = currentIndex >= slideCount - 1;
       }
      
       // Create dots
       if (dotsContainer) {
            for (let i = 0; i < slideCount; i++) {
                const dot = document.createElement('button');
                dot.classList.add('ctrw-slider-dot');
                if (i === 0) dot.classList.add('ctrw-slider-active');
                dot.dataset.index = i;
                dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
                dot.addEventListener('click', () => {
                    currentIndex = i;
                    updateSlider();
                });
                dotsContainer.appendChild(dot);
            }
       }

       if (nextButton) {
           nextButton.addEventListener('click', () => {
               if (currentIndex < slideCount - 1) {
                   currentIndex++;
                   updateSlider();
               }
           });
       }

       if (prevButton) {
           prevButton.addEventListener('click', () => {
               if (currentIndex > 0) {
                   currentIndex--;
                   updateSlider();
               }
           });
       }

       window.addEventListener('resize', updateSlider);
       updateSlider(); // Initial call
    }
});